<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\SIPReporting;

class GenerateMrrReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:generate-mrr-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate MRR report for residential buildings';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting MRR report generation');
        $reportingHelper = new SIPReporting(true);
        $reportingHelper->runMrrReportJob();
        $this->info('Done');
    }
}
