<?php

namespace App\Extensions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

use DateTime;
use DateTimeZone;
use App\Models\Address;

class GoogleCalendar {

    public $client;

    public $service;

    function __construct()
    {
        /* Get config variables */
        $client_id = Config::get('google.client_id');
        $service_account_name = Config::get('google.service_account_name');
        $key_file_location = base_path() . Config::get('google.key_file_location');

        $this->client = new \Google_Client();
        $this->client->setApplicationName("silverip-portal-v2");
        $this->service = new \Google_Service_Calendar($this->client);

        /* If we have an access token */
        if (Cache::has('service_token'))
        {
            $this->client->setAccessToken(Cache::get('service_token'));
        }

        $key = file_get_contents($key_file_location);
        /* Add the scopes you need */
        $scopes = array('https://www.googleapis.com/auth/calendar');
        $cred = new \Google_Auth_AssertionCredentials(
            $service_account_name,
            $scopes,
            $key
        );

        $this->client->setAssertionCredentials($cred);
        if ($this->client->getAuth()->isAccessTokenExpired())
        {
            $this->client->getAuth()->refreshTokenWithAssertion($cred);
        }
        Cache::forever('service_token', $this->client->getAccessToken());

    }

    public function get($calendarId, $optparams = [])
    {

        $results = $this->service->calendars->get($calendarId, $optparams);

        return $results;
    }

    public function all($calendarId)
    {

        $results = $this->service->calendars->get($calendarId);

        return $results;
    }

//start custom codes

    /**
     * @return \Google_Service_Calendar_CalendarList
     */
    public function ListAvailableCalendars()
    {
        $results = $this->service->calendarList->listCalendarList();

        return $results;
    }

    /**
     * @param DateTime $date
     * defines earliest and latest tech availability on given date.
     * @return array
     */
    public function GetScheduleRange(DateTime $date)
    {

        //needed for the schedule builder to determine length of schedule.
        $startofday = null;
        $endofday = null;


        $techSchedule = $this->GetTechSchedule($date);

        foreach ($techSchedule as $info)
        {
            if ( ! isset($startofday))
            {
                $startofday = new DateTime($info['start'], new DateTimeZone('America/Chicago'));
            }
            if (new DateTime($info['start'], new DateTimeZone('America/Chicago')) < $startofday)
            {
                $startofday = new DateTime($info['start']);
            }

            if ( ! isset($endofday))
            {
                $endofday = new \DateTime($info['end'], new DateTimeZone('America/Chicago'));
            }
            if (new DateTime($info['end'], new DateTimeZone('America/Chicago')) > $endofday)
            {
                $endofday = new DateTime($info['end']);
            }
        }

        $value = ['start' => $startofday, 'end' => $endofday];

        return $value;

    }

    /**
     * @param DateTime $date
     * @return array indicates each techs work hours.
     */
    public function GetTechSchedule(DateTime $date)
    {
        //lists the technicians that are working and their hours on a given date.
        $timeMin = new DateTime($date->format(DATE_RFC3339));
        $timeMin->setTime(00, 00, 00);

        $timeMax = new DateTime($date->format(DATE_RFC3339));
        $timeMax->setTime(23, 59, 59);

        $optParams = array(
            'maxResults'   => 2500,
            'orderBy'      => 'startTime',
            'singleEvents' => true,
            'timeMin'      => $timeMin->format(DATE_RFC3339),
            'timeMax'      => $timeMax->format(DATE_RFC3339)
        );


        $scheduledtechs = $this->service->events->listEvents(Config::get('google.schedule_appointment'), $optParams);

        $listing = [];
        foreach ($scheduledtechs as $techevent)
        {
            array_push($listing, ['tech' => $techevent->getSummary(), 'start' => $techevent->getStart()->getDateTime(), 'end' => $techevent->getEnd()->getDateTime()]);
        }

        return $listing;

    }

