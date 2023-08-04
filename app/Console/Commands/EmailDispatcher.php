<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailDispatcher as ED;

class EmailDispatcher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:mails';

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
        return ED::sendPendingEnrolmentEmails();
    }
}