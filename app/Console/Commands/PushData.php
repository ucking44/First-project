<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PushDataService as PDS;

class PushData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command migrates pending customer enrolments and transactions that are pending';

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
        return PDS::trigger();
    }
}