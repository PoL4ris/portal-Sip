<?php

namespace App\Extensions;

use App\Models\Building;
use App\Models\RetailRevenue;
use Carbon\Carbon;
use DB;
use Log;

class SIPReporting {

//    private $testMode = true;
//    private $passcode = '$2y$10$igbvfItrwUkvitqONf4FkebPyD0hhInH.Be4ztTaAUlxGQ4yaJd1K';

    public function __construct()
    {
        // DO NOT ENABLE QUERY LOGGING IN PRODUCTION
        //        DB::connection()->enableQueryLog();
//        $configPasscode = config('billing.ippay.passcode');
//        $this->testMode = (Hash::check($configPasscode, $this->passcode)) ? false : true;
    }

    //MRR Process
    public function getBuildingMrrData($code)
    {
        $query = "SELECT ";
        $query .= "MONTH(b.date_time)    as Month, ";
        $query .= "YEAR(b.date_time)     as Year, ";
        $query .= "b.date_time           as Date, ";
        $query .= "c.id                  as CustomerID, ";
        $query .= "c.first_name          as First, ";
        $query .= "c.last_name           as Last, ";
        $query .= "a.unit                as Unit, ";
        $query .= "b.charge_description  as Description, ";
        $query .= "b.transaction_type    as TransactionType, ";
        $query .= "b.amount              as Amount, ";
        $query .= "b.charge_details      as ChargeDetails, ";
        $query .= "a.code                as Code ";
        $query .= "FROM billing_transaction_logs b ";
        $query .= "INNER JOIN customers c ";
        $query .= "ON c.id = b.id_customers ";
        $query .= "INNER JOIN address a ";
        $query .= "ON c.id = a.id_customers ";
        $query .= "WHERE a.code like '%" . $code . "%' ";
        $query .= "AND (b.transaction_type = 'SALE'  OR b.transaction_type = 'CREDIT') ";
        $query .= "AND (b.response_text = 'APPROVED' OR b.response_text = 'RETURN ACCEPTED') ";
        $query .= "ORDER BY ";
        $query .= "YEAR(b.date_time), ";
        $query .= "MONTH(b.date_time), ";
        $query .= "b.transaction_type ";

        return DB::select($query);
    }

    //MRR Process
    public function subtractCredit($buildingMrrTable, $mrr)
    {
        $key = $mrr->Month . '-' . $mrr->Year;

//        if ($buildingMrrTable  == null){
//            Log::debug('subtractCredit(): received null buildingMrrTable. skipping.');
//            return $buildingMrrTable;
//        }


        if (array_key_exists($key, $buildingMrrTable))
        {
            $buildingMrrTable[$key]['amount'] -= $mrr->Amount;
            $buildingMrrTable[$key]['credits'] += $mrr->Amount;
            $buildingMrrTable[$key]['details'][] = array($mrr->Date,
                $mrr->Unit,
                $mrr->First . ' ' . $mrr->Last,
                $mrr->Description,
                (- 1 * $mrr->Amount));
        } else
        {
            $buildingMrrTable[$key]['products'] = array();
            $buildingMrrTable[$key]['productTypes'] = array();
            $buildingMrrTable[$key]['units'] = array($mrr->Unit);
            $buildingMrrTable[$key]['amount'] = $mrr->Amount * - 1;
            $buildingMrrTable[$key]['credits'] = floatval($mrr->Amount);
            $buildingMrrTable[$key]['details'] = array(
                array($mrr->Date,
                    $mrr->Unit,
                    $mrr->First . ' ' . $mrr->Last,
                    $mrr->Description,
                    (- 1 * $mrr->Amount))
            );
        }

        return $buildingMrrTable;
    }

