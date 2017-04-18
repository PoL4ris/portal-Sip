<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;
//use App\Models\Product;
use App\Models\Address;
use App\Models\Building\Building;
use App\Extensions\MtikRouter;
use App\Extensions\CiscoSwitch;
use App\Extensions\SIPSignup;


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

    public function getUnitNumbers(Request $request){

        $input = $request->all();
        $this->session = $request->session();

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

        $unitNumbers = $unitNumberArray[$streetAddress];
        return view('signup.unit-drop-down', compact('unitNumbers'));
    }

    public function getSignupForm(Request $request){

        $this->session = $request->session();

        $locName = $this->session->get('locName');

        $building = $this->session->get('building');

        $addressList = Address::where('id_buildings', $building->id)
                            ->whereNull('id_customers')
                            ->get();

//        $addressList = $addressList->pluck('address', 'id');
//        $addressUnitNumberMap = $building->getUnitNumbers();
//        $unitNumberArray = array();
//        foreach($addressUnitNumberMap as $addressId => $unitNumberList){
//            $unitNumberArray[$addressList[$addressId]] = $unitNumberList;
//        }
//        dd($unitNumberArray);


        return view('signup.form', compact('locName', 'addressList'));




//        if ($field['label'] == 'Street Address') {
//
//                $alertMsg = 'service router: ' . $userSession->serviceRouterName . "\n";
//                $alertMsg .= 'addresses: ' . print_r($this->config['building_address'], true) . "\n";
////                $alertMsg .= 'units: ' . print_r($this->config['building_units'], true) . "\n";
//                //[$userSession->serviceRouterName]
////                                        error_log($alertMsg);
//
//                echo "
//					</td>
//					<td width='50%'>\n";
//
//                if (isset($userSession->serviceRouterName)) {
//                    $router_name = $userSession->serviceRouterName;
//                    if (isset($this->config['building_address'][$router_name])) {
//                        $buildingList = $this->config['building_address'][$router_name];
//                        if (count($buildingList) > 1) {
//                            $field['type'] = 'select';
//                            $field['default'] = 1;
//                            $field['items'] = $buildingList;
//                        } elseif (count($buildingList) == 1) {
//                            $field['value'] = $buildingList[0];
//                            $field['access'] = 'readonly="readonly"';
//                        }
//                    }
//                }
//                $this->insertField($field, $fieldID, $fID);
//
//
//
//
//
//
//
//
//
//
//
//
//        if($this->session->get('alreadyRegistered') && trim($this->session->get('Unit')) != '') {
//
//        }
//
//        dd([$building, $addresses]);
//
//
//        if ($field['label'] == 'Apartment/Unit') {
//                if (isset($userSession->alreadyRegistered) && $userSession->alreadyRegistered && isset($userSession->Unit) && trim($userSession->Unit) != '') {
//                    $field['value'] = $userSession->Unit;
////                                        $field['access'] = 'readonly="readonly"';
//                } elseif (isset($userSession->serviceRouterName)) {
//                    $router_name = $userSession->serviceRouterName;
//                    if (isset($this->config['building_address'][$router_name])) {
//                        $buildingList = $this->config['building_address'][$router_name];
//                        $field['type'] = 'select';
//                        $field['default'] = 0;
//                        if (isset($this->config['building_units'][$buildingList[0]])) {
//                            $field['items'] = $this->config['building_units'][$buildingList[0]];
//                        } else {
//                            $field['items'] = array('No address selected');
//                        }
//                    }
//                }
//                $this->insertField($field, $fieldID, $fID);
//            }



//        if (isset($userSession->alreadyRegistered) && $userSession->alreadyRegistered && isset($userSession->Unit) && trim($userSession->Unit) != '') {
//            $field['value'] = $userSession->Unit;
//            //                                        $field['access'] = 'readonly="readonly"';
//        } elseif (isset($userSession->serviceRouterName)) {
//            $router_name = $userSession->serviceRouterName;
//            if (isset($this->config['building_address'][$router_name])) {
//                $buildingList = $this->config['building_address'][$router_name];
//                $field['type'] = 'select';
//                $field['default'] = 0;
//                if (isset($this->config['building_units'][$buildingList[0]])) {
//                    $field['items'] = $this->config['building_units'][$buildingList[0]];
//                } else {
//                    $field['items'] = array('No address selected');
//                }
//            }
//        }



        //dd($this->sipSignup->getBuildingPlans($this->session->get('id_address')));
        return view('signup.form', compact('locName', '$address'));
        //        return view('signup.thank-you');
    }






    //    public function getSignupProducts(Request $request)
    //    {
    //
    //        //    return Product::with('getProductsInfo')->get();
    //        return
    //            DB::select('SELECT p.id AS ProdID, p.name AS prodname, p.amount, p.frequency,
    //                         ppv.id_products, ppv.value as speed, ppv.id AS ProdPropValID, ppv.id_product_properties,
    //                         pp.id AS ProdPropID, pp.description,
    //                         ppvl.id AS ProdPropValIDLeft, ppvl.value AS slogan, ppvl.id_product_properties AS ProdPropIDLeft
    //                          FROM  building_products bp
    //                            INNER JOIN buildings b
    //                              ON bp.id_buildings = b.id
    //                            INNER JOIN products p
    //                              ON bp.id_products = p.id
    //                            INNER JOIN product_property_values ppv
    //                              ON ppv.id_products = p.id
    //                            INNER JOIN product_properties pp
    //                              ON pp.id = ppv.id_product_properties
    //                            LEFT JOIN product_property_values ppvl
    //                              ON ppvl.id_products = p.id
    //                          WHERE bp.id_buildings = '. $request->id .'
    //                            AND ppv.id_product_properties = 1
    //                            AND ppvl.id_product_properties = 4
    //                          ORDER BY speed * 1 asc');
    //    }
}
