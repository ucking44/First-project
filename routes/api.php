<?php

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\UGPController;
// use App\Http\Controllers\AuthController;
// use App\Http\Controllers\CompanyController;
// use App\Http\Controllers\EnrollmentController;
// use App\Http\Controllers\UserGroupsController;
// use App\Http\Controllers\ForgotPasswordController;
// use App\Http\Controllers\SendNotificationController;
// use App\Http\Controllers\ChannnelProviderController;
// use App\Http\Controllers\EmailGroupController;
// use App\Http\Controllers\EmailAddressController;
// use App\Http\Controllers\NotificationTypeController;
// use App\Http\Controllers\TemplateController;
// use App\Http\Controllers\VariableController;
// use App\Http\Controllers\ConfigureVariableController;
// use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\LoyaltyProgramController;
// use App\Http\Controllers\BranchController;
// use App\Http\Controllers\StatsController;
// use App\Http\Controllers\TransactionController;
// use App\Http\Controllers\UsersController;
// use App\Http\Controllers\EnrolmentReportLog;
// use App\Http\Controllers\ErrorReportLog;
// use App\Http\Controllers\TransactionReportLog;
// use App\Http\Controllers\EmailChannelController;
// use App\Http\Controllers\TestCurl;

// //use App\Models\Enrollment;
// //use Illuminate\Support\Enumerable;

// /*
// |--------------------------------------------------------------------------
// | API Routes
// |--------------------------------------------------------------------------
// |
// | Here is where you can register API routes for your application. These
// | routes are loaded by the RouteServiceProvider within a group which
// | is assigned the "api" middleware group. Enjoy building your API!
// |
// */

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
    
// });
// Route::get('test_api', [TestCurl::class, 'testAPI']);

// ####### Routes without Middleware/Privileges #########
// Route::post('send_notification',[SendNotificationController::class,'send']);

// Route::post('login',[AuthController::class, 'login'])->name('users.login')->middleware('cors');

// //"laravel/passport": "^10.1",

// Route::post('send-customer-mail', [EmailChannelController::class, 'channelMail']);
// Route::post('forgot_password',[ForgotPasswordController::class, 'forgot_password']);
// Route::post('reset_password', [ForgotPasswordController::class, 'password_reset']);
// Route::post('verify_link', [ForgotPasswordController::class, 'verifyResetLink']);
// Route::get('get_branches', [BranchController::class, 'view_branches']);
// Route::get('view-customers', [EnrollmentController::class, 'getAllEnrollments']);
// Route::get('view-users', [UsersController::class, 'index']);
// Route::get('view-stats', [StatsController::class, 'index']);
// Route::get('view-enrolment-log', [EnrolmentReportLog::class, 'index']);
// Route::get('view-transaction-log', [TransactionReportLog::class, 'index']);
// Route::get('view-error-log', [ErrorReportLog::class, 'index']);
// //Route::post('upload_customers', [EnrollmentController::class, 'uploadEnrollments'])->name('upload.customers');
// Route::get('view-trans', [TransactionController::class, 'view_transactions']);
// //Route::post('add_customer', [EnrollmentController::class,'Enrollment'])->name('add.customer');
//     Route::group( ['middleware' => ['auth:admin-api','scopes:admin','check.program', 'cors'],'prefix' => '{pro_slug}' ],function(){
       
