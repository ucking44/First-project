<?php

namespace App\Console\Commands;

use App\Services\TransactionMigrationService;
use Illuminate\Console\Command;

class TransactionCron5 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trans:cron5';

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
        return TransactionMigrationService::migrateTransaction5(); // ::migrateEnrolments5();
    }
}