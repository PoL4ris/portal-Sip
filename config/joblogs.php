<?php

return [
    'update-data-from-legacy-db-job-log' => env('UPDATE_DATA_FROM_LEGACY_DATABASE_LOG', storage_path('logs/update_data_from_legacy_db.log')),
    'process-pending-invoices-log' => storage_path('logs/process_pending_invoices.log')
];
