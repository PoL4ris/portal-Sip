<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\BillingHelper;

class GenerateCustomerCharges extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate-charges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all customer charges and store them in the charges table';

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
        $this->info('Starting charge generation');
        $billingHelper = new BillingHelper();
        $billingHelper->generateResidentialChargeRecords();
        $this->info('Done');
    }
}
