<?php

namespace App\Http\Controllers;

use App\Models\BuildingTicket;
use App\Models\TicketHistory;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Extensions\SIPBilling;
use App\Extensions\SIPSignup;
use App\Extensions\SIPNetwork;
use App\Extensions\BillingHelper;
use App\Extensions\CiscoSwitch;
use App\Extensions\DataMigrationUtils;
use DB;
//use App\User;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\CustomerProduct;
use App\Models\DataMigration;
use App\Models\Address;
use App\Models\BillingTransactionLog;
use App\Models\Building;
use App\Models\Product;
use App\Models\User;
use App\Models\Port;
use App\Models\NetworkNode;
use App\Models\ContactType;
use App\Models\PaymentMethod;
use App\Models\ActivityLog;
use App\Models\RetailRevenue;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Lib\UtilsController;
use Mail;
use Config;
use Auth;
use View;
use Carbon\Carbon;

//use ActivityLogs;
use Symfony\Component\Console\Helper\ProgressBar;

class ReportController extends Controller
{
    public function __construct()
    {
        DB::connection()->enableQueryLog();
    }
    //MRR Process
    public function queryBuild($code)
    {
        $query = "SELECT";
        $query .= "MONTH(b.date_time)    as Month,";
        $query .= "YEAR(b.date_time)     as Year,";
        $query .= "b.date_time           as Date,";
        $query .= "c.id                  as CustomerID,";
        $query .= "c.first_name          as First,";
        $query .= "c.last_name           as Last,";
        $query .= "a.unit                as Unit,";
        $query .= "b.charge_description  as Description,";
        $query .= "b.transaction_type    as TransactionType,";
        $query .= "b.amount              as Amount,";
        $query .= "b.charge_details      as ChargeDetails,";
        $query .= "a.code                as Code";
        $query .= "FROM billing_transaction_logs b";
        $query .= "INNER JOIN customers c";
        $query .= "ON c.id = b.id_customers";
        $query .= "INNER JOIN address a";
        $query .= "ON c.id = a.id_customers";
        $query .= "WHERE a.code like '%" . $code . "%'";
        $query .= "AND (b.transaction_type = 'SALE'  OR b.transaction_type = 'CREDIT')";
        $query .= "AND (b.response_text = 'APPROVED' OR b.response_text = 'RETURN ACCEPTED')";
        $query .= "ORDER BY";
        $query .= "YEAR(b.date_time),";
        $query .= "MONTH(b.date_time),";
        $query .= "b.transaction_type";

        return DB::select($query);
    }

    //MRR Process
    public function subtractCredit($buildingMrrTable, $mrr)
    {
        $key = $mrr->Month . '-' . $mrr->Year;

        if (!$buildingMrrTable || !isset($buildingMrrTable))
            return;

        if (array_key_exists($key, $buildingMrrTable))
        {
            $buildingMrrTable[$key]['amount']   -= $mrr->Amount;
            $buildingMrrTable[$key]['credits']  += $mrr->Amount;
            $buildingMrrTable[$key]['details'][] = array($mrr->Date,
                                                         $mrr->Unit,
                                                         $mrr->First . ' ' . $mrr->Last,
                                                         $mrr->Description,
                                                         (-1 * $mrr->Amount));
        }
        else
        {
            $buildingMrrTable[$key]['products']     = array();
            $buildingMrrTable[$key]['productTypes'] = array();
            $buildingMrrTable[$key]['units']        = array($mrr->Unit);
            $buildingMrrTable[$key]['amount']       = $mrr->Amount * -1;
            $buildingMrrTable[$key]['credits']      = floatval($mrr->Amount);
            $buildingMrrTable[$key]['details']      = array(
                                                            array($mrr->Date,
                                                                  $mrr->Unit,
                                                                  $mrr->First . ' ' . $mrr->Last,
                                                                  $mrr->Description,
                                                                  (-1 * $mrr->Amount))
            );
        }
        return $buildingMrrTable;
    }

