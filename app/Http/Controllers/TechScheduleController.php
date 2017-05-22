<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Extensions\GoogleCalendar;
use DateTime;
use Log;
use stdClass;
use App\Models\Buildings;
use App\Models\Address;
use Validator;
use Config;

class TechScheduleController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');

    }

    /**
     * @param Request $request
     * @return mixed
     * filters google calendar information, returns 2d array for timespan with relevent information for scheduling or rescheduling technicians.
     * accepts a date via $request->date, not required (will display today if no date provided).
     */
    public function GenerateTableSchedule(Request $request)
    {


        $Calendar = new GoogleCalendar;
        if (isset($request->date))
        {
            $date = new DateTime($request->date);
        } else
        {
            $date = new DateTime('now');
        }

        $datenow = new DateTime('now');

        $schedulerange = $Calendar->getScheduleRange($date);  //earliest and latest times someone is working
        $scheduledtechs = $Calendar->GetTechSchedule($date);  //exactly who is working and when

        $pendingAppointments = $Calendar->GetPendingAppointments($date);  //appointments!

        $completedAppointments = $Calendar->GetCompletedAppointments($date); //finished appointments.


        $onsiteAppointments = $Calendar->GetOnsiteAppointments($date);


        $date1 = $schedulerange['start'];
        $date2 = $schedulerange['end'];

        $diff = $date2->diff($date1);

        $hours = $diff->h;
        $hours = $hours + ($diff->days * 24);

        $tablelength = $hours;  // how many rows tall
        $tableoffset = (int) $date1->format('h');  //number of hours prior to start of listings.
        $techcount = count($scheduledtechs);  //also likely the number of columns

        //initilize multidimensional array (damn you php for being a pain in the ass)
        $tablesetup;
        $tablesetup['rows'] = $tablelength;
        $tablesetup['colums'] = $techcount;
        $tablesetup['header'] = [];
        $tablesetup['offset'] = $tableoffset;
        for ($row = 0; $row < $tablelength; $row ++)
        {
            for ($column = 0; $column < $techcount; $column ++)
            {
                if ( ! isset($tablesetup[$row]))
                {
                    $tablesetup[$row] = [];
                }
                $tablesetup[$row][$column] = [];
            }
        }

        //mark table with tech availability.

        unset($row); //just because
        unset($column);
        unset($diff);

        foreach ($scheduledtechs as $key => $value)
        {
            $end = new DateTime($value['end']);
            $start = new DateTime($value['start']);
            $diff = $end->diff($start);
            $starth = $start->format('H');

            $startat = $starth - $tableoffset;
            array_push($tablesetup['header'], $value['tech']);

            for ($x = $startat; $x < $startat + $diff->h; $x ++)
            {
                //echo 'row: ' . $x . ' column: ' . $key . '<br>';
                $provideddate = new DateTime($request->date);
                $provideddate->setTime($x + $tableoffset, 0, 0);
                if ($provideddate <= $datenow)
                {
                    $tablesetup[$x][$key] = ['type' => 'closed', 'tech' => $value['tech'], 'hour' => $x + $tableoffset];
                } else
                {
                    $tablesetup[$x][$key] = ['type' => 'free', 'tech' => $value['tech'], 'hour' => $x + $tableoffset];
                }
            }

        }


        //parse pending and add to table.
        foreach ($pendingAppointments as $appt)
        {
            //parse pending and add to table.
            $tech = $appt['tech'];

            //match tech with table key
            foreach ($scheduledtechs as $key => $value)
            {
                if ($tech == $value['tech'])
                {
                    $hourofappointment = new DateTime($appt['appointment']->getStart()->getDateTime());
                    $hourofappointment = $hourofappointment->format('H') - $tableoffset;
                    $tablesetup[$hourofappointment][$key] = ['type' => 'pending', 'appointment' => $appt['appointment'], 'hour' => $hourofappointment + $tableoffset, 'tech' => $tech, 'eventid' => (string) $appt['appointment']->getID()];

                    //sadly we also need to figure the difference between the hour of the start of the appointment and the end.
                    $endhourofappointment = new DateTime($appt['appointment']->getEnd()->getDateTime());
                    $endhourofappointment = $endhourofappointment->format('H') - $tableoffset;
                    if ($endhourofappointment > $hourofappointment)
                    {
                        for ($z = 0; $z < $endhourofappointment - $hourofappointment; ++ $z)
                        {
                            $tablesetup[$hourofappointment + $z][$key] = ['type' => 'pending', 'appointment' => $appt['appointment'], 'hour' => $hourofappointment + $tableoffset + $z, 'tech' => $tech, 'eventid' => (string) $appt['appointment']->getID()];
                        }
                    }

                }
            }

        }

        foreach ($completedAppointments as $appt)
        {
            //parse pending and add to table.
            $tech = $appt['tech'];

            //match tech with table key
            foreach ($scheduledtechs as $key => $value)
            {
                if ($tech == $value['tech'])
                {
                    $hourofappointment = new DateTime($appt['appointment']->getStart()->getDateTime());
                    $hourofappointment = $hourofappointment->format('H') - $tableoffset;
                    $tablesetup[$hourofappointment][$key] = ['type' => 'completed', 'appointment' => $appt['appointment'], 'hour' => $hourofappointment + $tableoffset, 'tech' => $tech];

                    //sadly we also need to figure the difference between the hour of the start of the appointment and the end.
                    $endhourofappointment = new DateTime($appt['appointment']->getEnd()->getDateTime());
                    $endhourofappointment = $endhourofappointment->format('H') - $tableoffset;
                    if ($endhourofappointment > $hourofappointment)
                    {
                        for ($z = 0; $z < $endhourofappointment - $hourofappointment; ++ $z)
                        {
                            $tablesetup[$hourofappointment + $z][$key] = ['type' => 'completed', 'appointment' => $appt['appointment'], 'hour' => $hourofappointment + $tableoffset + $z, 'tech' => $tech];
                        }
                    }

                }
            }

        }

        foreach ($onsiteAppointments as $appt)
        {
            //parse pending and add to table.
            $tech = $appt['tech'];

            //match tech with table key
            foreach ($scheduledtechs as $key => $value)
            {
                if ($tech == $value['tech'])
                {
                    $hourofappointment = new DateTime($appt['appointment']->getStart()->getDateTime());
                    $hourofappointment = $hourofappointment->format('H') - $tableoffset;
                    $tablesetup[$hourofappointment][$key] = ['type' => 'onsite', 'appointment' => $appt['appointment'], 'hour' => $hourofappointment + $tableoffset, 'tech' => $tech];

                    //sadly we also need to figure the difference between the hour of the start of the appointment and the end.
                    $endhourofappointment = new DateTime($appt['appointment']->getEnd()->getDateTime());
                    $endhourofappointment = $endhourofappointment->format('H') - $tableoffset;
                    if ($endhourofappointment > $hourofappointment)
                    {
                        for ($z = 0; $z < $endhourofappointment - $hourofappointment; ++ $z)
                        {
                            $tablesetup[$hourofappointment + $z][$key] = ['type' => 'onsite', 'appointment' => $appt['appointment'], 'hour' => $hourofappointment + $tableoffset + $z, 'tech' => $tech];
                        }
                    }

                }
            }

        }


        return $tablesetup;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * returns a basic view of the tech scheduler.  No longer relevant probably.
     */
    /*    public function TechScheduler(Request $request)
        {


            return view('techscheduler.scheduler');
        }*/

    /**
     * @param Request $request ->search
     * @return json
     * for given search, return code and address
     */
    public function findBuildingCode(Request $request)
    {

        $search = $request->search;

        $locations = Address::whereNull('id_customers')
            ->where(function ($query) use ($search)
            {
                $query->where('code', 'LIKE', '%' . $search . '%')
                    ->orWhere('address', 'LIKE', '%' . $search . '%');
            })
            ->get();


        return $locations;
    }


    /**
     * @param Request $request
     * @return array
     * returns json with schedule range (earliest start time and latest end time)
     */
    public function getScheduleRange(Request $request)
    {
        $Calendar = new GoogleCalendar;

        if (isset($request->date))
        {
            $date = new DateTime($request->date);
        } else
        {
            $date = new DateTime('now');
        }

        $value = $Calendar->getScheduleRange($date);

        return $value;

    }

    /**
     * @param Request $request
     * @return string
     * for given origin and destination, moves appointment from origin to destination, changes assigned tech if applicable
     */
    public function moveAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin'      => 'required',
            'destination' => 'required'
        ]);

        if ($validator->fails())
        {
            return false;
        }



        $Calendar = new GoogleCalendar;

        $origin = json_decode($request['origin']);
        $destination = json_decode($request['destination']);

        //remove $origin->tech and replace with destination->tech
        $event = $Calendar->service->events->get(Config::get('google.pending_appointment'), $origin->eventid);


        $summary = $event->getSummary();
        $summary = str_replace($origin->tech, $destination->tech, $summary);
        $event->setSummary($summary);

        $eventstart = new DateTime($event->getStart()->getDateTime());
        $eventend = new DateTime($event->getEnd()->getDateTime());

        $diff = $eventstart->diff($eventend);
        $eventstart->setTime($destination->hour, 0, 0);
        $eventend = new DateTime($eventstart->format(DATE_RFC3339));
        $eventend->add($diff);

        $start = new \Google_Service_Calendar_EventDateTime();
        $start->setTimeZone('America/Chicago');
        $start->setDateTime($eventstart->format(DATE_RFC3339));
        $end = new \Google_Service_Calendar_EventDateTime();
        $end->setTimeZone('America/Chicago');
        $end->setDateTime($eventend->format(DATE_RFC3339));

        $event->setStart($start);
        $event->setEnd($end);
        $event->setSummary($summary);
        $updateDescription = $event->getDescription();

        $today = new DateTime();
        $updateDescription.= "\n" . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' Rescheduled on: ' . $today->format('Y-m-d H:i:s') . ' for ' . (new DateTime( $start->getDateTime() ))->format('Y-m-d H:i:s') . "\n";
        $event->setDescription($updateDescription);

        $updatedEvent = $Calendar->service->events->update(Config::get('google.pending_appointment'), $origin->eventid, $event);


        $request->session()->flash('alert-success', $updatedEvent->getSummary() . ' Rescheduled');


        return ($updatedEvent ? 'true' : 'false');
    }

    public function setAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'               => 'required',
            'buildingcode'           => 'required',
            'unit'                   => 'required',
            'selected'               => 'required',
            'service'                => 'required',
            'action'                 => 'required',
            'customername'           => 'required',
            'customerphone'          => 'required',
            'appointmentdescription' => 'required',
        ]);

        if ($validator->fails())
        {
            return redirect('tech-schedule')
                ->withErrors($validator)
                ->withInput();
        }

        $Calendar = new GoogleCalendar;
        //$onset = $Calendar->SetTechAppointment($username,$tech,$buildingcode,$unit,$service,$action,$customername,$customerphone,$appointmentdescription,$startTime,$endTime,$dtvaccount);
        //dtv account number is optional.

        //first we need to figure out which techs were selected and for how long.
        $techlist = array();


        foreach ($request->selected as $block)
        {
            $info = json_decode($block);

            if ( ! isset($techlist[$info->tech]))
            {
                $techlist[$info->tech] = new stdClass;
                $techlist[$info->tech]->start = $info->hour;
                $techlist[$info->tech]->end = $info->hour + 1;
                continue;
            }
            if ($techlist[$info->tech]->start > $info->hour)
            {
                $techlist[$info->tech]->start = $info->hour;
            }
            if ($techlist[$info->tech]->end < $info->hour + 1)
            {
                $techlist[$info->tech]->end = $info->hour + 1;
            }

        }

        $search = $request->buildingcode;
        $locations = Address::whereNull('id_customers')
            ->where('code', '=', $search)
            ->get();


        foreach ($techlist as $key => $value)
        {

            $startTime = new DateTime($request->techscheduledate);
            $startTime->setTime($value->start, 00, 00);
            $endTime = new DateTime($request->techscheduledate);
            $endTime->setTime($value->end, 00, 00);

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
                $locations[0]['address'],
                (isset($request->dtvaccount) && ($request->dtvaccount != '') ? $request->dtvaccount : null));  //dtv account number
            //$onset is the newly created calendar appointment.
        }

        $request->session()->flash('alert-success', $onset->getSummary());

        return redirect('/#/tech-schedule');
    }


    public function GetMyAppointments(Request $request)
    {
        $Calendar = new GoogleCalendar;
        $tech = Auth::user()->alias;

        $date = $request->date ? $request->date : new DateTime();
        $appointments = $Calendar->GetAppointmentsByTech($tech, $date);

        return $appointments;

    }

    public function ChangeAppointmentStatus(Request $request)
    {
        $Calendar = new GoogleCalendar;
        $validator = Validator::make($request->all(), [
            'origin'  => 'required',
            'eventid' => 'required',
            'target'  => 'required',
        ]);

        if ($validator->fails())
        {
            return $validator->errors()->all();
        }

        switch ($request->origin)
        {
            case 'onsite':
                $event = $Calendar->service->events->get(Config::get('google.onsite_appointment'), $request->eventid);
                $origin = Config::get('google.onsite_appointment');
                break;
            case 'pending':
                $event = $Calendar->service->events->get(Config::get('google.pending_appointment'), $request->eventid);
                $origin = Config::get('google.pending_appointment');
                break;
        };

        $description = $event->getDescription();


        switch ($request->target)
        {
            case 'onsite':
                $description .= "\n\n" . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' Onsite at ' . (new DateTime())->format('Y-m-d H:i:s');
                $target = Config::get('google.onsite_appointment');
                break;
            case 'problem':
                $description .= "\n\n" . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' Problem Ticket ' . (new DateTime())->format('Y-m-d H:i:s') . ":\n" . $request->comment;
                $target = Config::get('google.problem_appointment');
                break;
            case 'cancel':
                $description .= "\n\n" . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' Customer Cancelled ' . (new DateTime())->format('Y-m-d H:i:s') . ":\n" . $request->comment;
                $target = Config::get('google.cancelled_appointment');
                break;
            case 'complete':
                $description .= "\n\n" . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' Completed ' . (new DateTime())->format('Y-m-d H:i:s') . ":\n" . $request->comment;
                $target = Config::get('google.completed_appointment');
                break;
            default:
                break;
        };
        $event->setDescription($description);
        $Calendar->service->events->update($origin, $request->eventid, $event);
        $Calendar->service->events->move($origin, $request->eventid, $target);

        return 'updated';
    }


}
