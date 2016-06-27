<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;

use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;



class ChartController extends Controller
{
  public function __construct() {
    $this->middleware('auth');
  }

  //FUNCTIONS
  public function getTicketsByMonth()
  {
    $resultQuery = DB::select('SELECT MONTH(created_at) as label, COUNT(*) as value 
                                FROM tickets 
                                  WHERE YEAR(created_at) = 2015 
                                    GROUP BY MONTH(created_at) 
                                      ORDER BY created_at ASC');

    $resultData = array('key'=>"Cumulative Return", 'values' => $resultQuery);
    return $resultData;
  }
  public function fillArray($arrayNum, $bigResult)
  {
    foreach($arrayNum as $k => $rest)
    {
      $bigResult[$k]['values'][] = $rest->value;
    }
    return $bigResult;
  }
  public function getSignedUpCustomersByYear()
  {
    $bigResult = array();
    $labelMonth = array(0 => 'January',1 => 'February',2 => 'March',3 => 'April',4 => 'May',5 => 'June',6 => 'July',7 => 'August',8 => 'September',9 => 'October',10 => 'November',11 => 'December');

    $resultQuery11 = DB::select('SELECT MONTH(signedup_at) AS label, COUNT(*) AS value FROM  customers WHERE YEAR(signedup_at) = 2011 GROUP BY MONTH(signedup_at) ORDER BY signedup_at ASC');
    $bigResult = $this->fillArray($resultQuery11,$bigResult);
    $resultQuery12 = DB::select('SELECT MONTH(signedup_at) AS label, COUNT(*) AS value FROM  customers WHERE YEAR(signedup_at) = 2012 GROUP BY MONTH(signedup_at) ORDER BY signedup_at ASC');
    $bigResult = $this->fillArray($resultQuery12,$bigResult);
    $resultQuery13 = DB::select('SELECT MONTH(signedup_at) AS label, COUNT(*) AS value FROM  customers WHERE YEAR(signedup_at) = 2013 GROUP BY MONTH(signedup_at) ORDER BY signedup_at ASC');
    $bigResult = $this->fillArray($resultQuery13,$bigResult);
    $resultQuery14 = DB::select('SELECT MONTH(signedup_at) AS label, COUNT(*) AS value FROM  customers WHERE YEAR(signedup_at) = 2014 GROUP BY MONTH(signedup_at) ORDER BY signedup_at ASC');
    $bigResult = $this->fillArray($resultQuery14,$bigResult);
    $resultQuery15 = DB::select('SELECT MONTH(signedup_at) AS label, COUNT(*) AS value FROM  customers WHERE YEAR(signedup_at) = 2015 GROUP BY MONTH(signedup_at) ORDER BY signedup_at ASC');
    $bigResult = $this->fillArray($resultQuery15,$bigResult);
    $resultQuery16 = DB::select('SELECT MONTH(signedup_at) AS label, COUNT(*) AS value FROM  customers WHERE YEAR(signedup_at) = 2016 GROUP BY MONTH(signedup_at) ORDER BY signedup_at ASC');
    $bigResult = $this->fillArray($resultQuery16,$bigResult);

    foreach($bigResult as $x => $temp)
    {
      $bigResult[$x]['key'] = $labelMonth[$x];
    }
    return $bigResult;

  }

}
