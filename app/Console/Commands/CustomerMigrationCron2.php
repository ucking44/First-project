<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EnrolmentMigrationService;

class CustomerMigrationCron2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmc2:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        return EnrolmentMigrationService::migrateEnrolments2();
    }
}