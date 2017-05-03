<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\DataMigrationUtils;

class GeneralTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:general-task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the general task function of the DataMigrationUtils class';

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
        $this->info('Starting general task');
        $dbMigrationUtil = new DataMigrationUtils(true);
        $dbMigrationUtil->generalDatabaseTask();
        $this->info('Done');

    }
}