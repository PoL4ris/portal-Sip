<?php

namespace App\Extensions;

use Illuminate\Support\Collection;

class CalendarDataCollector {

    public function __construct($appointmentList) {
    $this->appointments=$appointmentList;

   }

    public function findRepeatVisitsToUnits()
    {

    }

    public function sumServiceCalls()
    {
        $collection = collect($this->appointments);
        return count($collection);
    }

    public function serviceCallTotals()
    {
        $collection = collect($this->appointments);
        $buildingcollection = $collection->unique('bcode');
        $buildinglist = [];
        $actionlist = [];
        $servicelist = [];
        $servicecollection = $collection->unique('service');
        foreach($servicecollection as $service) {
            array_push($servicelist,$service['service']);

            foreach($buildingcollection as $building) {

                array_push($buildinglist,$building['bcode']);
            }

        }
        $actioncollection = $collection->unique('action');
        foreach($actioncollection as $action) {

            array_push($actionlist,$action['action']);
        }
        unset($building);
        $this->appointmentsbybuilding = [];

        foreach($buildinglist as $building) {
            $setofappointments = $collection->where('bcode',$building);
           $this->appointmentsbybuilding[$building] = [];
            foreach($servicelist as $service) {
                $this->appoinmentsbybuilding[$building][$service] = [];

                foreach($actionlist as $action) {
                    $this->appoinmentsbybuilding[$building][$service][$action] = count($setofappointments->where('service',$service)->where('action',$action));
                }

            }
        }

    return $this->appoinmentsbybuilding;

    }

    public function visitsPerTech()
    {
        $collection = collect($this->appointments);
        $techs = $collection->unique('tech');
        $techlist = [];

        foreach($techs as $tech) {
        array_push($techlist,$tech['tech']);
        }

        unset($tech);
        $this->techcount = [];
        foreach($techlist as $tech) {
            $setofappointments = $collection->where( 'tech',$tech);
            array_push($this->techcount,[$tech,count($setofappointments)]);
        }

        return $this->techcount;
    }

    public function visitsPerTimeOfDay()
    {

    }

    public function visitsPerDayOfMonth()
    {

    }


}