    //MRR Process
    public function addSale($buildingMrrTable, $mrr)
    {

        $key = $mrr->Month . '-' . $mrr->Year;
        $decodedChargeDetails = json_decode($mrr->ChargeDetails, true);
        if ( ! isset($decodedChargeDetails) || ! $decodedChargeDetails){
            Log::debug('addSale(): received empty mrr record. skipping.');
            return $buildingMrrTable;
        }


        $chargeDetailArr = array_shift($decodedChargeDetails);
        $prodName = '';
        $prodType = '';

        if (array_key_exists('ProdName', $chargeDetailArr) == false && count($chargeDetailArr) == 6)
        {
            $prodName = $chargeDetailArr[0];
            $prodType = ucfirst($chargeDetailArr[3]) . ' ' . $chargeDetailArr[2];
        } else
        {
            $prodName = $chargeDetailArr['ProdName'];
            $prodType = ucfirst($chargeDetailArr['ChargeFrequency']) . ' ' . $chargeDetailArr['ProdType'];
        }

        if (array_key_exists($key, $buildingMrrTable))
        {
            if (array_key_exists($prodName, $buildingMrrTable[$key]['products']))
            {
                $buildingMrrTable[$key]['products'][$prodName] += 1;
            } else
            {
                $buildingMrrTable[$key]['products'][$prodName] = 1;
            }

            if (array_key_exists($prodType, $buildingMrrTable[$key]['productTypes']))
            {
                $buildingMrrTable[$key]['productTypes'][$prodType] ++;
            } else
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

        } else
        {
            $buildingMrrTable[$key]['products'] = array($prodName => 1);
            $buildingMrrTable[$key]['productTypes'] = array($prodType => 1);
            $buildingMrrTable[$key]['units'] = array($mrr->Unit);
            $buildingMrrTable[$key]['amount'] = $mrr->Amount;
            $buildingMrrTable[$key]['details'] = array(
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
        if ( ! $buildingMrrTable || ! isset($buildingMrrTable)) {
            Log::debug('updateRetailRevenueDBTable(): received empty building mrr table. skipping.');
            return;
        }

        foreach ($buildingMrrTable as $date => $mrr)
        {
            $carbonDate = Carbon::createFromFormat('m-Y', $date);
            $carbonDate->day('01');
            $month = $carbonDate->format('m');
            $year = $carbonDate->year;

            $rev_record = RetailRevenue::where('month', '=', $month)
                ->where('year', '=', $year)
                ->where('locid', '=', $locid)
                ->first();

            if (isset($rev_record))
            {
                if ($rev_record->status != 'new')
                {
                    Log::info('Skipping data insert for ' . $shortname . ' - ' . $month . '/' . $year);

                    return false;
                }
                Log::info('Updating data for ' . $shortname . ' - ' . $month . '/' . $year . ' ... ');

            } else
            {
                $rev_record = new RetailRevenue;
                $rev_record->locid = $locid;
                $rev_record->shortname = $shortname;
                $rev_record->month = $month;
                $rev_record->year = $year;
                Log::info('Adding data for ' . $shortname . ' - ' . $month . '/' . $year . ' ... ');
            }

            if (array_key_exists('credits', $mrr) == false)
            {
                $mrr['credits'] = 0;
            }

            $revenue_data = json_encode($mrr);
            $rev_record->revenue_data = $revenue_data;
            $rev_record->status = 'new';
            $rev_record->save();
//            Log::info('done');
        }

        return true;
    }


    public function generateMrrReport()
    {
        //mrr process
        $buildingLocation = Building::where('type', '!=', 'commercial')
//            ->where('alias', '125J')
//                ->take(3)
            ->get();

        foreach ($buildingLocation as $location)
        {
            $locid = $location->id;
            $shortname = $location->code;
Log::info($shortname);
            $retailMrr = $this->getBuildingMrrData($shortname);

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
                } else
                {
                    $buildingMrrTable = $this->addSale($buildingMrrTable, $mrr);
                }
            }

            $this->updateRetailRevenueDBTable($locid, $shortname, $buildingMrrTable);
        }

        Log::info('COMPLETE');

    }

}

?>
