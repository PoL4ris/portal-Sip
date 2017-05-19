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
//        return Users::with('accessApps', 'accessApps.apps')->find(Auth::user()->id);
        return Users::find(Auth::user()->id)->accessApps->load('apps')->pluck('apps', 'apps.position')->sortBy('position');
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

    /**
     * @return dashboard Data
     * Commercial Buildings
     * Retail Buildings
     * Open Tickets
     * Average time to close ticket (hours)
     * Average time to close ticket (days)
     */
    public function dashboard()
    {
        //Dashboard data Working
        $result = array();
        $result['commercial'] = Building::where('type', 'like', '%commercial%')->count();
        $result['retail']     = Building::where('type', 'not like', '%commercial%')->count();
        $result['tickets']    = Ticket::where('status', '!=', 'closed')->count();
        $ticketAverage        = DB::select('SELECT 
                                              avg(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as hours,
                                              avg(TIMESTAMPDIFF(DAY, created_at, updated_at))  as days
                                            FROM tickets
                                              where updated_at > created_at
                                              and status like "%closed%"')[0];
        $result['avgHour']    = $ticketAverage->hours;
        $result['avgDay']     = $ticketAverage->days;
        return $result;
    }
}

