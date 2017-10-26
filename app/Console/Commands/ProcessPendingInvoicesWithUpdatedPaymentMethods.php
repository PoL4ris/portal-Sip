<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\BillingHelper;

class ProcessPendingInvoicesWithUpdatedPaymentMethods extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:process-pending-invoices-with-updated-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rerun the pending invoices that updated their payment methods.';

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
        $this->info('Rerunning pending auto-pay invoices that have updated payment methods');
        $billingHelper = new BillingHelper();
//        $billingHelper->processFailedAutopayInvoicesThatHaveUpdatedPaymentMethods();
        $billingHelper->rerunFailedAutopayInvoices(true);
        $this->info('Done');
    }
}
