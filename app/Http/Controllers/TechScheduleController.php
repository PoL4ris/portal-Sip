<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use App\Extensions\GoogleCalendar;
use DateTime;
use Log;
use stdClass;
use App\Models\Building\Building;
use App\Models\Address;
use Validator;

class TechScheduleController extends Controller
{

    public function __construct() {
        $this->middleware('auth');

    }

    public function GenerateTableSchedule(Request $request) {
        $Calendar = new GoogleCalendar;
        if(isset($request->date)) {
            //dd($request->date);
            //Log::info( print_r( $request->date ) );
            $date = new DateTime($request->date);
        }
        else
        {
            $date = new DateTime('now');
        }
        $datenow = new DateTime('now');

        $schedulerange = $Calendar->getScheduleRange($date);  //earliest and latest times someone is working
        $scheduledtechs = $Calendar->GetTechSchedule($date);  //exactly who is working and when

        $pendingAppointments = $Calendar->GetAppointments($date);  //appointments!

        $completedAppointments = $Calendar->GetCompletedAppointments($date); //finished appointments.


        $onsiteAppointments = $Calendar->GetOnsiteAppointments($date);


        $date1 = $schedulerange['start'];
        $date2 = $schedulerange['end'];

        $diff = $date2->diff($date1);

        $hours = $diff->h;
        $hours = $hours + ($diff->days*24);

        $tablelength = $hours;  // how many rows tall
        $tableoffset = (int) $date1->format('h');  //number of hours prior to start of listings.
        $techcount = count($scheduledtechs);  //also likely the number of columns

        //initilize multidimensional array (damn you php for being a pain in the ass)
        $tablesetup;
        $tablesetup['rows']=$tablelength;
        $tablesetup['colums']=$techcount;
        $tablesetup['header']=[];
        $tablesetup['offset']=$tableoffset;
        for($row = 0 ; $row < $tablelength ; $row++) {
            for($column = 0; $column < $techcount; $column++){
                if(!isset($tablesetup[$row])) {
                    $tablesetup[$row] = [];
                }
                $tablesetup[$row][$column] = [];
            }
        }

        //mark table with tech availability.

        unset($row); //just because
        unset($column);
        unset($diff);

        foreach($scheduledtechs as $key => $value) {
            $end = new DateTime($value['end']);
            $start = new DateTime($value['start']);
            $diff = $end->diff( $start );
            $starth = $start->format('H');

            $startat = $starth - $tableoffset;
            array_push($tablesetup['header'],$value['tech']);

            for($x = $startat;$x<$startat+$diff->h;$x++) {
                //echo 'row: ' . $x . ' column: ' . $key . '<br>';
                $provideddate = new DateTime($request->date);
                $provideddate->setTime($x+$tableoffset,0,0);
                if($provideddate <= $datenow ) {
                    $tablesetup[$x][$key]=['type' => 'closed', 'tech' => $value['tech'], 'hour' => $x+$tableoffset ];
                } else {
                    $tablesetup[$x][$key]=['type' => 'free', 'tech' => $value['tech'], 'hour' => $x+$tableoffset ];
                }
            }

        }


        //parse pending and add to table.
        foreach($pendingAppointments as $appt) {
            //parse pending and add to table.
            $tech=$appt['tech'];

            //match tech with table key
            foreach($scheduledtechs as $key => $value) {
                if($tech == $value['tech']) {
                    $hourofappointment = new DateTime($appt['appointment']->getStart()->getDateTime());
                    $hourofappointment = $hourofappointment->format('H') - $tableoffset;
                    $tablesetup[$hourofappointment][$key] = ['type' => 'pending', 'appointment' => $appt['appointment'] , 'hour' => $hourofappointment+$tableoffset, 'tech' => $tech ];

                    //sadly we also need to figure the difference between the hour of the start of the appointment and the end.
                    $endhourofappointment = new DateTime($appt['appointment']->getEnd()->getDateTime());
                    $endhourofappointment = $endhourofappointment->format('H') - $tableoffset;
                    if($endhourofappointment > $hourofappointment) {
                        for($z=0;$z<$endhourofappointment-$hourofappointment;++$z) {
                            $tablesetup[$hourofappointment+$z][$key] = ['type' => 'pending', 'appointment' => $appt['appointment'] , 'hour' => $hourofappointment+$tableoffset+$z, 'tech' => $tech ];
                        }
                    }

                }
            }

        }

        foreach($completedAppointments as $appt) {
            //parse pending and add to table.
            $tech=$appt['tech'];

            //match tech with table key
            foreach($scheduledtechs as $key => $value) {
                if($tech == $value['tech']) {
                    $hourofappointment = new DateTime($appt['appointment']->getStart()->getDateTime());
                    $hourofappointment = $hourofappointment->format('H') - $tableoffset;
                    $tablesetup[$hourofappointment][$key] = ['type' => 'completed', 'appointment' => $appt['appointment'] , 'hour' => $hourofappointment+$tableoffset, 'tech' => $tech ];

                    //sadly we also need to figure the difference between the hour of the start of the appointment and the end.
                    $endhourofappointment = new DateTime($appt['appointment']->getEnd()->getDateTime());
                    $endhourofappointment = $endhourofappointment->format('H') - $tableoffset;
                    if($endhourofappointment > $hourofappointment) {
                        for($z=0;$z<$endhourofappointment-$hourofappointment;++$z) {
                            $tablesetup[$hourofappointment+$z][$key] = ['type' => 'completed', 'appointment' => $appt['appointment'] , 'hour' => $hourofappointment+$tableoffset+$z, 'tech' => $tech ];
                        }
                    }

                }
            }

        }

        foreach($onsiteAppointments as $appt) {
            //parse pending and add to table.
            $tech=$appt['tech'];

            //match tech with table key
            foreach($scheduledtechs as $key => $value) {
                if($tech == $value['tech']) {
                    $hourofappointment = new DateTime($appt['appointment']->getStart()->getDateTime());
                    $hourofappointment = $hourofappointment->format('H') - $tableoffset;
                    $tablesetup[$hourofappointment][$key] = ['type' => 'onsite', 'appointment' => $appt['appointment'] , 'hour' => $hourofappointment+$tableoffset, 'tech' => $tech ];

                    //sadly we also need to figure the difference between the hour of the start of the appointment and the end.
                    $endhourofappointment = new DateTime($appt['appointment']->getEnd()->getDateTime());
                    $endhourofappointment = $endhourofappointment->format('H') - $tableoffset;
                    if($endhourofappointment > $hourofappointment) {
                        for($z=0;$z<$endhourofappointment-$hourofappointment;++$z) {
                            $tablesetup[$hourofappointment+$z][$key] = ['type' => 'onsite', 'appointment' => $appt['appointment'] , 'hour' => $hourofappointment+$tableoffset+$z, 'tech' => $tech ];
                        }
                    }

                }
            }

        }





        /*
for($row=0;$row<$tablesetup['rows'];$row++) {
	for($column=0;$column<$tablesetup['columns'];$column++) {

	}
}
*/

        //populate existing appointments




        return $tablesetup;

    }

