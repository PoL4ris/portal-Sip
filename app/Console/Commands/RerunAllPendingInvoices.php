<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\BillingHelper;

class RerunAllPendingInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:rerun-all-pending-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reruns all pending invoices and notifies customers.';

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
        $this->info('Rerunning all pending invoices');
        $billingHelper = new BillingHelper();
        $billingHelper->rerunFailedAutopayInvoices();
        $this->info('Done');
    }
}