    //MRR Process
    public function addSale($buildingMrrTable, $mrr)
    {

        $key = $mrr->Month . '-' . $mrr->Year;
        $decodedChargeDetails = json_decode($mrr->ChargeDetails, true);
        if (!isset($decodedChargeDetails) || !$decodedChargeDetails || !$buildingMrrTable || !isset($buildingMrrTable))
            return;

        $chargeDetailArr = array_shift($decodedChargeDetails);
        $prodName        = '';
        $prodType        = '';

        if (array_key_exists('ProdName', $chargeDetailArr) == false && count($chargeDetailArr) == 6)
        {
            $prodName = $chargeDetailArr[0];
            $prodType = ucfirst($chargeDetailArr[3]) . ' ' . $chargeDetailArr[2];
        }
        else
        {
            $prodName = $chargeDetailArr['ProdName'];
            $prodType = ucfirst($chargeDetailArr['ChargeFrequency']) . ' ' . $chargeDetailArr['ProdType'];
        }

        if (array_key_exists($key, $buildingMrrTable))
        {
            if (array_key_exists($prodName, $buildingMrrTable[$key]['products']))
            {
                $buildingMrrTable[$key]['products'][$prodName] += 1;
            }
            else
            {
                $buildingMrrTable[$key]['products'][$prodName] = 1;
            }

            if (array_key_exists($prodType, $buildingMrrTable[$key]['productTypes']))
            {
                $buildingMrrTable[$key]['productTypes'][$prodType]++;
            }
            else
            {
                $buildingMrrTable[$key]['productTypes'][$prodType] = 1;
            }

            if (array_search($mrr->Unit, $buildingMrrTable[$key]['units']) === false)
            {
                $buildingMrrTable[$key]['units'][] = $mrr->Unit;
            }

            $buildingMrrTable[$key]['amount'] += $mrr->Amount;

            $buildingMrrTable[$key]['details'][] = array($mrr->Date,
                                                         $mrr->Unit,
                                                         $mrr->First . ' ' .
                                                         $mrr->Last,
                                                         $mrr->Description,
                                                         $mrr->Amount);

        }
        else
        {
            $buildingMrrTable[$key]['products']     = array($prodName => 1);
            $buildingMrrTable[$key]['productTypes'] = array($prodType => 1);
            $buildingMrrTable[$key]['units']        = array($mrr->Unit);
            $buildingMrrTable[$key]['amount']       = $mrr->Amount;
            $buildingMrrTable[$key]['details']      = array(
                                                        array($mrr->Date,
                                                              $mrr->Unit,
                                                              $mrr->First . ' ' .
                                                              $mrr->Last,
                                                              $mrr->Description,
                                                              $mrr->Amount)
            );
        }
        return $buildingMrrTable;
    }

    //MRR Process
    public function updateRetailRevenueDBTable($locid, $shortname, $buildingMrrTable)
    {
        if (!$buildingMrrTable || !isset($buildingMrrTable))
            return;
        foreach ($buildingMrrTable as $date => $mrr)
        {
            $carbonDate = Carbon::createFromFormat('m-Y', $date);
            $carbonDate->day('01');
            $month      = $carbonDate->format('m');
            $year       = $carbonDate->year;

            $rev_record = RetailRevenue::where('month', '=', $month)
                                       ->where('year',  '=', $year)
                                       ->where('locid', '=', $locid)
                                       ->first();

            if (isset($rev_record))
            {
                if ($rev_record->status != 'new')
                {
                    echo 'Skipping data insert for ' . $shortname . ' - ' . $month . '/' . $year . '<br/>';
                    return false;
                }
                echo 'Updating data for ' . $shortname . ' - ' . $month . '/' . $year . ' ... ';

            }
            else
            {
                $rev_record             = new RetailRevenue;
                $rev_record->locid      = $locid;
                $rev_record->shortname  = $shortname;
                $rev_record->month      = $month;
                $rev_record->year       = $year;
                echo 'Adding data for ' . $shortname . ' - ' . $month . '/' . $year . ' ... ';
            }

            if (array_key_exists('credits', $mrr) == false) {
                $mrr['credits'] = 0;
            }

            $revenue_data               = json_encode($mrr);
            $rev_record->revenue_data   = $revenue_data;
            $rev_record->status         = 'new';
            $rev_record->save();
            echo ' done<br/>';
        }

        return true;
    }

