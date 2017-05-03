<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
use DB;
use Auth;
//Models
use App\Models\NetworkTab;
use App\Models\Reason;
use App\Models\App;
use App\Models\AccessApp;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\Address;
use App\Models\Building;
use App\Models\Status;
use App\Models\User as Users;


class MainController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        DB::connection()->enableQueryLog();
    }


    /**
     * @return Index.blade.php Main page
     * Rename this to Index.
     */
    public function homeView()
    {
        return view('index');
    }
    /**
     * @return Menu with Access Apps permission to each user according to Profiles.
     * Rename to getUserAccessMenu
     */
    public function menuMaker()
    {
        return Users::with('accessApps', 'accessApps.apps')->find(Auth::user()->id);
    }
    /**
     * @return Logged user information.
     */
    public function getUserData()
    {
        return Auth::user();
    }
    /**
     * @return networktab data,
     * REFACTOR AND MOVE TO NETWORKCONTROLLER ALSO RENAME AND UPDATE ON ROUTES.PHP
     */
    public function networkDashboard()
    {
        return NetworkTab::get();
    }
    /**
     * @param Request $request
     * table = table name to get all data of
     * @return table data.
     * MAYBE RENAME AND REFACTOR, RELOCATE OR USE TO IMPROVE LOOKING FOR TABLES.
     */
    public function getTableData(Request $request)
    {
        switch ($request->table) {
            case 'reasons':
                return Reason::all();
        }

    }
    /**
     * @return Reasons data table
     */
    public function getReasonsData()
    {
        return Reason::all();
    }
    /**
     * @return Status table data
     */
    public function getStatus()
    {
        return Status::all();
    }
    /**
     * @return List of all buildings for geoLoc GoogleMap
     */
    public function getBuildingLocations()
    {
        return Address::whereNull('id_customers')->groupBy('id_buildings')->get();
    }





    public function getCustomerCodeSearch(Request $request)//NOT IN USE, REMOVE FROM ROUTES AND FROM HERE AFTER CLEANUP. ONLY IN USE BY PORTAL/LIB.JS
    {
        $stringA = explode("#", $request->string)[0];
        $string = explode(" ", $stringA)[0];

        return Address::with('customer')
            ->where('code', 'LIKE', '%' . $string . '%')
            ->orWhere('unit', 'LIKE', '%' . $string . '%')
            ->take(20)
            ->get();
    }

    public function getCustomersSearch(Request $request)//NOT IN USE, REMOVE FROM ROUTES CLL TO MAIN CONTROLLER AND FROM HERE AFTER CLEANUP. ONLY IN USE BY PORTAL/LIB.JS
    {
        $stringA = explode("#", $request->string)[0];
        $string = explode(" ", $stringA)[0];
        return Customer::where('first_name', 'LIKE', '%' . $string . '%')
            ->orWhere('last_name', 'LIKE', '%' . $string . '%')
            ->orWhere('email', 'LIKE', '%' . $string . '%')
            ->take(20)
            ->get();
    }

    public function getCustomersPreview(Request $request)//NOT IN USE, REMOVE FROM ROUTES AND FROM HERE AFTER CLEANUP. ONLY IN USE BY PORTAL/LIB.JS
    {
        $input = $request->all();
        print '<pre>';

        $customersData = Customer::with('building', 'address', 'contact', 'services', 'type')
            ->where('first_name', 'LIKE', '%' . $input['string'] . '%')
            ->orWhere('last_name', 'LIKE', '%' . $input['string'] . '%')
            ->orWhere('email', 'LIKE', '%' . $input['string'] . '%')
            ->take(2)
            ->get();

        print_r($customersData->toArray());


        die();
    }

    public function getBuildingsSearch(Request $request)//NOT IN USE, REMOVE FROM ROUTES CLL TO MAIN CONTROLLER AND FROM HERE AFTER CLEANUP. ONLY IN USE BY PORTAL/LIB.JS
    {
        $stringA = explode("#", $request->string)[0];
        $string = explode(" ", $stringA)[0];
        return Building::where('name', 'LIKE', '%' . $string . '%')
            ->orWhere('nickname', 'LIKE', '%' . $string . '%')
            ->orWhere('code', 'LIKE', '%' . $string . '%')
            ->orWhere('legal_name', 'LIKE', '%' . $string . '%')
            ->take(20)
            ->get();
    }

    public function getCustomerPoundSearch(Request $request)//NOT IN USE, REMOVE FROM ROUTES CLL TO MAIN CONTROLLER AND FROM HERE AFTER CLEANUP. ONLY IN USE BY PORTAL/LIB.JS
    {
        $string = explode("#", $request->string);
        if (isset($string[1])) {
            return Address::with('customer')
                ->where('code', 'LIKE', '%' . explode(" ", $string[0])[0] . '%')
                ->where('unit', 'LIKE', '%' . $string[1] . '%')
                ->take(20)
                ->get();
        }
        return 'ERROR';
    }

    public function updateUser(Request $request)//NOT IN USE, REMOVE FROM ROUTES CLL TO MAIN CONTROLLER AND FROM HERE AFTER CLEANUP.
    {

        if (empty($request->password))
            DB::table('users')->where('id', $request->id)->update(array('name' => $request->name, 'email' => $request->email, 'access' => strtolower($request->access), 'role' => strtolower($request->role), 'updated_at' => date("Y-m-d H:i:s")));
        else
            DB::table('users')->where('id', $request->id)->update(array('name' => $request->name, 'email' => $request->email, 'password' => password_hash($request->password, PASSWORD_BCRYPT), 'access' => strtolower($request->access), 'role' => strtolower($request->role), 'updated_at' => date("Y-m-d H:i:s")));

        return redirect('/adminusers');
    }

    public function salesdashboard()//OLD DB, NOT IN USE REMOVE FROM ROUTES.PHP
    {
        $salesData = DB::select('SELECT Priority, Status, City, 
                                    Neighborhood, Code, INT_Wiring, 
                                    ShortName, ContactName, ContactPhone, 
                                    ContactEmail, MgmtCo, BuiltDate, Units, 
                                    Floors 
                                      FROM salesPropertyInfo 
                                        ORDER BY Priority ASC');

        return view('sales.dashboard', ['salesdata' => $salesData]);
    }






    public function dummyRouteController()
    {
        $data = array();
        $data['open_tickets'] = Ticket::where('status', '!=', 'closed')->count();
        return $data;
    }//

}

