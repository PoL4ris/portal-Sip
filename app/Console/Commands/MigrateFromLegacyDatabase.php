<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\DataMigrationUtils;

class MigrateFromLegacyDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:migrate-from-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Performs the initial migration of data from the legacy portal database';

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
        $dbMigrationUtil = new DataMigrationUtils(true);

//        $dbMigrationUtil->seedDataMigrationsTable();
//        $dbMigrationUtil->seedUsersTable();
//        $dbMigrationUtil->seedAppsTable();
//        $dbMigrationUtil->seedCategoriesTable();
//        $dbMigrationUtil->seedStatusTable();
//        $dbMigrationUtil->seedTypesTable();
//        $dbMigrationUtil->seedContactTypesTable();
//        $dbMigrationUtil->seedBuildingPropertiesTable();
//        $dbMigrationUtil->seedNeighborhoodTable();

        $dbMigrationUtil->migrateCustomersTable();
        $dbMigrationUtil->migrateServiceLocationsTable();
        $dbMigrationUtil->migrateServiceLocationPropertiesTable();
        $dbMigrationUtil->migrateAdditionalServiceLocationPropertyValues();
        $dbMigrationUtil->migrateProductsTable();
        $dbMigrationUtil->migrateProductPropertiesTable();
        $dbMigrationUtil->migrateProductPropertyValuesTable();
        $dbMigrationUtil->migrateCustomerProductsTable();
        $dbMigrationUtil->migrateBuildingProductsTable();
        $dbMigrationUtil->migrateNetworkNodesTable();
        $dbMigrationUtil->migrateDataServicePortsTable();
        $dbMigrationUtil->migrateSupportTicketReasonsTable();
        $dbMigrationUtil->migrateSupportTicketsTable();
        $dbMigrationUtil->migrateSupportTicketHistoryTable();
        $dbMigrationUtil->migrateBillingTransactionLogsTable();
        $dbMigrationUtil->migrateNetworkTabTable();

        $this->info('Done');
    }
}
