<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;
use View;
//use App\Models\Product;
use App\Models\Address;
use App\Models\Building\Building;
use App\Extensions\MtikRouter;
use App\Extensions\CiscoSwitch;
use App\Extensions\SIPSignup;
use App\Extensions\SIPBilling;
use Illuminate\Support\Facades\Validator;

class SignupController extends Controller
{

    protected $session;
    protected $serviceRouter;
    protected $userPortInfo;
    protected $sipSignup;

    public function __construct(){
        //    DB::connection()->enableQueryLog();
        $this->sipSignup = new SIPSignup();
    }

    public function getSplashPage(){

        return view('signup.splash');
    }

    public function getWelcomePage(Request $request){

        $input = $request->all();

        $sessionArray = $this->sipSignup->processRouterRequest($input);
        $this->session = $request->session();

        if ($sessionArray == false || $sessionArray['invalidUserIP']){
            return view('signup.error');
        }

        foreach($sessionArray as $key => $value){
            $this->session->put($key, $value);
        }

        $switchIP = $this->session->get('serviceSwitch')->ip_address;
        $switchPort = $this->session->get('userPortInfo.Switch Port Number');
        $port = $this->sipSignup->findPort($switchIP, $switchPort);

        $alreadyRegistered = false;
        $unit = null;
        if($port == null){
            $alreadyRegistered = false;
        } else {
            $this->session->put('userPortInfo.PortID', $port->id);
            $this->session->put('PortID', $port->id);
            $portAccess = $port->access_level;

            if ($portAccess == 'yes'){
                $this->session->put('activateUser', true);
                return view('signup.port-active');
            }

            $unit = $this->sipSignup->getUnitFromPort($port);
            if($unit != null){
                $alreadyRegistered = true;
            }
        }
        $this->session->put('alreadyRegistered', $alreadyRegistered);
        $this->session->put('Unit', $unit);


        $address = Address::find($this->session->get('id_address'));
        $building = Building::find($address->id_buildings);
        $locName = $building->name;
        $locSubtitle = $address->address;
        $buildingProperties = $building->getProperties();
        $buildingLogo = 'img/buildings/'.$buildingProperties[config('const.building_property.image')];
        $phoneNumber = $buildingProperties[config('const.building_property.support_number')];

        $this->session->put('locName', $locName);
        $this->session->put('locSubtitle', $locSubtitle);
        $this->session->put('phoneNumber', $phoneNumber);
        $this->session->put('address', $address);
        $this->session->put('building', $building);

        // This is where we render the building welcome screen
        return view('signup.welcome', compact('locName', 'locSubtitle', 'buildingLogo',
                                              'phoneNumber'));
    }

    public function getSignupForm(Request $request){

        $this->session = $request->session();

        $locName = $this->session->get('locName');
        $building = $this->session->get('building');
        $addressList = Address::where('id_buildings', $building->id)
            ->whereNull('id_customers')
            ->get();

        $unitNumbers = array();
        if(count($addressList) == 1){
            $unitNumbers = $this->getUnitNumbers(['address' => $addressList->first()->address]);
        }

        $addressId = $this->session->get('id_address');
        $servicePlanInfo = $this->sipSignup->getBuildingPlans($addressId);
        $splashMode = 'bulk';
        $activationFees = $this->sipSignup->getBuildingActivationFees($addressId);

        return view('signup.form', compact('locName', 'addressList', 'unitNumbers', 'servicePlanInfo', 'activationFees','splashMode'));
    }

    public function getUnitNumbersAjax(Request $request){

        $input = $request->all();
        $this->session = $request->session();
        $unitNumbers = $this->getUnitNumbers;
        return view('signup.unit-drop-down', compact('unitNumbers'));
    }