    //First ROute->calls next one for data.
    public function getDisplayRetailRevenue(Request $request)
    {

        $shortname       = $request->code;
        $bldData         = Building::where('code',$request->code)->first();
        $retailStats     = $this->getRetailRevenue($shortname);

        $monthsArray     = array_keys($retailStats);
        $monthsList      = implode(',', array_keys($retailStats));
        $monthsListArray = explode(',',$monthsList);
        $latestMonth     = $monthsArray[count($monthsArray)-1];

        $data_pointsArr  = array();
        $months          = array();
        $result          = array();
        $x               = $y = 0;

        foreach($retailStats as $statObj)
        {
            $detailsJson        = $statObj->revenue_data;
            $detailsArr         = json_decode($detailsJson,true);
            $data_pointsArr[$x] = $detailsArr['amount'];
            $x++;
        }

        $data_points = $data_pointsArr;

        $result['data']         = $retailStats;
        $result['latestMonth']  = $latestMonth;
        $result['months']       = $monthsListArray;
        $result['shortname']    = $shortname;
        $result['route']        = 'getDisplayRetailRevenue';
        $result['data_points']  = $data_points;
        $result['building']     = $bldData;

        return $result;
        //Laravel View Return OLD
        return view('reports', array('retail_stats' => $retailStats, 'shortname' => $shortname));
    }
    public function getRetailRevenue($shortname = null)
    {
        $carbonDate     = Carbon::today();
        $carbonDate->day('01');
        $currentYear    = $carbonDate->year;
        $carbonDate->subMonths(12);
        $month          = $carbonDate->format('m');
        $year           = $carbonDate->year;

        $retailStatsArr = array();

        if (isset($shortname))
        {
            $retailStats = RetailRevenue::where('shortname', $shortname)
                                        ->where(function ($query) use ($year, $month, $currentYear) {
                                            $query->where('year', '=', $currentYear);
                                            $query->orWhere(function ($query2) use ($year, $month) {
                                                $query2->where('year',  '>=', $year);
                                                $query2->where('month', '>=', $month);
                                            });
                                        })
                                        ->orderBy('year',  'asc')
                                        ->orderBy('month', 'asc')
                                        ->get();

            foreach ($retailStats as $stat)
            {
                $dateFormatter = Carbon::createFromDate($stat->year, $stat->month, '01');
                $retailStatsArr[$dateFormatter->format('M') . '-' . $dateFormatter->format('y')] = $stat;
            }
        }
        return $retailStatsArr;
    }