    public function TechScheduler(Request $request) {


        //dd($locations[3]->address[0]->address);

        return view('techscheduler.scheduler');
    }

    public function findBuildingCode(Request $request) {

        $search = $request->search;

        //$locations = Building::with('address')->where('id',"!=",1)->get();

//        $locations = Building::where('id',"!=",1)
//            ->where('code','LIKE',"%$search%")->get();

        $locations = Address::whereNull('id_customers')
            ->where(function($query) use($search){
                $query->where('code','LIKE','%'.$search.'%')
                      ->orWhere('address', 'LIKE', '%'.$search.'%');
            })
            ->get();

        return $locations;
    }





    public function GetScheduledTechs(Request $request) {
        $Calendar = new GoogleCalendar;
        if(isset($request->date)){
            $techSchedule = $Calendar->GetTechSchedule(new DateTime($request->date));  //returns an array of scheduled techs and their hours
        } else {

            $techSchedule = $Calendar->GetTechSchedule(new DateTime('now'));
        }
        //return view('techscheduler.scheduletestview')->with('testvar',$techSchedule);
        return $techSchedule;
    }

    public function GetPendingAppointments(Request $request) {
        $Calendar = new GoogleCalendar;
        if(isset($request->date)){
            $pendingAppointments = $Calendar->GetAppointments(new DateTime($request->date));  //returns an array of scheduled techs and their hours
        } else {
            $pendingAppointments = $Calendar->GetAppointments(new DateTime('now'));
        }
        return $pendingAppointments;
    }