    protected function getUnitNumbers($input){

        $streetAddress = $input['address'];
        if($streetAddress == '0'){
            $unitNumbers = array();
            return view('signup.unit-drop-down', compact('unitNumbers'));
        }

        $building = $this->session->get('building');
        $addressCollection = Address::where('id_buildings', $building->id)
            ->whereNull('id_customers')
            ->get();

        $addressList = $addressCollection->pluck('address', 'id');
        $addressUnitNumberMap = $building->getUnitNumbers();
        $unitNumberArray = array();
        foreach($addressUnitNumberMap as $addressId => $unitNumberList){
            $unitNumberArray[$addressList[$addressId]] = $unitNumberList;
        }

        return $unitNumberArray[$streetAddress];
    }

    public function validateSignupForm(Request $request) {

        $this->session = $request->session();
        $input = $request->all();

        //        dump($input);
        //        return;

        $rules = [
            'first_name' => 'required|alpha_dash|max:255',
            'last_name'  => 'required|alpha_dash|max:255',
            'email'      => 'required|e-mail|max:255',
            'phone_number' => 'required|alpha_dash|max:255',
            'street_address' => 'required|max:255',
            'unit' => 'required|max:255',
            'city' => 'required|alpha|max:255',
            'state' => 'required|size:2|alpha|max:2',
            //            'zip' => 'required|numeric|size:5',
            'service_plan' => 'required|alpha_dash|max:255',
            'wireless_router' => 'required|max:255',
            'cc_type' => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|alpha|size:2',
            'cc_number' => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|numeric',
            'cc_exp_month' => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|numeric|size:2',
            'cc_exp_year' => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|numeric',
            'cc_sec_code' => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|numeric',
            't_and_c_check_box' => 'required',
        ];

        $messages = [
            'unit.required' => 'Please enter or select your unit number',
            't_and_c_check_box.required' => 'You must read and agree with the terms and conditions',
            'wireless_router.required' => 'Please select a router option',
            'service_plan.required' => 'Please select an internet plan',
            'required' => 'Please enter your :attribute',
            'alpha_dash' => 'Please use letters and numbers only',
            'alpha' => 'Please use letters only',
            'size'    => 'The :attribute must be exactly :size.',
            'cc_type.required_unless'   => 'Please select your card type',
            'cc_number.required_unless'   => 'Please enter your card number',
            'cc_exp_month.required_unless'   => 'Please select an expiration month',
            'cc_exp_year.required_unless'   => 'Please select an expiration year',
            'cc_sec_code.required_unless'   => 'Please enter your card\'s security code',
        ];

        $validation = Validator::make($request->all(), $rules, $messages);

        $validation->setAttributeNames(['first_name' => 'first name',
                                        'last_name' => 'last name',
                                        'email' => 'Email',
                                        'phone_number' => 'phone number',
                                        'street_address' => 'address',
                                        'unit' => 'unit number',
                                        'city' => 'city',
                                        'state' => 'state',
                                        'zip' => 'zip code',
                                        'service_plan' => 'plan',
                                        'wireless_router' => 'wireless router',
                                        'cc_type' => 'card type',
                                        'cc_number' => 'card number',
                                        'cc_exp_month' => 'expiration month',
                                        'cc_exp_year' => 'expiration year',
                                        'cc_sec_code' => 'security code']);


        if ($validation->passes() == false) {

            $errors = $validation->getMessageBag();
            $errors->add('error', 1);
            return $errors;
            //            return
            //            return view('signup.error');
        } else {


            $this->session->put('validated_data', $input);
//            dump($input);

            dump($this->session);

            return;

            return view('signup.thank-you');






            //            $this->session


            $signupService->validateForm();

            // FOR DEBUGGING ONLY. COMMENT THESE OUT IN PROD.
            //				 $errors['field1'] = 'debug 3: '.print_r($_SESSION,true);
            //				 $errors = array('error' => 1) + $errors;
            //                                 $config['displayError'] = true;
            //				 throw new FormValidateException($errors);

            $signupService->checkCC();
            $activationCode = rand();
            if ($config['internetEnabled'] == true) {
                if (isset($activeSession->alreadyRegistered) && !$activeSession->alreadyRegistered) {
                    $signupService->storeUserPortInfo();
                    //                        $activeSession->PrivateVlan = $signupService->getPrivateVlan($activeSession->portID);
                } else {
                    //                        $signupService->setUserPortAccess('yes');
                    setCustomerPortAccessByPortID($activeSession->portID, 'yes');
                }
            }
            $signupService->storeUserInfo();
            if ($activeSession->transLogged) {
                $signupService->updateXactionWithCID($activeSession->CID);
            }
            $signupService->storeUserProductInfo();
            $signupService->sendSipEmail();
            $signupService->sendUserReceipt();
            if ($activeSession->NumberTransfer == 'yes') {
                $signupService->sendVoipEmail();
            }

            $thankYou = $config['thankYouMessage']; // . '<br>Support ID: ' . $activeSession->userPortInfo['PortID'] . '<br>port ID: ' . $activeSession->portID;
            // FOR DEBUGGING ONLY. COMMENT THESE OUT IN PROD.
            // $signupService->activateUser($activationCode);
            // debug_message("ABOUT TO CALL JSON ...");

            if (IS_AJAX) {
                //					echo json_encode(array('validation_success'=>1));
                $redir_timer = '90000';
                if (isset($activeSession->universalNat) && $activeSession->universalNat == 'true') {
                    $redir_timer = '5000';
                }

                $jsonResponse = array('success' => 1,
                                      'activationCode' => $activationCode,
                                      'universal' => $redir_timer);

                if (isset($activeSession->portID)) {
                    $jsonResponse['portID'] = $activeSession->portID;
                } else {
                    $jsonResponse['portID'] = '';
                }

                header("Content-type: application/json; charset=UTF-8");
                echo json_encode($jsonResponse);

                // FOR DEBUGGING ONLY. COMMENT THESE OUT IN PROD.
                // $signupService->activateUser($activationCode);

                exit;
            }




        }



        if(trim($input['first_name']) == ''){
            $errors['first_name']= 'Please enter a valid first name';
        }
        //        if(trim($input['last_name']) == ''){
        //            $errors['last_name']= 'Please enter a valid last name';
        //        }
        //        if(trim($input['email']) == ''){
        //            $errors['email']= 'Please enter a valid email';
        //        }
        //        if(trim($input['first_name']) == ''){
        //            $errors['first_name']= 'Please enter a valid first name';
        //        }
        //        if(trim($input['first_name']) == ''){
        //            $errors['first_name']= 'Please enter a valid first name';
        //        }
        //        if(trim($input['first_name']) == ''){
        //            $errors['first_name']= 'Please enter a valid first name';
        //        }
        //        if(trim($input['first_name']) == ''){
        //            $errors['first_name']= 'Please enter a valid first name';
        //        }
        //        if(trim($input['first_name']) == ''){
        //            $errors['first_name']= 'Please enter a valid first name';
        //        }
        //        if(trim($input['first_name']) == ''){
        //            $errors['first_name']= 'Please enter a valid first name';
        //        }
        //        if(trim($input['first_name']) == ''){
        //            $errors['first_name']= 'Please enter a valid first name';
        //        }
        if (count($errors) > 0) {
            $errors['error'] = 1;
            return $errors;
        }

        dump($input);

        return;

        //        return view('signup.thank-you');
        //
        $fields = &$this->config['fields'];
        $userSession = Session::getInstance();

        for ($i = 0; $i < count($fields); $i++) {

            if (isset($_POST['field' . $i]) || $fields[$i]['type'] == 'checkBox') {

                if ($fields[$i]['label'] == 'E-mail') {
                    $userSession->Email = $_POST['field' . $i];
                }

                if (isset($fields[$i]['validation']) && $fields[$i]['validation'] == 'state') {
                    $_POST['field' . $i] = strtoupper($_POST['field' . $i]);
                }

                if ($fields[$i]['label'] == 'Credit Card Type' || $fields[$i]['label'] == 'Credit Card Number' ||
                    $fields[$i]['label'] == 'Expiration Month' || $fields[$i]['label'] == 'Expiration Year' ||
                    $fields[$i]['label'] == 'Security Code') {
                    if (($userSession->Totalcharges == '0.00' && $userSession->delayedCharges == '0.00') || ($userSession->Totalcharges == '' && $userSession->delayedCharges == '')) {
                        continue;
                    }
                    // FOR DEBUGGING ONLY. COMMENT THESE OUT IN PROD.
                    //                    $errors['field1'] = '($userSession->Totalcharges: '."'".$userSession->Totalcharges . "'\n".'$userSession->delayedCharges: '."'".$userSession->delayedCharges."'";
                    //                    $errors = array('error' => 1) + $errors;
                    //                    throw new FormValidateException($errors);
                }

                if (isset($fields[$i]['validation'])) {
                    $fields[$i]['validation'] = str_replace(
                        array_keys($this->patterns), array_values($this->patterns), $fields[$i]['validation']
                    );

                    if (!in_array($fields[$i]['validation'], array('email', 'captcha')) && !preg_match('/^(\W).+\1[a-z]*$/', trim($fields[$i]['validation']))) {
                        $fields[$i]['validation'] = '/^' . $fields[$i]['validation'] . '$/i';
                    }
                }

                if ((isset($fields[$i]['required']) && $fields[$i]['required'] && (!isset($_POST['field' . $i]) || mb_strlen($_POST['field' . $i], 'utf-8') < 1)) ||
                    (isset($fields[$i]['required']) && $fields[$i]['required'] && $fields[$i]['type'] == 'select' && $_POST['field' . $i] == $fields[$i]['default']) ||
                    (isset($fields[$i]['validation']) && $fields[$i]['validation'] == 'email' && !PHPMailer::ValidateAddress($_POST['field' . $i])) ||
                    (isset($fields[$i]['validation']) && $fields[$i]['validation'] == 'captcha' && $_POST['field' . $i] != $userSession->captchaResult) ||
                    (isset($fields[$i]['validation']) && !in_array($fields[$i]['validation'], array('email', 'captcha')) && @!preg_match($fields[$i]['validation'], $_POST['field' . $i]))) {
                    $errors['field' . $i] = $fields[$i]['errorText'] ? $fields[$i]['errorText'] : 'You\'ve entered incorrect data.';
                    $fields[$i]['displayError'] = true;
                    //                    error_log('Error detected on: '.$fields[$i]['id']);
                }

                if ($fields[$i]['label'] == 'Delayed Charges') {
                    $delayedCharges = $_POST['field' . $i];
                    $userSession->delayedCharges = $delayedCharges; //.'.00';
                }

                // If field has a DB field name (supposed to be stored in DB)
                // then store it in the session so we can save it to the DB later
                if (isset($fields[$i]['dbFieldName'])) {
                    $sessionFieldName = $fields[$i]['dbFieldName'];


                    if ($fields[$i]['id'] == 'phonenumber') {
                        $phoneNum = $_POST['field' . $i];
                        $userSession->rawPhoneNumber = getRawPhoneNumber($phoneNum);
                        $userSession->$sessionFieldName = formatPhoneNumber($phoneNum);
                    }
                    // Process the Street Address field
                    elseif ($fields[$i]['label'] == 'Street Address') {
                        if (isset($userSession->serviceRouterName)) {
                            $router_name = $userSession->serviceRouterName;
                            if (isset($this->config['building_address'][$router_name])) {
                                // Use the address information in the config file
                                $buildingList = $this->config['building_address'][$router_name];
                                if (count($buildingList) > 1) {
                                    if ($_POST['field' . $i] == 0) {
                                        $errors['field' . $i] = $fields[$i]['errorText'];
                                    }
                                    // Convert the value from the address select box
                                    // to the actual address of the building
                                    $userSession->$sessionFieldName = $this->config['building_address'][$router_name][$_POST['field' . $i]];
                                } elseif (count($buildingList) == 1) {
                                    // Else the address is the value of the text box in the form
                                    $userSession->$sessionFieldName = $_POST['field' . $i];
                                }
                            } else {
                                // else use the address that the user typed in
                                $userSession->$sessionFieldName = $_POST['field' . $i];
                            }
                        }
                    }
                    // Or if it's the Apartment/Unit get the correct value
                    elseif ($fields[$i]['label'] == 'Apartment/Unit') {
                        //                        $logText = '$userSession->Address = '.$userSession->Address."\n";
                        //                        $logText .= '$userSession->Unit = '.$userSession->Unit."\n";
                        //                        $logText .= '$_POST[field . '."$i".'] = '.$_POST['field' . $i]."\n";
                        //                        error_log($logText);
                        if (isset($userSession->Address)) {
                            // $router_name = $userSession->serviceRouterName;

                            if (!isset($userSession->Unit) && isset($this->config['building_units'][$userSession->Address])) {
                                // Convert the value from the unit select box
                                // to the actual unit number of the customer
                                if ($_POST['field' . $i] == 0) {
                                    $errors['field' . $i] = $fields[$i]['errorText'];
                                } else {
                                    $userSession->$sessionFieldName = $this->config['building_units'][$userSession->Address][$_POST['field' . $i]];
                                }
                            } else {
                                if (isset($userSession->Unit)) {
                                    $userSession->$sessionFieldName = $userSession->Unit;
                                } else {
                                    // Else the unit number is the value of the text box in the form
                                    $userSession->$sessionFieldName = $_POST['field' . $i];
                                }
                            }
                        } else {
                            // Else the address is the value of the text box in the form
                            $userSession->$sessionFieldName = $_POST['field' . $i];
                        }
                    }
                    // Or if it's the Service Plan determine the plan name and cycle
                    elseif ($fields[$i]['label'] == 'Service Plan') {
                        $servicePlan = $_POST['field' . $i];
                        if (isset($servicePlan) && $servicePlan != '') {
                            $servicePlanNameArr = preg_split("/-/", $servicePlan);
                            if (count($servicePlanNameArr) > 1) {
                                $planSpeed = $servicePlanNameArr[0];
                                $planCycle = $servicePlanNameArr[1];

                                // FOR DEBUGGING ONLY. COMMENT THESE OUT IN PROD.
                                //                        $errors['field1'] = '$servicePlanNameArr: '.print_r($servicePlanNameArr,true);
                                //                        $errors['field1'] .= "\n".'$fields[$i][\'items\'][$planSpeed]: '.print_r($fields[$i]['items'][$planSpeed], true);
                                //                        $errors = array('error' => 1) + $errors;
                                //                        $config['displayError'] = true;
                                //                        throw new FormValidateException($errors);


                                if (isset($fields[$i]['items'][$planSpeed]['type']) && $fields[$i]['items'][$planSpeed]['type'] == 'included') {

                                    $userSession->servicePlanName = $planSpeed . ' Mbps - ' . ucfirst($planCycle) . ' ($0.00)';
                                    $userSession->servicePlanID = $this->config['servicePlanInfo'][$planSpeed]['id-included'];
                                } else {
                                    $userSession->servicePlanName = $planSpeed . ' Mbps - ' . ucfirst($planCycle) . ' ($' . $fields[$i]['items'][$planSpeed][$planCycle] . ')';
                                    $userSession->servicePlanID = $this->config['servicePlanInfo'][$planSpeed]['id-' . $planCycle];
                                }

                                $userSession->servicePlanCycle = $planCycle;
                                //                        if (array_key_exists($servicePlan, $fields[$i]['items'])) {
                                //                            $userSession->servicePlanName = $fields[$i]['items'][$servicePlan];
                                //                        }
                                $userSession->$sessionFieldName = $_POST['field' . $i];
                            } else {
                                $userSession->servicePlanName = $servicePlan;
                                $userSession->servicePlanID = '';
                                $userSession->servicePlanCycle = '';
                            }
                        }
                    }
                    // Or if it's the Wireless Router get the correct value
                    elseif ($fields[$i]['label'] == 'Wireless Router') {
                        if ($_POST['field' . $i] == "") {
                            $errors['field' . $i] = $fields[$i]['errorText'];
                        } elseif ($_POST['field' . $i] == "NoRouter") {
                            $userSession->$sessionFieldName = "No Router";
                        } elseif ($_POST['field' . $i] == "BasicWiFi") {
                            $userSession->$sessionFieldName = "Basic WiFi";
                            $userSession->routerProdID = '109';
                        } else {
                            $userSession->$sessionFieldName = "Fast WiFi";
                            $userSession->routerProdID = '136';
                        }
                    }
                    // Or if it's VOIP get the correct value
                    elseif ($fields[$i]['label'] == 'VOIP') {

                        if ($_POST['field' . $i] == "") {
                            $errors['field' . $i] = $fields[$i]['errorText'];
                        } elseif ($_POST['field' . $i] == "NoVoip") {
                            $userSession->$sessionFieldName = "No Voip";
                        } else {
                            $userSession->$sessionFieldName = "Unlimited Calling";
                            $userSession->voipPlanID = '101';
                            $userSession->voipAdapterProdID = '110';
                        }
                    }
                    // Voip Features
                    elseif ($fields[$i]['label'] == 'Voip Features') {
                        if ($_POST['field' . $i] == "") {
                            $userSession->$sessionFieldName = "None";
                        } else {
                            $userSession->$sessionFieldName = $_POST['field' . $i];
                            $voipFeaturesArr = explode(',', $_POST['field' . $i]);
                            $voipFeatureIDs = array();
                            foreach ($voipFeaturesArr as $voipFeat) {
                                $voipFeat = trim($voipFeat);
                                $voipFeatureIDs[] = $this->config['voipFeatureInfo'][$voipFeat];
                            }
                            if (count($voipFeatureIDs) > 0) {
                                $userSession->voipFeatureIDs = $voipFeatureIDs;
                            }
                        }
                    }
                    // Or if it's the Digital Phone Number get the correct value
                    elseif ($fields[$i]['label'] == 'I would like to') {
                        if ($userSession->Voipplan == "No Voip") {
                            $userSession->$sessionFieldName = "None";
                        } else {
                            if ($_POST['field' . $i] == 0) {
                                $errors['field' . $i] = $fields[$i]['errorText'];
                            } elseif ($_POST['field' . $i] == 1) {
                                $userSession->$sessionFieldName = "Number Transfer Requested";
                                $userSession->NumberTransfer = 'yes';
                            } elseif ($_POST['field' . $i] == 2) {
                                $userSession->$sessionFieldName = "New Number Requested";
                                $userSession->NumberTransfer = 'no';
                            }
                        }
                    }
                    // Total Charges
                    elseif ($fields[$i]['label'] == 'Total Charges') {

                        $amountToCharge = $_POST['field' . $i];
                        $userSession->amountCharged = $amountToCharge; //.'.00';
                        $userSession->$sessionFieldName = $amountToCharge; //.'.00';
                    } elseif ($fields[$i]['label'] == 'Recurring Charges') {
                        $recurringCharges = $_POST['field' . $i];
                        $userSession->recurringCharges = $recurringCharges; //.'.00';
                        $totalRecurringCharges = $_POST['totalRecurringChargeBox'];
                        $userSession->totalRecurringCharges = $totalRecurringCharges; //.'.00';
                    } else {
                        $userSession->$sessionFieldName = $_POST['field' . $i];
                    }
                }
            }
        }

        //        if (isset($userSession->lastSubmit) && ( time() - $userSession->lastSubmit < 30 || $userSession->submitsLastHour[date('d-m-Y-H')] > 10 )) {
        //            $errors['general'] = 'Please wait for a few minutes before submitting again.';
        //        }
        // FOR DEBUGGING ONLY. COMMENT THESE OUT IN PROD.
        //        $errors['field1'] = 'debug 1: '.print_r($_SESSION,true);
        ////        $errors['field1'] = 'amountCharged: '.$userSession->Totalcharges."\n".'recurringCharges: '.$userSession->recurringCharges."\n".'delayedCharges: '.$userSession->delayedCharges;
        //        $errors = array('error' => 1) + $errors;
        //        $config['displayError'] = true;
        //        throw new FormValidateException($errors);
        //
        //       if (isset($errors) && count($errors)) {
        //            $errors['field1'] = 'debug 1: '.print_r($errors,true);
        //            $errors = array('error' => 1) + $errors;
        //            $config['displayError'] = true;
        //            throw new FormValidateException($errors);
        //        }

        if (isset($errors) && count($errors)) {
            $errors = array('error' => 1) + $errors;
            $fields[$i]['displayError'] = true;
            throw new FormValidateException($errors);
        }

        $errors['field1'] = 'debug 2: '.print_r($_SESSION,true);
        $errors = array('error' => 1) + $errors;
        $config['displayError'] = true;
        throw new FormValidateException($errors);
    }