    //on click bars
    public function getDisplayRetailRevenueDetails(Request $request)
    {
        $shortname  = $request['shortname'];
        $date       = $request['date'];

        if($date != null && $date != '')
        {
            $carbonDate  = Carbon::createFromFormat('M-y-d', $date.'-01');
            $month       = $carbonDate->format('m');
            $year        = $carbonDate->year;
            $retailStats = RetailRevenue::where('shortname', $shortname)
                                        ->where('year',  $year)
                                        ->where('month', $month)
                                        ->get()
                                        ->toArray();

            return array('retail_stat'  => $retailStats[0],
                         'month'        => $carbonDate->format('F'),
                         'year'         => $carbonDate->format('Y'),
                         'shortname'    => $shortname);
            //LARAVEL RETURN VIEW OLD
            return view('reports.retailrevenuedetails', array('retail_stat' => $retailStats[0],'month' => $carbonDate->format('F'),'year' => $carbonDate->format('Y'),    'shortname' => $shortname));
        }
        return null;
    }
    public function getDisplayRetailRevenueUnitDetails(Request $request)
    {
        $shortname  = $request['shortname'];
        $date       = $request['date'];
        if($date != null && $date != '')
        {
            $carbonDate  = Carbon::createFromFormat('F-Y-d', $date.'-01');
            $month       = $carbonDate->format('m');
            $year        = $carbonDate->year;
            $retailStats = RetailRevenue::where('shortname', $shortname)
                                        ->where('year',  $year)
                                        ->where('month', $month)
                                        ->get()
                                        ->toArray();

            return array('retail_stat' => $retailStats[0],
                         'month' => $carbonDate->format('F'),
                         'year'  => $carbonDate->format('Y'));

            //LARAVEL RETURN VIEW OLD
            return view('reports.retailrevenueunitdetails', array('retail_stat' => $retailStats[0],'month' => $carbonDate->format('F'), 'year' => $carbonDate->format('Y')));
        }
        return null;
    }
    public function getDisplayLocationStats(Request $request)
    {
        $allProducts = $this->getAllInternetProductsForLocation($request);
        return array('products' => $allProducts);

        //LARAVEL RETURN VIEW OLD
        return view('reports.retaillocationstats', array('products' => $allProducts, 'shortname' => $shortname));
    }
    public function getAllInternetProductsForLocation($data = null)
    {
        $allProducts = Building::find($data->id_buildings)
                               ->activeParentProducts()
                               ->pluck('product')
                               ->toArray();

        $subbedProductsArr  = array();
        $subbedProducts     = Customer::join('customer_products', 'customer_products.id_customers', '=', 'customers.id')
                                      ->join('address',  'address.id_customers',          '=', 'customers.id')
                                      ->join('products', 'customer_products.id_products', '=', 'products.id')
                                          ->where('products.id_types',           '=', 1)    //1 => Internet
                                          ->where('customer_products.id_status', '=', 3)    //1 => Active BUT 3 is active somehow to this table
                                          ->where('customers.id_status',         '=', 1)    //1 => Active
                                          ->where('address.code',                '=', $data->code)
                                      ->select(DB::raw('count(customers.id) as Total'),
                                                       'products.id',
                                                       'products.name',
                                                       'products.amount',
                                                       'products.frequency')
                                      ->groupby('products.name')
                                      ->get();

        foreach ($subbedProducts as $product)
        {
            $subbedProductsArr[$product->id] = $product;
        }

        foreach($allProducts as $key => $product)
        {
            if(array_key_exists($key, $subbedProductsArr))
            {
                $allProducts[$key]['Total'] = $subbedProductsArr[$key]->Total;
            }
            else
            {
                $allProducts[$key]['Total'] = 0;
            }
        }

        return $allProducts;
    }


    public function supportTest()
    {
        //mrr process
        $buildingLocation = Building::where('type', '!=', 'commercial')->get();

        foreach ($buildingLocation as $location)
        {
            $locid      = $location->id;
            $shortname  = $location->code;

            $retailMrr  = $this->queryBuild($shortname);

            $buildingMrrTable = array();
            foreach ($retailMrr as $mrr)
            {
                $trimmedChargeDetails = trim($mrr->ChargeDetails);

                if (strcasecmp($mrr->TransactionType, 'SALE') == 0 && ($trimmedChargeDetails == null || $trimmedChargeDetails == ''))
                {
                    continue;
                }

                if ($mrr->TransactionType == 'CREDIT')
                {
                    $buildingMrrTable = $this->subtractCredit($buildingMrrTable, $mrr);
                }
                else
                {
                    $buildingMrrTable = $this->addSale($buildingMrrTable, $mrr);
                }
            }

            $this->updateRetailRevenueDBTable($locid, $shortname, $buildingMrrTable);
        }

        dd('COMPLETE');

    }


    public function something(Request $request)
    {
        dd('aaa');
    }
}