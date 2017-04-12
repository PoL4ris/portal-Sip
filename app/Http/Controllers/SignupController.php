<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
//use App\Models\Product;


class SignupController extends Controller
{

    protected $session;
    protected $serviceRouter;
    protected $userPortInfo;

    public function __construct(){
        //    DB::connection()->enableQueryLog();
    }


    public function getSplashPage(){
        return view('signup.splash');
    }

    protected function isRequestFromRouter() {

        if (isset($input['MacAddress']) && isset($input['IPAddress']) && isset($input['#LinkLogin']) &&
            isset($input['#LinkOrig']) && isset($input['ServiceRouter'])) {

            $this->session->put('MacAddress') = $input['MacAddress'];
            $this->session->put('IPAddress') = $input['IPAddress'];
            $this->session->put('LinkLogin') = $input['#LinkLogin'];
            $this->session->put('LinkOrig') = $input['#LinkOrig'];
            $this->session->put('ServiceRouter') = $input['ServiceRouter'];
            $this->session->put('RequestSource') = 'Router';
            return true;
        }
        return false;
    }

    public function processRouterData(Request $request){

        $input = $request->all();

        $this->session = $request->session();

        if($this->isRequestFromRouter() == false) {
            //        if(!isset($input['PostType']) && $input['PostType'] != 'Router'){
            dd('Who are you?');
            //            $userSession->RouterQuery = "Query not from a router";
            //            $userSession->invalidUserIP = true;
            //            error_log('ERROR: SignupService::processRouterData(): Query not from a router');
            //            return false;
        }


        $this->serviceRouter = new MtikRouter(array('HostName' => $this->session->get('ServiceRouter')));
        if ($this->serviceRouter->isSelected() == false) {
            dd('Router not found');
            //            $userSession->RouterQuery = "router not found";
            //            $userSession->invalidUserIP = true;
            //            error_log('ERROR: SignupService::processRouterData(): Router '.$_POST['ServiceRouter'].' not found');
            //            return false;
        }

        $this->userPortInfo = $this->serviceRouter->getUserPortInfo(null, $this->session->get('IPAddress'), $this->session->get('MacAddress'));

        if (empty($this->userPortInfo)) {
            dd('Failed to get user port info');
            //            $userSession->RouterQuery = "failed";
            //            $userSession->invalidUserIP = true;
            //            error_log('ERROR: SignupService::processRouterData(): User port info not found (empty)');
            //            return false;
        }

        if (empty($this->userPortInfo['IPAddress'])) {
            $this->userPortInfo['IPAddress'] = $input['IPAddress'];
        }
        if (empty($this->userPortInfo['MacAddress'])) {
            $this->userPortInfo['MacAddress'] = $input['MacAddress'];
        }

        if (isset($this->userPortInfo['Switch MAC Address']) == false || !$this->userPortInfo['Switch MAC Address']) {
            dd('Switch not found');
            //            $userSession->RouterQuery = "switch not found";
            //            $userSession->invalidUserIP = true;
            //            error_log('ERROR: SignupService::processRouterData(): Switch not found (no MAC found in IP lease)');
            //            return false;
        }




        //        $ip = $input['IPAddress'];
        //        $mac = $input['MacAddress'];
        //        $router = $input['ServiceRouter'];
        //        $origLink = $input['#LinkOrig'];
        //        $loginLink = $input['#LinkLogin'];
        //
        //
        //        dd([$ip, $mac, $router, $origLink, $loginLink]);
        //
        //
        //        $userSession = Session::getInstance();
        //        if ($this->isRequestFromRouter() == false) {
        //            $userSession->RouterQuery = "Query not from a router";
        //            $userSession->invalidUserIP = true;
        //            error_log('ERROR: SignupService::processRouterData(): Query not from a router');
        //            return false;
        //        }
        //        $userSession->techDevice = false;
        //        //            error_log('$this->config[techTestMacs] = '.print_r($this->config['techTestMacs'],true));
        //        if (isset($this->config['techTestMacs'][strtolower($_POST['MacAddress'])]) && $this->config['techTestMacs'][strtolower($_POST['MacAddress'])] == 'yes') {
        //            $userSession->techDevice = true;
        //        }
        //
        //        $this->serviceRouter = new MtikRouter(array('HostName' => $_POST['ServiceRouter']));
        //
        //        if ($this->serviceRouter->isSelected() == false) {
        //            $userSession->RouterQuery = "router not found";
        //            $userSession->invalidUserIP = true;
        //            error_log('ERROR: SignupService::processRouterData(): Router '.$_POST['ServiceRouter'].' not found');
        //            return false;
        //        }

        // Find the user's:
        // 1. Real IP lease (universal NAT or private IP)
        // 2. Switch port info from the DHCP lease
        // if($this->config['testMode'] == false){
        $this->userPortInfo = $this->serviceRouter->getUserPortInfo(null, $_POST['IPAddress'], $_POST['MacAddress']);

        if (empty($this->userPortInfo)) {
            $userSession->RouterQuery = "failed";
            $userSession->invalidUserIP = true;
            error_log('ERROR: SignupService::processRouterData(): User port info not found (empty)');
            return false;
        }

        if (empty($this->userPortInfo['IPAddress'])) {
            $this->userPortInfo['IPAddress'] = $_POST['IPAddress'];
        }
        if (empty($this->userPortInfo['MacAddress'])) {
            $this->userPortInfo['MacAddress'] = $_POST['MacAddress'];
        }

        if (isset($this->userPortInfo['Switch MAC Address']) == false || !$this->userPortInfo['Switch MAC Address']) {
            $userSession->RouterQuery = "switch not found";
            $userSession->invalidUserIP = true;
            error_log('ERROR: SignupService::processRouterData(): Switch not found (no MAC found in IP lease)');
            return false;
        }

        $userSession->RouterQuery = "succeeded";
        $serviceSwitch = new CiscoSwitch();
        $this->serviceSwitch = $serviceSwitch->loadFromDB(NULL, $this->userPortInfo['Switch MAC Address']);
        if ($this->serviceSwitch == null) {
            $userSession->RouterQuery = "switch not found";
            $userSession->invalidUserIP = true;
            error_log('ERROR: SignupService::processRouterData(): Switch not found in DB');
            return false;
        }

        $userSession->serviceSwitch = $this->serviceSwitch;

        if ($this->serviceSwitch['Model'] == 'WS-C2950-24' || $this->serviceSwitch['Model'] == 'WS-C2950T-24') {
            $this->userPortInfo['Switch Port Number'] += 1;
        } else if (strstr($this->serviceSwitch['Model'], 'WS-C6509')) {
            $this->userPortInfo['Switch Port Number'] = $this->userPortInfo['Switch Instance ID'] . '/' . $this->userPortInfo['Switch Port Number'];
        }
        $serviceLocationInfo = getServiceLocationByLocID($userSession->serviceSwitch['LocID']);
        $userSession->ShortName = $serviceLocationInfo['ShortName'];
        //        error_log('SignupService::processRouterData(): serviceSwitch: '. print_r($this->serviceSwitch, true));
        $userSession->PrivateVlan = $this->getPrivateVlan($this->serviceSwitch['NodeID'], $this->userPortInfo['Switch Port Number']);
        //        error_log('SignupService::processRouterData(): $userSession->PrivateVlan = '. $userSession->PrivateVlan);
        $userSession->serviceRouter = $this->serviceRouter->getRouterObject();
        $userSession->serviceRouterName = $_POST['ServiceRouter'];
        $userSession->userPortInfo = $this->userPortInfo;
        return true;



    }







    public function getSignupProducts(Request $request)
    {

        //    return Product::with('getProductsInfo')->get();
        return
            DB::select('SELECT p.id AS ProdID, p.name AS prodname, p.amount, p.frequency,
                         ppv.id_products, ppv.value as speed, ppv.id AS ProdPropValID, ppv.id_product_properties,
                         pp.id AS ProdPropID, pp.description,
                         ppvl.id AS ProdPropValIDLeft, ppvl.value AS slogan, ppvl.id_product_properties AS ProdPropIDLeft
                          FROM  building_products bp
                            INNER JOIN buildings b
                              ON bp.id_buildings = b.id
                            INNER JOIN products p
                              ON bp.id_products = p.id
                            INNER JOIN product_property_values ppv
                              ON ppv.id_products = p.id
                            INNER JOIN product_properties pp
                              ON pp.id = ppv.id_product_properties
                            LEFT JOIN product_property_values ppvl
                              ON ppvl.id_products = p.id
                          WHERE bp.id_buildings = '. $request->id .'
                            AND ppv.id_product_properties = 1 
                            AND ppvl.id_product_properties = 4 
                          ORDER BY speed * 1 asc');
    }
}
