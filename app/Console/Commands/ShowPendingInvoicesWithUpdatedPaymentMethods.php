<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\BillingHelper;

class ShowPendingInvoicesWithUpdatedPaymentMethods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:show-pending-invoices-with-updated-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display list of pending invoices that updated their payment methods.';

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
        $this->info('Getting pending auto-pay invoices that have updated payment methods');
        $billingHelper = new BillingHelper();
//        $pendingInvoices = collect($billingHelper->getFailedAutopayInvoicesThatHaveUpdatedPaymentMethods());
        $pendingInvoices = collect($billingHelper->getFailedAutopayInvoices(true));
        dd($pendingInvoices->pluck('amount', 'id'));
        $this->info('Done');
    }
}