//         Route::get('get_all_enrollments',[EnrollmentController::class,'getAllEnrollments']);
//         Route::post('get_enrollment', [EnrollmentController::class, 'get_enrollment_by_number']);
//         Route::get('get_enrollment_statement/{loyalty_number}',[EnrollmentController::class, 'getStatement']);
//         Route::post('search_enrollment', [EnrollmentController::class, 'searchEnrollment']);
//         Route::get('edit_member/{id}', [EnrollmentController::class, 'editMember']);
//         Route::post('update_member_info',[EnrollmentController::class, 'UpdateMemberInfo']);
//         Route::post('update_member_contact',[EnrollmentController::class, 'UpdateMemberContact']);
//         Route::post('update_member_tier',[EnrollmentController::class, 'UpdateMemberTier']);
//         Route::post('search_statement', [EnrollmentController::class, 'searchStatement']);
//         Route::get('dashboard',[DashboardController::class,'index']);
//         Route::post('add_customer', [EnrollmentController::class,'Enrollment'])->name('add.customer');
//         Route::post('upload_customers', [EnrollmentController::class, 'uploadEnrollments'])->name('upload.customers');
//         Route::post('upload_transactions', [TransactionController::class, 'UploadTransactions']);
//         Route::get('get_profile', [AuthController::class, 'profile']);
//         Route::post('update_profile', [AuthController::class, 'UpdateProfile']);

//         Route::prefix('admin')->middleware('Admin_go')->group(function () {
    
//                 Route::get('/privileges', [UGPController::class, 'get_priviledges']);
//                 Route::post('/privilege/access', [UGPController::class, 'get_priviledge_routes']);
                
//                 Route::prefix('company')->group(function () {
//                     Route::get('view_companies/{status?}', [CompanyController::class, 'viewCompanies'])->name('view.companies');
//                     Route::post('add_company', [CompanyController::class, 'addCompany'])->name('add.company');
//                     Route::get('view_company/{id}', [CompanyController::class, 'viewCompany'])->name('view.company');
//                     Route::get('update_company/{id}/{status}', [CompanyController::class, 'updateStatus'])->name('edit.company');
//                 });
                
//                 //Route::get('view-trans', [TransactionController::class, 'view_transactions'])->middleware('cors');

//                 Route::prefix('loyalty_program')->group(function (){
//                     Route::post('add_loyalty_program',[LoyaltyProgramController::class, 'AddProgram'])->name('add.program');
//                     Route::post('get_loyalty_programs',[LoyaltyProgramController::class, 'GetPrograms'])->name('view.programs');
//                     Route::get('fetch_loyalty_programs',[LoyaltyProgramController::class, 'FetchPrograms'])->name('fetch.programs');
//                     Route::get('edit/{id}',[LoyaltyProgramController::class, 'EditProgram'])->name('edit.program');
//                     Route::post('update',[LoyaltyProgramController::class, 'UpdateProgram'])->name('edit.program');
//                 });

//                 Route::prefix('users')->group(function () {
//                     Route::post('/create', [AuthController::class, 'createUser'])->name('create.user');
//                     Route::get('/view', [AuthController::class, 'viewUsers'])->name('view.users');
//                 });

//                 Route::prefix('usergroups')->group(function () {
//                     Route::post('/create', [UserGroupsController::class, 'createUserGroup'])->name('create.usersgroups');
//                     Route::post('/edit', [UserGroupsController::class, 'editUserGroup'])->name('edit.usersgroups');
//                     Route::post('/status', [UserGroupsController::class, 'statusUserGroup'])->name('status.usersgroups');
//                     Route::get('/view', [UserGroupsController::class, 'fetchUserGroups'])->name('view.usersgroups');
//                 });

//             Route::prefix('user/privilege')->group(function () {
//                 //Route::get('/view', [UGPController::class, 'viewUsersPriviledges'])->name('view.userprivilege');
//                 //Route::post('/create', [UGPController::class, 'createUsersPriviledges'])->name('create.userprivilege');
//             });
//         });