    /**
     * @param DateTime $date
     * @return array of all appointments completed on given date
     */
    public function GetCompletedAppointments(DateTime $date)
    {

        $timeMin = new DateTime($date->format(DATE_RFC3339));
        $timeMin->setTime(00, 00, 00);

        $timeMax = new DateTime($date->format(DATE_RFC3339));
        $timeMax->setTime(23, 59, 59);
        $optParams = array(
            'maxResults'   => 2500,
            'orderBy'      => 'startTime',
            'singleEvents' => true,
            'timeMin'      => $timeMin->format(DATE_RFC3339),
            'timeMax'      => $timeMax->format(DATE_RFC3339)
        );
        $listing = [];

        $scheduledtechs = $this->service->events->listEvents(Config::get('google.schedule_appointment'), $optParams);
        //should have google schedule now, but let's process the appointments and make a cleaner table.
        $techsworking = [];
        foreach ($scheduledtechs as $techevent)
        {
            array_push($techsworking, $techevent->getSummary());
        }


        //$availableTechCalendar = $this->all(Config::get('google.schedule_appointment') );
        //dd($availableTechCalendar);
        $pending_appointments = $this->service->events->listEvents(Config::get('google.completed_appointment'), $optParams);
        //should have google schedule now, but let's process the appointments and make a cleaner table.

        foreach ($pending_appointments as $appointment)
        {

            foreach ($techsworking as $tech)
            {
                //find out which tech an appointment is scheduled for.
                $summary = $appointment->getSummary();
                if ($this->foundinstring($summary, $tech))
                {
                    $appointmentend = $appointment->getEnd()->getDateTime();
                    $appointmentstart = $appointment->getStart()->getDateTime();

                    array_push($listing, ['tech' => $tech, 'appointment' => $appointment, 'start' => $appointmentstart, 'end' => $appointmentend, 'type' => 'completed']);
                }
            }


        }


        // dd($pending_appointments);
        return $listing;

    }