    public function checkCC($input){

        $amount = '0.00';

        // If customer has selected a router then charge them now
        if ($input['total_charges'] != '0.00' && $input['total_charges'] != '') {
            //            error_log('Inside checkCC(Totalcharges): Calling: $this->processCC(false, '.$this->session->Totalcharges.', '.$this->session->Wireless . ' - ($' . $this->session->Totalcharges . '))');
            return $this->processCC(false, $this->session->Totalcharges, $this->session->Wireless . ' - ($' . $this->session->Totalcharges . ')');
        } elseif ($this->session->recurringCharges != '0.00' && $this->session->recurringCharges != '') {
            //            error_log('Inside checkCC(recurringCharges): Calling: $this->processCC(true, '.$this->session->recurringCharges.', SilverIP Service - ($' . $this->session->recurringCharges . '))');
            return $this->processCC(true, $this->session->recurringCharges, 'SilverIP Service - ($' . $this->session->recurringCharges . ')');
        } elseif ($this->session->delayedCharges != '0.00' && $this->session->delayedCharges != '') {
            //            error_log('Inside checkCC(delayedCharges): Calling: $this->processCC(true, '.$this->session->delayedCharges.', SilverIP Service - ($' . $this->session->delayedCharges . '))');
            return $this->processCC(true, $this->session->delayedCharges, 'SilverIP Service - ($' . $this->session->delayedCharges . ')');
        }
    }

