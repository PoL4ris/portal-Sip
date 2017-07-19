<?php

namespace App\Extensions;

use App\Models\BillingTransactionLog;
use App\Models\Building;
use App\Models\RetailRevenue;
use App\Models\ScheduledJob;
use Carbon\Carbon;
use DB;
use Log;
use DateTimeZone;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\ProgressBar;

class SIPReporting {

//    private $testMode = true;
//    private $passcode = '$2y$10$igbvfItrwUkvitqONf4FkebPyD0hhInH.Be4ztTaAUlxGQ4yaJd1K';
    private $jobNames = ['generate-mrr-report-job' => 'report:generate-mrr-report'];
    private $console;

    public function __construct($console = false)
    {
        if ($console == true)
        {
            $this->console = $console;
            $this->output = new ConsoleOutput();
        }

        // DO NOT ENABLE QUERY LOGGING IN PRODUCTION
        //        DB::connection()->enableQueryLog();
//        $configPasscode = config('billing.ippay.passcode');
//        $this->testMode = (Hash::check($configPasscode, $this->passcode)) ? false : true;
    }

    //MRR Process
    public function getBuildingMrrData($code, $month = '', $year = '')
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

        if ($month != '')
        {
            $query .= "AND MONTH(b.date_time) = '" . $month . "' ";
        }

        if ($year != '')
        {
            $query .= "AND YEAR(b.date_time) = '" . $year . "' ";
        }

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
        if ( ! isset($decodedChargeDetails) || ! $decodedChargeDetails)
        {
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
        if ( ! $buildingMrrTable || ! isset($buildingMrrTable))
        {
            Log::debug('updateRetailRevenueDBTable(): received empty building mrr table. skipping.');

            return;
        }

        foreach ($buildingMrrTable as $date => $mrr)
        {
            $carbonDate = Carbon::createFromFormat('m-Y', $date, 'America/Chicago');
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
                    Log::debug('Skipping data insert for ' . $shortname . ' - ' . $month . '/' . $year);

                    return false;
                }
                Log::debug('Updating data for ' . $shortname . ' - ' . $month . '/' . $year . ' ... ');

            } else
            {
                $rev_record = new RetailRevenue;
                $rev_record->locid = $locid;
                $rev_record->shortname = $shortname;
                $rev_record->month = $month;
                $rev_record->year = $year;
                Log::debug('Adding data for ' . $shortname . ' - ' . $month . '/' . $year . ' ... ');
            }

            if (array_key_exists('credits', $mrr) == false)
            {
                $mrr['credits'] = 0;
            }

            $revenue_data = json_encode($mrr);
            $rev_record->revenue_data = $revenue_data;
            $rev_record->status = $this->getStatus($carbonDate);
            $rev_record->save();
        }

        return true;
    }

    protected function getStatus(Carbon $carbonDate) //, $dataType)
    {

        $carbonNow = Carbon::now(new DateTimeZone('America/Chicago'));

        $month = $carbonDate->format('m');
        $day = $carbonDate->format('d');
        $year = $carbonDate->format('Y');

//        if ($dataType == 'daily')
//        {
//            return $carbonNow->diffInDays($carbonDate) > 1 ? 'active' : 'new';
//        }
//
//        if ($dataType == 'monthly' || $dataType == 'master')
//        {
        return $carbonNow->diffInMonths($carbonDate) >= 1 ? 'active' : 'new';
//        }
//
//        if ($dataType == 'annual')
//        {
//            return $carbonNow->diffInYears($carbonDate) >= 1 ? 'active' : 'new';
//        }

//        return 'new';
    }

    public function runMrrReportJob()
    {
        $jobName = 'generate-mrr-report-job';
//        if ($this->isJobEnabled($jobName) == false)
//        {
//            Log::notice('Job: ' . $this->jobNames[$jobName] . ' is disabled');
//
//            return false;
//        }

        $this->setJobStatus($jobName, 'running');
        Log::notice('Job: ' . $this->jobNames[$jobName] . ' starting ...');

        $this->generateMrrReport();
        $this->updateJobTimestamp($jobName);

        Log::notice('Job: ' . $this->jobNames[$jobName] . ' stopping.');
        $this->setJobStatus($jobName, 'stopped');
    }

    protected function getActiveDataMonthsForBuilding($buildingCode)
    {
        return RetailRevenue::where('shortname', $buildingCode)
            ->where('status', 'active')
            ->get();
    }

    protected function generateMrrReport()
    {
        $month = '07';
        $year = '2017';

        //mrr process
        $buildings = Building::where('type', '!=', 'commercial')
//            ->where('alias', '235V')
            ->get();


//        foreach ($tables as $table)
//        {
//            DB::table($table)->truncate();
//            $this->advanceProgressBar();
//        }
//        $this->stopProgressBar();
//
//
//
//        if ($dataMigration == null)
//        {
//            $this->progressBarError('<fg=magenta>Could not find ' . $legacyTableName . ' in the data_migrations table. Ignoring.</>');
//            $this->stopProgressBar();
//            Log::info('Could not find ' . $legacyTableName . ' in the data_migrations table. Ignoring.');
//
//            return false;
//        }
//
//        // Update the progress bar
//        $this->advanceProgressBar(0, $dataMigration->records_processed);
//
//        $this->writeLog('<info> neighborhoods table is not empty. Skipping.</info>');

        $this->startProgressBar($buildings->count(), 'Generating MRR Reports');
        foreach ($buildings as $building)
        {
//            $activeMonths = getActiveDataMonthsForBuilding($building->alias);

            $locid = $building->id;
            $shortname = $building->code;
            $retailMrr = $this->getBuildingMrrData($shortname, $month, $year);

//dd($retailMrr);
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
//                usleep(300);
            }

            $this->updateRetailRevenueDBTable($locid, $shortname, $buildingMrrTable);
            $this->advanceProgressBar();
        }
        $this->stopProgressBar();
        Log::debug('MRR report generated.');

    }

    /*
     * @param string $itemId - the item's ID from Zabbix
     * @return array
     */
