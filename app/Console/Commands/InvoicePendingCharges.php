<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\BillingHelper;

class InvoicePendingCharges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:invoice-pending-charges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invoice Pending Charges';

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
        $this->info('Starting invoice generation');
        $billingHelper = new BillingHelper();
        $billingHelper->invoicePendingCharges();
        $this->info('Done');
    }
}