    /**
     * just a helper function, finds $needle in $haystack
     * @param $haystack
     * @param $needle
     * @return bool
     */
    private function foundinstring($haystack, $needle)
    {
        $result = strpos($haystack, $needle);
        if ($result !== false)
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function GetValidCompletedAppointmentsInRange(DateTime $startdate, DateTime $enddate)
    {

        $optParams = array(
            'maxResults'   => 2500,
            'orderBy'      => 'startTime',
            'singleEvents' => true,
            'timeMin'      => $startdate->format(DATE_RFC3339),
            'timeMax'      => $enddate->format(DATE_RFC3339)
        );
        $listing = [];


        //to avoid multiple db calls we are going to do one right now to get a list of building codes to check against.
        $bcodelist = Address::whereNull('id_customers')->pluck('code')->toArray();

        $appointments = $this->service->events->listEvents(Config::get('google.completed_appointment'), $optParams);
        //should have google schedule now, but let's process the appointments and make a cleaner table.
        $lastappointmentstart = null;
        foreach ($appointments as $appointment)
        {


            $appointmentend = new DateTime($appointment->getEnd()->getDateTime());
            $appointmentstart = new DateTime($appointment->getStart()->getDateTime());

if($appointmentstart == null) {
    continue;
}
            $firstDate = $appointmentstart->format('Y-m-d');
if($lastappointmentstart != null)
{
    $secondDate = $lastappointmentstart->format('Y-m-d');
} else {
    $secondDate = null;
}
            if($firstDate != $secondDate) {
                $optParams = array(
                    'maxResults'   => 2500,
                    'orderBy'      => 'startTime',
                    'singleEvents' => true,
                    'timeMin'      => (new DateTime($firstDate))->modify('12:01 a.m.')->format(DATE_RFC3339),
                    'timeMax'      => (new DateTime($firstDate))->modify('11:59 p.m.')->format(DATE_RFC3339)
                );
                $scheduledtechs = $this->service->events->listEvents(Config::get('google.schedule_appointment'), $optParams);
                $lastappointmentstart = new DateTime($firstDate);
            }

            $appointment = ['appointment' => $appointment, 'start' => $appointmentstart->format(DATE_RFC3339), 'end' => $appointmentend->format(DATE_RFC3339), 'type' => 'completed'];
            $validatecheck = $this->validateAppointment($appointment, $bcodelist, $scheduledtechs);
            if ($validatecheck)
            {
                array_push($listing, $appointment);
            }


        }


        // dd($pending_appointments);
        return $listing;

    }

    /**
     * @param $user
     * @param $tech
     * @param $buildingcode
     * @param $unit
     * @param $service (int or tv)
     * @param $action (connect repair or other)
     * @param $customername
     * @param $customerphone
     * @param $appointmentdescription
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @param null $address
     * @param null $dtvaccount
     * @return \Google_Service_Calendar_Event
     */
    public function SetTechAppointment($user, $tech, $buildingcode, $unit, $service, $action, $customername, $customerphone, $appointmentdescription, DateTime $startTime, DateTime $endTime, $address = null, $dtvaccount = null)
    {
        $date = new $startTime;

        $address;  //should be based off of buildingcode

        $Calendar = new GoogleCalendar; //a calendar hook.

        $summary = "({$tech}) ";
        $summary .= "{$buildingcode} ";
        $summary .= "#{$unit} ";
        $summary .= "{$service} ";
        $summary .= "{$action}";

        $description = "";
        $description .= "{$customername}\n";
        $description .= "{$customerphone}\n";
        if (isset($dtvaccount))
        {
            $description .= "DTV# {$dtvaccount}\n\n";
        } else
        {
            $description .= "\n";
        }

        $description .= "{$appointmentdescription}\n\n";
        $description .= "Scheduled By:\n";
        $description .= $user . " on " . (new DateTime('now'))->format(DATE_RFC3339);

        $event = new \Google_Service_Calendar_Event;
        $start = new \Google_Service_Calendar_EventDateTime();
        $end = new \Google_Service_Calendar_EventDateTime();;
        $start->setDateTime($startTime->format(DATE_RFC3339));
        $start->setTimeZone('America/Chicago');
        $end->setDateTime($endTime->format(DATE_RFC3339));
        $end->setTimeZone('America/Chicago');
        $event->setStart($start);
        $event->setEnd($end);
        $event->setSummary($summary);
        $event->setDescription($description);
        if ($address)
        {
            $event->setLocation($address);
        }


//set the event on the pending calendar.
        $googleresponse = $Calendar->service->events->insert(Config::get('google.pending_appointment'), $event, []);

//$googleresponse is just the newly created appointment in case we need anything else done with it.

        return $googleresponse;
    }  //does this contain that?

    /**
     * checks that tech is not already booked in given datetime range.
     * @param $tech
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @return bool
     */
    public function VerifyTechIsFree($tech, DateTime $startTime, DateTime $endTime)
    {

        //verify that tech is free at particular date and time.
        $techSchedule = $this->GetTechSchedule($startTime);

        $pendingAppointments = $this->GetPendingAppointments($startTime);


        $techavailable = false;
        $scheduleconflict = true;
        //check that time exists during techs scheduled hours
        foreach ($techSchedule as $key => $value)
        {

            if ($value['tech'] == $tech)
            {
                //if this tech is the tech we are looking for
                //make sure requested startTime/endTime is within technician schedule.
                if (max(new \DateTime($value['start'], new \DateTimeZone('America/Chicago')), $startTime) <= min(new \DateTime($value['end'], new \DateTimeZone('America/Chicago')), $endTime))
                {
                    $techavailable = true;

                    break;
                } else
                {
                    $techavailable = false;

                }
            }
        }

        //check that time does not overlap any exisiting appointments for that tech
        foreach ($pendingAppointments as $key => $value)
        {
            if ($tech != $value['tech'])
            {
                continue;
            } else
            {
                if (max(new \DateTime($value['appointment']->getStart()->getDateTime(), new \DateTimeZone('America/Chicago')), $startTime) <= min(new \DateTime($value['appointment']->getEnd()->getDateTime(), new \DateTimeZone('America/Chicago')), $endTime))
                {

                    $scheduleconflict = true;
                    break;
                } else
                {
                    $scheduleconflict = false;
                }
            }
        }
        if ($techavailable && ! $scheduleconflict)
        {
            return true;
        } else
        {
            return false;

        }

    }

    /**
     * for given date return array of all pending appointments.
     * @param DateTime $date
     * @return array
     */
    public function GetPendingAppointments(DateTime $date)
    {

        $timeMin = new DateTime($date->format(DATE_RFC3339));
        $timeMin->setTime(00, 00, 00);

        $timeMax = new DateTime($date->format(DATE_RFC3339));
        $timeMax->setTime(23, 59, 59);
        $optParams = array(
            'maxResults'   => 2500,
            'orderBy'      => 'startTime',
            'singleEvents' => true,
            'timeMin'      => $timeMin->format(DATE_RFC3339),
            'timeMax'      => $timeMax->format(DATE_RFC3339)
        );
        $listing = [];

        $scheduledtechs = $this->service->events->listEvents(Config::get('google.schedule_appointment'), $optParams);
        //should have google schedule now, but let's process the appointments and make a cleaner table.
        $techsworking = [];
        foreach ($scheduledtechs as $techevent)
        {
            array_push($techsworking, $techevent->getSummary());
        }

        $pending_appointments = $this->service->events->listEvents(Config::get('google.pending_appointment'), $optParams);
        //should have google schedule now, but let's process the appointments and make a cleaner table.

        foreach ($pending_appointments as $appointment)
        {

            foreach ($techsworking as $tech)
            {
                //find out which tech an appointment is scheduled for.
                $summary = $appointment->getSummary();
                if ($this->foundinstring($summary, $tech))
                {

                    $appointmentend = $appointment->getEnd()->getDateTime();
                    $appointmentstart = $appointment->getStart()->getDateTime();

                    array_push($listing, ['tech' => $tech, 'appointment' => $appointment, 'start' => $appointmentstart, 'end' => $appointmentend, 'type' => 'pending']);
                }
            }


        }


        return $listing;

    }

    public function GetProblemAppointments(DateTime $date)
    {

        $timeMin = new DateTime($date->format(DATE_RFC3339));
        $timeMin->setTime(00, 00, 00);

        $timeMax = new DateTime($date->format(DATE_RFC3339));
        $timeMax->setTime(23, 59, 59);
        $optParams = array(
            'maxResults'   => 2500,
            'orderBy'      => 'startTime',
            'singleEvents' => true,
            'timeMin'      => $timeMin->format(DATE_RFC3339),
            'timeMax'      => $timeMax->format(DATE_RFC3339)
        );
        $listing = [];

        $scheduledtechs = $this->service->events->listEvents(Config::get('google.schedule_appointment'), $optParams);
        //should have google schedule now, but let's process the appointments and make a cleaner table.
        $techsworking = [];
        foreach ($scheduledtechs as $techevent)
        {
            array_push($techsworking, $techevent->getSummary());
        }

        $problem_appointments = $this->service->events->listEvents(Config::get('google.problem_appointment'), $optParams);
        //should have google schedule now, but let's process the appointments and make a cleaner table.

        foreach ($problem_appointments as $appointment)
        {

            foreach ($techsworking as $tech)
            {
                //find out which tech an appointment is scheduled for.
                $summary = $appointment->getSummary();
                if ($this->foundinstring($summary, $tech))
                {

                    $appointmentend = $appointment->getEnd()->getDateTime();
                    $appointmentstart = $appointment->getStart()->getDateTime();

                    array_push($listing, ['tech' => $tech, 'appointment' => $appointment, 'start' => $appointmentstart, 'end' => $appointmentend, 'type' => 'pending']);
                }
            }


        }


        return $listing;

    }

    /**
     * for given tech on given date return all that technicians appointments.
     * @param $tech
     * @param DateTime $date
     * @return array
     */
    public function GetAppointmentsByTech($tech, DateTime $date)
    {

        $pending = $this->GetPendingAppointments($date);
        $onsite = $this->GetOnsiteAppointments($date);

        $totallist = [];

        foreach ($onsite as $event)
        {
            if ($event['tech'] == $tech)
            {
                array_push($totallist, $event);
            }
        }

        foreach ($pending as $event)
        {
            if ($event['tech'] == $tech)
            {
                array_push($totallist, $event);
            }
        }

        return $totallist;
    }

    /**
     * @param DateTime $date
     * @return array containing all current techs that are marked as onsite
     */
    public function GetOnsiteAppointments(DateTime $date)
    {

        $timeMin = new DateTime($date->format(DATE_RFC3339));
        $timeMin->setTime(00, 00, 00);

        $timeMax = new DateTime($date->format(DATE_RFC3339));
        $timeMax->setTime(23, 59, 59);
        $optParams = array(
            'maxResults'   => 2500,
            'orderBy'      => 'startTime',
            'singleEvents' => true,
            'timeMin'      => $timeMin->format(DATE_RFC3339),
            'timeMax'      => $timeMax->format(DATE_RFC3339)
        );
        $listing = [];

        $scheduledtechs = $this->service->events->listEvents(Config::get('google.schedule_appointment'), $optParams);
        //should have google schedule now, but let's process the appointments and make a cleaner table.
        $techsworking = [];
        foreach ($scheduledtechs as $techevent)
        {
            array_push($techsworking, $techevent->getSummary());
        }

        $onsite_appointments = $this->service->events->listEvents(Config::get('google.onsite_appointment'), $optParams);


        foreach ($onsite_appointments as $appointment)
        {

            foreach ($techsworking as $tech)
            {
                //find out which tech an appointment is scheduled for.
                $summary = $appointment->getSummary();
                if ($this->foundinstring($summary, $tech))
                {
                    $appointmentend = $appointment->getEnd()->getDateTime();
                    $appointmentstart = $appointment->getStart()->getDateTime();

                    array_push($listing, ['tech' => $tech, 'appointment' => $appointment, 'start' => $appointmentstart, 'end' => $appointmentend, 'type' => 'onsite']);
                }
            }


        }

        return $listing;

    }


    //given an appointment this function determines if it has a valid building code, while a tech wash schedule, and that the service and action are valid
    //$appointment will be modified with some additional information. bcode service action and tech will be added.
    //passing in a building list and a list of the scheduled techs will speed this function up since it will not have to make it's own db calls.
    protected function validateAppointment(&$appointment, $buildinglist = null, $scheduledtechs = null)
    {

        $codevalid = 0;
        $servicevalid = 0;
        $actionvalid = 0;
        $techvalid = 0;

        $apptSummary = $appointment['appointment']->getSummary();
        $explodedSummary = explode(" ", $apptSummary);
        $countSum = count($explodedSummary);
        if ($countSum != 5)
        {
            return 0;
        }

        //need to define range of techs.

        if (($daystart = new DateTime($appointment['appointment']->getStart()->getDateTime())) != null)
        {
            $daystart->modify("12:01 a.m.");
            $dayend = new DateTime($appointment['appointment']->getEnd()->getDateTime());
            $dayend->modify("11:59 p.m.");


            $optParams = array(
                'maxResults'   => 2500,
                'orderBy'      => 'startTime',
                'singleEvents' => true,
                'timeMin'      => $daystart->format(DATE_RFC3339),
                'timeMax'      => $dayend->format(DATE_RFC3339)
            );
        } else
        {
            return false;
        }

        if ($scheduledtechs == null)
        {
            $scheduledtechs = $this->service->events->listEvents(Config::get('google.schedule_appointment'), $optParams);
        }


        //should have google schedule now, but let's process the appointments and make a cleaner table.
        $techsworking = [];
        foreach ($scheduledtechs as $techevent)
        {
            array_push($techsworking, $techevent->getSummary());
        }


        //validate 2nd field of summary (building code) against valid building codes, for the sake of not making a million db calls
        //we can pass in a valid list, however if list is not present db calls will be made.
        $apptSummary = $appointment['appointment']->getSummary();
        $explodedSummary = explode(' ', $apptSummary);
        $search = $explodedSummary[1];
        $bcode = $explodedSummary[1];

        $tech = trim($explodedSummary[0], ' ()');
        $tech = trim($tech);

        if (in_array($tech, $techsworking))
        {
            $techvalid = 1;
            $appointment['tech'] = $tech;
        } else
        {
            $techvalid = 0;
        }

        //this is the one at a time search, does not check the list passed in.
        if ($buildinglist == null)
        {
            $searchresult = Address::whereNull('id_customers')->where('code', '=', $search)->get();
            //dd($regionsearch);
            if ( ! $searchresult->isEmpty() && $bcode == $searchresult->first()->code)
            {
                $codevalid = 1;
                $appointment['bcode'] = $bcode;
            } else
            {
                $codevalid = 0;
            }
        } else
        {
            //need code to check against provided list.
            if (in_array($bcode, $buildinglist))
            {
                $codevalid = 1;
                $appointment['bcode'] = $bcode;
            } else
            {
                $codevalid = 0;
            }
        }

//check for a valid services, this should probably be turned into a function with definable services in the site config
        switch ($explodedSummary[3])
        {
            case "TV/INT":
                $appointment['service'] = 'tvint';
                $servicevalid = 1;
                break;
            case "TV":
                $appointment['service'] = 'tv';
                $servicevalid = 1;
                break;
            case "INT":
                $appointment['service'] = 'int';
                $servicevalid = 1;
                break;
            default:
                $servicevalid = 0;
                break;
        }

        //and check for a valid service action.
        switch ($explodedSummary[4])
        {
            case "Connect":

                $actionvalid = 1;
                $appointment['action'] = 'connect';
                break;
            case "Repair":
                $actionvalid = 1;
                $appointment['action'] = 'repair';
                break;
            case "Other":
                $actionvalid = 1;
                $appointment['action'] = 'other';
                break;
            default:
                $actionvalid = 0;
                break;
        }

        if ($codevalid && $servicevalid && $actionvalid && $techvalid)
        {

            return true;
        } else
        {
            return false;
        }

        return false;
    }


}