//    public function getFirstItemTrendByItemID($itemId) {
//
////        $item = BillingTransactionLog::where()with(['trends' => function($query) {
////            $query->first();
////        }])->where('itemid', $itemId)->first();
//
////        dd([$itemId, $item]);
//
//        $trend = $item->getRelationValue('trends')->first()->toArray();
//        return $trend;
//    }
//
//    /*
//     * @param string $itemId - the item's ID from Zabbix
//     * @return array
//     */
//    public function getLastItemTrendByItemID($itemId) {
//
//        $item = Item::with(['trends' => function($query) {
//            $query->orderBy('clock', 'desc')->first();
//        }])->where('itemid', $itemId)->first();
//
//        $trend = $item->getRelationValue('trends')->first()->toArray();
//        return $trend;
//    }

    protected function isJobEnabled($jobNameKey)
    {

        $job = ScheduledJob::where('command', $this->jobNames[$jobNameKey])->first();
        if ($job == null)
        {
            Log::notice('Job: ' . $this->jobNames[$jobNameKey] . ' is missing from the scheduled_jobs table in the database');

            return false;
        }

        if ($job->enabled == 'yes')
        {
            return true;
        }

        return false;
    }

    protected function setJobStatus($jobNameKey, $status)
    {

        $job = ScheduledJob::where('command', $this->jobNames[$jobNameKey])->first();
        if ($job != null)
        {
            $job->status = $status;
            $job->last_run = ($status == 'stopped') ? date('Y-m-d H:i:s') : $job->last_run;
            $job->save();

            return true;
        }

        return false;
    }

    protected function updateJobTimestamp($jobNameKey)
    {

        $job = ScheduledJob::where('command', $this->jobNames[$jobNameKey])->first();
        if ($job && $job->enabled == 'yes')
        {
            $job->touch();

            return true;
        }

        return false;
    }

    protected function getJobProperty($jobNameKey, $propName)
    {

        $job = ScheduledJob::where('command', $this->jobNames[$jobNameKey])->first();

        return $job->getProperty($propName);
    }

    protected function startProgressBar($units = null, $component = '')
    {

        if ($this->console == false)
        {
            return;
        }

        if ($units == null || $units < 1)
        {
            $this->output->writeln('<error>Progress bar units not set</error>');

            return false;
        }

        $this->progress = new ProgressBar($this->output, $units);
        $this->progress->setFormatDefinition('custom', ' %component%:    %current%/%max% [%bar%] %percent:3s%%       %estimated:-6s%');
        $this->progress->setFormat('custom');
        $this->progress->setMessage($component, 'component');
        $this->progress->start();

        return true;
    }

    protected function advanceProgressBar($count = 1, $progress = null)
    {

        if ($count > 0)
        {
            $this->progress->advance($count);

            return;
        }

        if ($this->progress == null)
        {
            return;
        }
        $this->progress->setProgress($progress);
    }

    protected function progressBarError($errorMessage)
    {

        if ($this->progress == null)
        {
            return;
        }
        $this->progress->setFormatDefinition('custom', ' %table%:    %error_message%');
        $this->progress->setMessage($errorMessage, 'error_message');
    }

    protected function stopProgressBar()
    {
        if ($this->progress == null)
        {
            return;
        }
        // ensure that the progress bar is at 100%
        $this->progress->finish();
        $this->output->writeln('');
    }

    protected function writeLog($message)
    {
        if ($this->output != null)
        {
            $this->output->writeln($message);
        } else
        {
            Log::info($message);
        }
    }
}

?>