//             //notification_routes
//             Route::prefix("notifications")->group(function () {
//                 Route::group(['prefix'=> 'variable'], function () {
//                     Route::get('/', [VariableController::class,'all']);
//                     Route::post('/create', [VariableController::class,'create']);
//                     Route::post('/delete/{variable_id}', [VariableController::class,'delete']);
//                     Route::post('{variable_id}/link-notifications', [VariableController::class,'link_to_not_type']);
//                     Route::post('{variable_id}/unlink-notifications', [VariableController::class,'unlink_from_not_type']);
//                 });
//                 Route::group(['prefix'=> 'type'], function () {
//                     Route::get('/', [NotificationTypeController::class,'all']);
//                     Route::post('/create', [NotificationTypeController::class,'create']);
//                     Route::post('/update/{not_type_slug}', [NotificationTypeController::class,'update']);
//                     Route::post('/disable/{not_type_slug}', [NotificationTypeController::class,'disable']);
//                     Route::post('/enable/{not_type_slug}', [NotificationTypeController::class,'enable']);
//                     Route::get('/{not_type_slug}/variables', [NotificationTypeController::class,'variables']);
//                     //Route::get('/{not_type_slug}/channels', 'NotificationChannel@notificationchannels');
//                 });
//                 Route::group(['prefix'=> 'channel'], function () {
//                     Route::get('/view', [ChannnelProviderController::class, 'all'])->name("view.channels");
//                     Route::post('/create', [ChannnelProviderController::class, 'create'])->name("create.channel");
//                     Route::post('/store', [ChannnelProviderController::class, 'store'])->name("store.channel");
//                     Route::post('/update/{slug}', [ChannnelProviderController::class, 'update'])->name("update.channel");
//                     Route::post('disable/{slug}', [ChannnelProviderController::class, 'disable'])->name("disable.channel");
//                     Route::post('enable/{slug}', [ChannnelProviderController::class, 'enable'])->name("enable.channel");
//                 });
//                 Route::group(['prefix' => '{program_slug}/email/groups', "middleware" => 'check.program'], function () { 
//                     Route::post('create', [EmailGroupController::class,'create_mail_group']);

//                     Route::get('/', [EmailGroupController::class, 'get_program_groups']);

//                     Route::post('/disable/{group_id}', [EmailGroupController::class,'disable']);

//                     Route::post('/enable/{group_id}', [EmailGroupController::class,'enable']);

//                     Route::post('add-emails/', [EmailGroupController::class,'add_mails_to_group']);

//                     Route::post('remove-emails', [EmailGroupController::class,'remove_mails_from_group']);

//                     Route::get('{group_id}', [EmailGroupController::class,'group_emails']);

//                     Route::post('delete', [EmailGroupController::class, 'delete_mail_groups']);

//                     Route::post('add-groups', [EmailGroupController::class ,'add_groups_to_program_not_type']);

//                     Route::post('remove-groups', [EmailGroupController::class ,'remove_groups_from_program_not_type']);
//                 });

//                 Route::group(['prefix'=> '{program_slug}/template', "middleware" => 'check.program'], function () {
//                     Route::get('/', [TemplateController::class,'program_templates']);
//                     Route::get('/notification-type/{not_type_slug}', [TemplateController::class,'notification_templates']);
//                     Route::post('/create', [TemplateController::class,'create']);
//                     Route::post('/update/{template_id}', [TemplateController::class,'update']);
//                     Route::post('/disable/{template_id}', [TemplateController::class,'disable']);
//                     Route::post('/enable/{template_id}', [TemplateController::class,'enable']);
//                 });
//                 Route::group(['prefix'=> 'email'], function () {
//                     Route::get('/', [EmailAddressController::class,'program_emails']);
//                     Route::post('/create', [EmailAddressController::class,'create']);
//                     Route::post('/update/{id}', [EmailAddressController::class,'update']);
//                     Route::post('disable/{id}', [EmailAddressController::class,'disable']);
//                     Route::post('enable/{id}', [EmailAddressController::class,'enable']);
//                     Route::post('delete/{id}', [EmailAddressController::class,'delete']);
//                 });

//                 Route::group(['prefix'=> 'configuration-variable'], function () {
//                     Route::post('create', [ConfigureVariableController::class,'create']);
//                     Route::post('delete', [ConfigureVariableController::class,'delete']);
//                     Route::post('update/{id}', [ConfigureVariableController::class,'update']);
//                 });
        
//                 Route::get('/show-channels', [ChannnelProviderController::class, 'all']);
//             });

// });