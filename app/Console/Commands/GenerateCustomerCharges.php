<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\BillingHelper;

class GenerateCustomerCharges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate-customer-charges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate charges for customer products';

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
        $this->info('Starting data migration');
        $billingHelper = new BillingHelper();
        $billingHelper->generateResidentialChargeRecords();
        $this->info('Done');
    }
}
