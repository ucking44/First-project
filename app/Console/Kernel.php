<?php

namespace App\Console;

use App\Services\EnrolmentMigrationService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        Commands\CustomerMigrationCron::class,
        Commands\CustomerMigrationCron2::class,
        Commands\CustomerMigrationCron3::class,
        Commands\CustomerMigrationCron4::class,
        Commands\CustomerMigrationCron5::class,
        
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        //$schedule->command('push:data')->everyMinute()->runInBackground();
        //$schedule->command('read:data')->everyMinute()->runInBackground();
        $schedule->command('cmc:cron')->everyThreeMinutes()->runInBackground();
        // $schedule->command('cmc2:cron')->everyTwoMinutes()->runInBackground();
        // $schedule->command('cmc3:cron')->everyThreeMinutes()->runInBackground();
        // $schedule->command('cmc4:cron')->everyFourMinutes()->runInBackground();
        // $schedule->command('cmc5:cron')->everyMinute()->runInBackground();
        $schedule->command('trans:cron')->everyMinute()->runInBackground();
        // $schedule->command('trans:cron2')->everyMinute()->runInBackground();
        // $schedule->command('trans:cron3')->everyMinute()->runInBackground();
        // $schedule->command('trans:cron4')->everyMinute()->runInBackground();
        // $schedule->command('trans:cron5')->everyMinute()->runInBackground();
         $schedule->command('push:mails')->everyFiveMinutes()->runInBackground();
         $schedule->command('token:cron')->weekly();
                 //phpLog::info('This is some useful information.');
                 //

    }

    /**
     * PATH='/Library/Frameworks/Python.framework/Versions/3.7.2/bin/python3' export PATH
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}