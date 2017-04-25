<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;
use View;
//use App\Models\Product;
use App\Models\Address;
use App\Models\Contact;
use App\Models\Building\Building;
use App\Models\Customer;
use App\Models\CustomerProduct;
use App\Models\CustomerPort;
use App\Models\Port;
use App\Models\PaymentMethod;
use App\Extensions\MtikRouter;
use App\Extensions\CiscoSwitch;
use App\Extensions\SIPSignup;
use App\Extensions\SIPBilling;
use App\Extensions\SIPNetwork;
use Illuminate\Support\Facades\Validator;

class SignupController extends Controller
{

    protected $session;
    protected $serviceRouter;
    protected $userPortInfo;
    protected $sipSignup;
    protected $sipNetwork;
    protected $validatedFormData;
    protected $billingService;
    protected $chargeDetails;

    protected $validationRules = [
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
        'cc_exp_month' => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|numeric',
        'cc_exp_year' => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|numeric',
        'cc_sec_code' => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|numeric',
        't_and_c_check_box' => 'required',
    ];

    protected $validationMessages = [
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

    protected $validationAttributes = ['first_name' => 'first name',
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
                                       'cc_sec_code' => 'security code'];

    public function __construct(){
        //    DB::connection()->enableQueryLog();
        $this->sipSignup = new SIPSignup();
        $this->sipNetwork = new SIPNetwork();
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

        $portAlreadyRegistered = false;
        $unit = null;
        
        if($port != null){
            
            $this->session->put('userPortInfo.PortID', $port->id);
            $this->session->put('PortID', $port->id);

            if ($port->access_level == 'yes'){
                $this->session->put('activateUser', true);
                $activationCode = rand();
                $portId = $this->session->get('PortID');
                $this->session->put('activationCode', $activationCode);
                $timer = $this->session->get('universalNat') ? 5000 : 90000;
                $returnURL = $this->session->get('LinkOrig');

                // Port should already be active. Activate customer
                return view('signup.port-active', compact('activationCode', 'portId', 'timer', 'returnURL'));
            }

            $unit = $this->sipSignup->getUnitFromPort($port);
//            if($unit != null){
//                $portAlreadyRegistered = true;
//            }
            $portAlreadyRegistered = true;
        }
        $this->session->put('portAlreadyRegistered', $portAlreadyRegistered);
        $this->session->put('Unit', $unit);

        $address = Address::find($this->session->get('id_address'));
        $building = Building::find($address->id_buildings);
        $locName = $building->name;
        $this->session->put('locName', $locName);
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
        $this->session->put('servicePlanInfo', $servicePlanInfo);
        
        $serviceType = $building->getProperty(config('const.building_property.service_type'));
        $splashMode = (preg_match('/Bulk.*/', $serviceType) === 1) ? 'bulk' : 'retail';
        $this->session->put('splashMode', $splashMode);
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

    public function processCC($input) {

        $authOnly = false;
        $amount = 0;
        $desc = 'SilverIP Comm';
        $chargeDetails = false;

        // If customer has selected a router then charge them now
        if ($input['total_charges'] != '0.00' && $input['total_charges'] != '') {
            $authOnly = false;
            $amount = $input['total_charges'];
            $desc = $input['wireless_router'] . ' - ($' . $input['total_charges'] . ')';
        } 
        // If customer has selected a monthly plan just authorize their card            
        elseif ($input['recurring_charges'] != '0.00' && $input['recurring_charges'] != '') {
            $authOnly = true;
            $amount = $input['recurring_charges'];
            $desc = 'SilverIP Service - ($' . $input['recurring_charges'] . ')';

        } 
        // If customer has selected an annual plan just authorize their card
        elseif ($input['delayed_charges'] != '0.00' && $input['delayed_charges'] != '') {            
            $authOnly = true;
            $amount = $input['delayed_charges'];
            $desc = 'SilverIP Service - ($' . $input['delayed_charges'] . ')';
        } 
        // otherwise this function shouldn't have been called
        else {
            return false;
        }

        $this->billingService = new SIPBilling();

        $cardInfo = array();
        $cardInfo['CardNum'] = $this->validatedFormData['cc_number'];    // customer card number
        $cardInfo['CardName'] = trim($this->validatedFormData['first_name']) . ' ' . trim($this->validatedFormData['last_name']);     // customer name
        $cardInfo['CardExpMonth'] = $this->validatedFormData['cc_exp_month'];   // customer CC expire month - MM
        $cardInfo['CardExpYear'] = substr($this->validatedFormData['cc_exp_year'], 2);    // customer CC expire year - YY
        $cardInfo['CVV2'] = $this->validatedFormData['cc_sec_code'];
        $cardInfo['CardType'] = $this->validatedFormData['cc_type'];
        $cardInfo['BillingPhone'] = $this->validatedFormData['phone_number'];
        
        

        $orderNumber = ($this->validatedFormData['cc_type'] == 'AX') ? date('My') : date('My') . ' Data'; ;

        $address = new Address;        
        $address->address = $this->validatedFormData['street_address'];
        $address->city = $this->validatedFormData['city'];
        $address->state = $this->validatedFormData['state'];
        $address->zip = $this->validatedFormData['zip'];
        $address->unit = $this->validatedFormData['unit'];

        if($authOnly){
            Log::info('SignupController: Authorzing customer: ' . "\n" . print_r([$cardInfo['CardName'], $desc, $address['address'], $address['unit'], $address['city'], $address['state'], $address['zip']],true));
            return $this->billingService->authCreditCard($cardInfo, $amount, $desc,  $orderNumber, $chargeDetails, $address);    
        }

        Log::info('SignupController: Charging customer: ' . "\n" . print_r([$cardInfo['CardName'], $desc, $address['address'], $address['unit'], $address['city'], $address['state'], $address['zip']],true));
        return $this->billingService->chargeCreditCard($cardInfo, $amount, $desc,  $orderNumber, $chargeDetails, $address);
    }

    protected function handleChargeResult($chargeResult){

        $response;
        if(isset($chargeResult['FAILED']) == true){
            $response = 'failed';
        } 
        
        else if (!isset($chargeResult['RESPONSETEXT']) && !isset($chargeResult['ACTIONCODE'])) {
            $response = 'failed';  
        }

        else if ($chargeResult['RESPONSETEXT'] == 'DECLINED') {
            $response = 'declined';
        }

        else if ($chargeResult['RESPONSETEXT'] == 'APPROVED' && $chargeResult['ACTIONCODE'] == '000') {
            $response = 'approved';
        }

//        Log::info('SignupController: Charge '.$response.': ' . "\n" . print_r($chargeResult,true));
        return $response;
    }

    protected function getRawPhoneNumber($phoneNumber) {

        $phoneNumber = trim($phoneNumber);
        $phoneNumber = preg_replace('/ /', '', $phoneNumber);
        $phoneNumber = preg_replace('/^\+1/', '', $phoneNumber);
        $phoneNumber = preg_replace('/^1-/', '', $phoneNumber);
        $phoneNumber = preg_replace('/-/', '', $phoneNumber);
        $phoneNumber = preg_replace('/[\(\)]/', '', $phoneNumber);
        $phoneNumber = preg_replace('/\./', '', $phoneNumber);
        $phoneNumber = trim($phoneNumber);

        return $phoneNumber;
    }

    protected function storeCustomer(){

        $customer = new Customer;
        $customer->first_name = $this->validatedFormData['first_name'];
        $customer->last_name = $this->validatedFormData['last_name'];
        $customer->email = $this->validatedFormData['email'];
        $customer->password = bcrypt($this->getRawPhoneNumber($this->validatedFormData['phone_number']));
        $customer->id_status = config('const.status.active');
        $customer->signedup_at = date('Y-m-d H:i:s');
        $customer->save();
        
        $phoneContact = new Contact;
        $phoneContact->id_customers = $customer->id;
        $phoneContact->id_types = config('const.contact_type.mobile_phone');
        $phoneContact->value = $this->validatedFormData['phone_number'];
        $phoneContact->save();

        $emailContact = new Contact;
        $emailContact->id_customers = $customer->id;
        $emailContact->id_types = config('const.contact_type.email');
        $emailContact->value = $this->validatedFormData['email'];
        $emailContact->save();

        return $customer;
    }

    protected function storeCustomerPort($customer){
        
        $portAlreadyRegistered = $this->session->get('portAlreadyRegistered');
        $portId;
        if($portAlreadyRegistered){

            $port = Port::find($this->session->get('PortID'));
            $port->id_customers = $customer->id;
            $port->access_level = 'yes';
            $port->save();
            $portId = $port->id;

        } else {

            $userPortInfo = $this->session->get('userPortInfo');
            $serviceSwitch = $this->session->get('serviceSwitch');
            $port = new Port;
            $port->port_number = $userPortInfo['Switch Port Number'];
            $port->access_level = 'yes';
            $port->id_customers = $customer->id;
            $port->id_network_nodes = $serviceSwitch->id;
            $port->save();
            $portId = $port->id;
            $this->session->put('PortID', $port->id);
        }
        
        $customerPort = new CustomerPort;
        $customerPort->customer_id = $customer->id;
        $customerPort->port_id = $portId;
        $customerPort->save();
        return $customerPort;
    }
    
    protected function storeCustomerAddress($customerId, $address){

        $newAddress = $address->replicate();
        $newAddress->unit = $this->validatedFormData['unit'];
        $newAddress->id_customers = $customerId;
        $newAddress->save();

        return $newAddress;
    }

    protected function storeCustomerProducts($customerId, $addressId, $servicePlan, $wirelessRouter, $buildingPlanInfo){
        
        $servicePlanInfoArray = explode('-', $servicePlan);
        $servicePlanKey = $servicePlanInfoArray[0];
        $servicePlanType = $servicePlanInfoArray[1];
        $servicePlanId = $buildingPlanInfo[$servicePlanKey]['id-'.$servicePlanType];
        
        $customerProduct = new CustomerProduct;
        $customerProduct->id_customers = $customerId;
        $customerProduct->id_products = $servicePlanId;
        $customerProduct->id_status = config('const.status.active');;
        $customerProduct->id_address = $addressId;
        $customerProduct->id_customer_products = 0;
        $customerProduct->id_users = 0;
        $customerProduct->invoice_status = 0;
        $customerProduct->amount_owed = 0;
        $customerProduct->save();
        
        if($wirelessRouter == 'FastWiFi'){
            $customerProduct = new CustomerProduct;
            $customerProduct->id_customers = $customerId;
            $customerProduct->id_products = 136;
            $customerProduct->id_status = config('const.status.active');;
            $customerProduct->id_address = $addressId;
            $customerProduct->id_customer_products = 0;
            $customerProduct->id_users = 0;
            $customerProduct->invoice_status = 2;
            $customerProduct->amount_owed = 0;
            $customerProduct->save();
        }
        return $customerProduct;
    }
        
    protected function storePaymentMethod($customerId, $addressId){

        $pm = new PaymentMethod;
        $pm->account_number = $this->validatedFormData['ccToken'];
        $pm->exp_month = $this->validatedFormData['cc_exp_month'];
        $pm->exp_year = $this->validatedFormData['cc_exp_year'];
        $pm->types = 'Credit Card';
        $pm->billing_phone = $this->validatedFormData['phone_number'];
        $pm->priority = 1;
        $pm->id_address = $addressId;
        $pm->id_customers = $customerId;
        $pm->card_type = $this->validatedFormData['cc_type'];
        $pmPropertiesArr = array();
        $pmPropertiesArr['last four'] = 'XXXX-XXXX-XXXX-'.substr($this->validatedFormData['cc_number'], -4);
        $pmPropertiesArr['card type'] = $this->validatedFormData['cc_type'];
        $pmPropertiesArr['exp month'] = $this->validatedFormData['cc_exp_month'];
        $pmPropertiesArr['exp year'] = $this->validatedFormData['cc_exp_year'];
        $pm->properties = json_encode($pmPropertiesArr);

        $pm->save();

        return $pm;
    }

    public function activate(Request $request){
        
        $this->session = $request->session();
        $input = $request->all();
        
//         dump([$this->session,$input]);
//        return;
        
        if(isset($input['ActivationCode']) && $input['ActivationCode'] == $this->session->get('activationCode')){
            $result = $this->sipNetwork->activatePort($this->session->get('PortID'));            
            if($result){ return 'activated'; }
        }
        return 'failed';
    }
    
    public function validateSignupForm(Request $request) {

        $this->session = $request->session();
        $input = $request->all();

//        dump($input);
//        dump($this->session);
//        return;
        
        $validation = Validator::make($request->all(), $this->validationRules, $this->validationMessages);
        $validation->setAttributeNames($this->validationAttributes);

        // Validation failed
        if($validation->passes() == false) {

            $errors = $validation->getMessageBag();
            $errors->add('error', 1);
            return $errors;
        } 

        // Validation passed
        $this->session->put('validated_data', $input);
        $this->validatedFormData = $input;

        $chargeResult = null;
        if($input['total_charges'] != '0.00' || $input['recurring_charges'] != '0.00' || $input['delayed_charges'] != '0.00'){
                        
            $chargeResult = $this->processCC($input);
            $chargeCheck = $this->handleChargeResult($chargeResult);

            if($chargeCheck == 'failed'){
                $errors = $validation->getMessageBag();
                $errors->add('cc_number', ['We were unable to process your credit card. Please check your card information and try again.']);
                $errors->add('error', 1);
                return $errors;                    
            }

            if($chargeCheck == 'declined'){
                $errors = $validation->getMessageBag();
                $errors->add('cc_number', ['Your card was declined. Please check the number and/or expiration date and click Submit again.']);
                $errors->add('error', 1);
                return $errors;                    
            }

            if(isset($chargeResult['TOKEN'])) {
                $this->session->put('ccToken', $chargeResult['TOKEN']);
                $this->validatedFormData['ccToken'] = $chargeResult['TOKEN'];
                $this->session->put('TransactionLogId', $chargeResult['TransactionLogId']);
            }
        }

        $customer = $this->storeCustomer();
        $customerPort = $this->storeCustomerPort($customer);
        $address = $this->storeCustomerAddress($customer->id, $this->session->get('address'));
        $customerProduct = $this->storeCustomerProducts($customer->id, $address->id, $this->validatedFormData['service_plan'], $this->validatedFormData['wireless_router'], $this->session->get('servicePlanInfo'));
        
        if(isset($this->validatedFormData['ccToken'])){
            $pm = $this->storePaymentMethod($customer->id, $address->id);
            $this->billingService->updateXactionWithCustomer($this->session->get('TransactionLogId'), $customer, $address, $pm);
        }

        $activationCode = rand();
        $portId = $this->session->get('PortID');
        $this->session->put('activationCode', $activationCode);
        $timer = $this->session->get('universalNat') ? 5000 : 90000;
        $returnURL = $this->session->get('LinkOrig');
        $locName = $this->session->put('locName');

//        $signupService->sendSipEmail();
//        $signupService->sendUserReceipt();        
        
        return view('signup.thank-you', compact('locName', 'activationCode', 'portId', 'timer', 'returnURL'));
    }
}