    public function VerifyAvailable(Request $request) {

        //moved to function, example below.  Tech name, start time and end time are required.  uses DateTime format.
        $Calendar = new GoogleCalendar;
        $tech=$request->tech;
        $start=$request->start;
        $end=$request->end;
        if( $Calendar->VerifyTechIsFree($tech,new DateTime($start), new DateTime($end)) ) {
            return 'true';
        } else {
            return 'false';
        }

        return 'false';

    }

    public function getScheduleRange(Request $request) {
        $Calendar = new GoogleCalendar;

        if(isset($request->date))
        {
            $date=new DateTime($request->date);
        } else {
            $date=new DateTime('now');
        }

        $value = $Calendar->getScheduleRange($date);

        return $value;

    }

    public function setAppointment(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'tech' => 'required',
            'buildingcode' => 'required',
            'unit' => 'required',
            'selected' => 'required',
            'service' => 'required',
            'action' => 'required',
            'customername' => 'required',
            'customerphone' => 'required',
            'appointmentdescription' => 'required',
            'selected' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('tech-schedule')
                ->withErrors($validator)
                ->withInput();
        }

        $Calendar = new GoogleCalendar;
        //$onset = $Calendar->SetTechAppointment($username,$tech,$buildingcode,$unit,$service,$action,$customername,$customerphone,$appointmentdescription,$startTime,$endTime,$dtvaccount);
        //dtv account number is optional.

        //first we need to figure out which techs for how long.
        $techlist = array();
        foreach($request->selected as $block) {
            $info=json_decode($block);

            if(!isset( $techlist[$info->tech] )) {
                $techlist[$info->tech] = new stdClass;
                $techlist[$info->tech]->start=$info->hour;
                $techlist[$info->tech]->end=$info->hour+1;
                continue;
            }
            if($techlist[$info->tech]->start > $info->hour) {
                $techlist[$info->tech]->start=$info->hour;
            }
            if($techlist[$info->tech]->end < $info->hour + 1) {
                $techlist[$info->tech]->end=$info->hour + 1;
            }

        }

        foreach($techlist as $key => $value) {

            $startTime = new DateTime($request->date);
            $startTime->setTime($value->start,00,00);
            $endTime = new DateTime($request->date);
            $endTime->setTime($value->end,00,00);

            $onset = $Calendar->SetTechAppointment(
                $request->username, //username
                $key,  //technician name
                $request->buildingcode,  //building code
                $request->unit,  //unit number
                $request->service,  //service
                $request->action,  //service action
                $request->customername, //customername
                $request->customerphone, //customerphone
                $request->appointmentdescription,  //appointment descrioption
                $startTime,  //start time (dont' forget to set the date on the start and end times)
                $endTime,  //end time
                null);  //dtv account number
            //$onset is the newly created calendar appointment.
        }

        $request->session()->flash('alert-success', 'Scheduled');
        return redirect('tech-schedule');
    }


    public function gCalList() {
        //Deletable example calling List available.
        //Note This does NOT return the primary Calendar.  For that Something else must be done.

        $Calendar = new GoogleCalendar;
        $results = $Calendar->ListAvailableCalendars();
        return view('techscheduler.scheduletestview')->with('gCalList',$results);

    }

    public function listAvailableWithName() {
        //Also Deletable, list all calendar id's + whatever the primary is.  possibly won't list the main calendar we are interested in at help@silverip.com

        $GCalIDList = [];
        $Calendar = new GoogleCalendar;
        $results = $Calendar->ListAvailableCalendars();
        foreach($results as $result) {
            array_push($GCalIDList, [$result->id,$result->getSummary()]);
        }
        $primary = $Calendar->get('primary');

        array_push($GCalIDList, [$primary->id,$primary->getSummary()]);

        return view('techscheduler.scheduletestview')->with('testvar',$GCalIDList);
    }



}
