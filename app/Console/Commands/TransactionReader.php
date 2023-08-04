<?php

namespace App\Console\Commands;

use App\Http\Controllers\FileUploadController;
use Illuminate\Console\Command;

class TransactionReader extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'read:cron';

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
        $transReader = new FileUploadController();
        return $transReader->saveFile();
    }
}