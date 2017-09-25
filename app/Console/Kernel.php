<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Log;
use App\Models\ScheduledJob;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\TruncateDatabaseTables::class,
        Commands\MigrateFromLegacyDatabase::class,
        Commands\UpdateFromLegacyDatabase::class,
        Commands\GeneralTasks::class,
        Commands\GenerateCustomerCharges::class,
        Commands\InvoicePendingCharges::class,
        Commands\ProcessPendingAutoPayInvoices::class,
        Commands\ProcessPendingInvoicesWithUpdatedPaymentMethods::class,
        Commands\RerunAllPendingInvoices::class,
        Commands\GenerateMrrReport::class,
        Commands\ParseSupportEmail::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        ######## Sample schedule commands ########
        //
        //            ->name('dhcp:process-pending-leases')
        //            ->everyMinute()
        //            ->weekdays()
        //            ->hourly()
        //            ->timezone('America/Chicago')
        //            ->when(function () {
        //                return date('H') >= 8 && date('H') <= 17;
        //            })


        $events = array();

        $events[] = $schedule->command('data:update-from-legacy')
            ->name('data:update-from-legacy')
            ->everyMinute()
            ->withoutOverlapping()
            ->sendOutputTo(config('joblogs.update-data-from-legacy-db-job-log'));

        $events[] = $schedule->command('report:generate-mrr-report')
            ->name('report:generate-mrr-report')
            ->daily()
            ->withoutOverlapping()
            ->sendOutputTo(config('joblogs.generate-mrr-report-job-log'));

        /**
         *  Leave the code below alone. It updates job timestamps and status
         *  in the database. Add your scheduled jobs above this section
         */
        $schedule->call(function () use ($events, $schedule)
        {
            foreach ($events as $event)
            {
                $scheduledJob = ScheduledJob::where('command', $event->description)->first();
                if ($scheduledJob == null)
                {
                    continue;
                }
                $scheduledJob->schedule = $event->getExpression();
                $scheduledJob->save();
                Log::info('Updated schedule for: ' . $event->description);
            }
        })->everyMinute()
            ->name('portal jobs schedule checker')
            ->withoutOverlapping();
    }
}
