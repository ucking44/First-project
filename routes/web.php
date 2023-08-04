<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailReportController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\MigrationController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TestCurl;
use App\Http\Controllers\EmailChannelController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportManagementController;
use App\Models\Transaction;
use App\Models\Enrollment;
use App\Models\PendingEmails;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Middleware\HSTS;
use App\Http\Middleware\HttpRedirect;
use App\Services\TransactionMigrationService;
use Illuminate\Http\Request;

//use Artisan;
//AuthController

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
//     Artisan::call('migrate',
//  array(
//    '--path' => 'database/migrations',
//    ));
 return 1;
});


//Route::middleware([HSTS::class, HttpRedirect::class])->group(function () {
Route::post('send-mail-v2', [EmailChannelController::class, 'channelMail']);
Route::resource('runcron2', MigrationController::class); 
Route::get('runcronid/{cron_id}', function ($cron_id) {
    return TransactionMigrationService::migrateTransactionCron($cron_id);
});
Route::get('whoami', [EnrollmentController::class, 'whoAmI']);
Route::get('mid', [EnrollmentController::class, 'whoAmI2']);
Route::resource('run-stats', StatsController::class);
Route::get('allow_me', function(){
    return view('stats.allow-me');
});

Route::post('/allow_me', function(Request $request){
    if($request->access == "LSLonlyPass"){
        $request->session()->put('is_allowed', true);
        return json_encode(array('url'=> url('run-stats')));
    }else{
        return redirect('/allow_me');
    }
});

//});
// Route::middleware([EnsureTokenIsValid::class])->group(function () {
//     Route::get('whoamii', [EnrollmentController::class, 'whoAmI']);
//     Route::resource('runcronn', MigrationController::class);
//     Route::get('email-log', [EmailReportController::class, 'index']);
//     Route::resource('run-stats', StatsController::class);
//     Route::get('report-gen', [ReportManagementController::class, 'index']);
// });