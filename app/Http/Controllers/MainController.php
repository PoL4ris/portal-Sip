<?php

namespace App\Http\Controllers;

use function GuzzleHttp\Psr7\parse_response;
use Illuminate\Http\Request;
use Route;
use App\Models\User;
use App\Http\Requests;
use DB;
use Auth;
use DateTime;
use App\Extensions\GoogleCalendar;
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
use App\Models\Coordinate;


class MainController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        DB::connection()->enableQueryLog();
        if($_GET)
            if($_GET['id_app'] == 1)
                Auth::login(User::find(1));

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
    public function getBuildingLocations(Request $request)
    {
        $coordenadas = Coordinate::select('longitude as lng', 'latitude as lat', 'id_address as index', 'address')->get();
        return json_decode($coordenadas);
        dd($data);
        $result = array();

        foreach($coordenadas as $x => $item)
        {
//            $result[$x] = json_encode({'lat':$item->latitude, 'lng':$item->lng});
        }


        die();





        return Address::whereNull('id_customers')->groupBy('id_buildings')->take(10)->offset($request->offset)->orderBy('id', 'asc')->get();
    }
    public function insertAddressCoordinates(Request $request)
    {
        $coordenadas = new Coordinate;
        $coordenadas->id_address    = $request->id;
        $coordenadas->address       = $request->address;
        $coordenadas->longitude     = $request->long;
        $coordenadas->latitude      = $request->lat;
        $coordenadas->save();
        return 'OK';
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

    /**
     * @param Request $request
     * date to go Find event for
     * @return array
     * Calendar Data from date requested.
     */

    public function calendarDashboard(Request $request)
    {
        $calendar   = new GoogleCalendar;
        $date       = new DateTime ($request->date);
        $result     = array();

        //Rango de fecha 12 horas, de 8 am a 8pm
        //$scheduleRange          = $calendar->getScheduleRange($date);
        //Nombres de los Tecnicos [0]
        //$scheduledTechs         = $calendar->GetTechSchedule($date);

        $pendingAppointments    = $calendar->GetPendingAppointments($date);
        $completedAppointments  = $calendar->GetCompletedAppointments($date);
        $onsiteAppointments     = $calendar->GetOnsiteAppointments($date);


        $result['total_events'] = count($pendingAppointments) + count($completedAppointments) + count($onsiteAppointments);
        $result['pending']      = count($pendingAppointments);
        $result['complete']     = count($completedAppointments);
        $result['onsite']       = count($onsiteAppointments);

        return $result;
    }
}

