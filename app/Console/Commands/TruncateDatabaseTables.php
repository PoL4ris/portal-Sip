<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\DataMigrationUtils;

class TruncateDatabaseTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:truncate-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncates all tables in the new portal database';

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
        $this->info('Starting truncate procedure');
        $dbMigrationUtil = new DataMigrationUtils(true);
        $dbMigrationUtil->truncateAllTables();
        $this->info('Done');
    }
}
