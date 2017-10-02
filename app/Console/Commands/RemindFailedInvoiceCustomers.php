<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\BillingHelper;

class RemindFailedInvoiceCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:remind-failed-invoice-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Email notifications to all customers that have failed pending invoices.';

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
        $messagesSent = $billingHelper->remindFailedInvoiceCustomers();
        $this->info('Sent '.$messagesSent.' notifications.');
    }
}