    public function processCC(){

        // Charge the invoice
        $billingService = new SIPBilling();
        $chargeResult = $billingService->chargeCC($invoice->id_customers, $invoice->amount, 'invoice_id: '. $invoice->id, 'SilverIP Data', $invoice->details);

        $customer = Customer::find($invoice->id_customers);
        $invoiceDetails = ($invoice->details != '') ? json_decode($invoice->details, true) : null;
        $customerProductIds = ($invoiceDetails != null) ? array_column($invoiceDetails, 'customer_product_id') : null;
        $transactionId = isset($chargeResult['TRANSACTIONID']) ? $chargeResult['TRANSACTIONID'] : NULL;

        if ($chargeResult['RESPONSETEXT'] == 'APPROVED') {

            error_log('BillingHelper::processInvoice(): INFO: id: '.$invoice->id_customers . ', ' . trim($customer->first_name) . ' ' . trim($customer->last_name) . ', $' . $invoice->amount . ', ' . 'invoice: ' . $invoice->id  . " ... Approved\n");

            if($customerProductIds != null){
                // Update the customer product/service's expiration and charge timestamps
                $updateCount = $this->updateCustomerProductDates($customerProductIds);
                error_log('BillingHelper::processInvoice(): INFO: Updated expiration dates for '.$updateCount.' products of invoice: ' . $invoice->id);
            } else {
                error_log('BillingHelper::processInvoice(): ERROR: Could not update expiration dates for invoice: ' . $invoice->id);
            }

            $this->logInvoice($invoice, 'processed', $transactionId);
            $this->sendInvoiceReceiptEmail($invoice, $chargeResult);
            Invoice::destroy($invoice->id);

        }

    }
}
