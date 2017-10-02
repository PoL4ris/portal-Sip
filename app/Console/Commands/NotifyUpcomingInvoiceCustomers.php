<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\BillingHelper;

class NotifyUpcomingInvoiceCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:notify-upcoming-invoice-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Email notifications to all customers acbout their upcoming charges.';

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
        $messagesSent = $billingHelper->notifyUpcomingInvoiceCustomers();
        $this->info('Sent '.$messagesSent.' notifications.');
    }
}
