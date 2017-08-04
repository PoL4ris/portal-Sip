<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\BillingHelper;

class ProcessPendingAutoPayInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:process-pending-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process 200 of the the monthly pending auto-pay invoices.';

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
        $this->info('Starting invoice processing');
        $billingHelper = new BillingHelper();
        $billingHelper->processPendingAutopayInvoices();
        $this->info('Done');
    }
}
