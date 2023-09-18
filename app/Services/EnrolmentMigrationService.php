<?php
namespace App\Services;
//ini_set('memory_limit', '128M');
use App\Models\Enrollment;
use App\Models\EnrolReportLog;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Log;
use App\Services\EmailDispatcher;
use Illuminate\Database\Migrations\Migration;

class EnrolmentMigrationService extends MigrationService{
  public static $username, $password;
  public static $key = '!QAZXSW@#EDCVFR$';
  public static $iv = '123456789101112';
  public static $placeholders = array('$first_name', '$last_name', '$membership_id',  '$password', '$program', '$link');

    public function __construct()
    {

    }

    public static function migrateEnrolments1() : string
    {
        //$this->key = '!QAZXSW@#EDCVFR$';

        self::$username = 'firstbank@1234';
        self::$password = parent::string_encrypt('ssw0rd20', self::$key,self::$iv);
        $data = [];
        $failure_count = 0;
        $success_count = 0;
        $company_details = new CompanyService(env('COMPANY_ID', 3));
        $company_details = $company_details->getCompanyDetails()->get();
        $pendingEnrolments = Enrollment::where('enrollment_status',0)->where('tries', '<=', 4)->select('first_name' ,'last_name', 'email','enrollment_status', 'tries', 'member_cif', 'branch_code', 'account_number', 'loyalty_number', 'pin', 'password')->limit(1000);//->get();//->where('tries', '<', 5);//->get();
       if ($pendingEnrolments->count()>0){
        foreach($pendingEnrolments->get() as $pendingEnrolment){
            if(Enrollment::where('member_cif', $pendingEnrolment->member_cif)->where('enrollment_status',1)){
                //CHECK MEMBER_CIF EXISTS. IF YES, PUSH TO ACCOUNT_NUMBER TABLE ON PERX
                $accDataToPush = array(
                 'Company_username'=>self::$username,//$company_details->username? $company_details->username: 0,
                 'Company_password'=>self::$password,//$company_details->password?$company_details->password:0,
                 'Membership_ID'=>parent::string_encrypt($pendingEnrolment->loyalty_number, self::$key,self::$iv),
                 'Account_number'=>$pendingEnrolment->account_number,
                 'API_flag'=>'attachAcountNumber',

                );

                parent::pushToPERX(parent::$url, $accDataToPush, parent::$headerPayload);

               }
               else{

        $pendingEnrolment->password ? $pendingEnrolment->password = $pendingEnrolment->password : $pendingEnrolment->password = '1234';
        $pendingEnrolment->pin ? $pendingEnrolment->pin = $pendingEnrolment->pin : $pendingEnrolment->pin = '0000';
        $pendingEnrolment->email ? $pendingEnrolment->email = $pendingEnrolment->email : $pendingEnrolment->email = $pendingEnrolment->loyalty_number . '@noemail.com';
        $pendingEnrolment->branch_code ? $pendingEnrolment->branch_code = $pendingEnrolment->branch_code : $pendingEnrolment->branch_code = '000';
                $arrayToPush = array(
                    'Company_username'=>self::$username,//$company_details->username? $company_details->username: 0,
                    'Company_password'=>self::$password,//$company_details->password?$company_details->password:0,
                    'Membership_ID'=>parent::string_encrypt($pendingEnrolment->loyalty_number, self::$key,self::$iv),
                    'Branch_code'=>$pendingEnrolment->branch_code,
                    'auto_gen_password'=>$pendingEnrolment->password?$pendingEnrolment->password:'1234',
                    'auto_gen_pin'=>$pendingEnrolment->pin?$pendingEnrolment->pin:'0000',
                    'API_flag'=>'enrol',

         );
          $resp = parent::pushToPERX(parent::$url, $arrayToPush, parent::$headerPayload);
        if (parent::isJSON($resp)) {
          $repsonse = json_decode($resp, true);
          //echo $resp;
          if ($repsonse) {
            EnrolReportLog::create([
              'firstname' => $pendingEnrolment->first_name?$pendingEnrolment->first_name:'',
              'lastname' => $pendingEnrolment->last_name?$pendingEnrolment->last_name:'',
              'email' => $pendingEnrolment->email ? $pendingEnrolment->email : $pendingEnrolment->loyalty_number . '@noemail.com',
              'customerid' => $pendingEnrolment->loyalty_number,
              'branchcode' => $pendingEnrolment->branch_code,
              'fileid' => 0,
              'status_code' => $repsonse['status'],
              'status_message' => $repsonse['Status_message']
            ]);
            if ($repsonse['status'] == 1001) {
              $success_count++;
              //implement send mail
              $values = array($pendingEnrolment->first_name, $pendingEnrolment->last_name, $pendingEnrolment->loyalty_number, $pendingEnrolment->password, parent::$program, parent::$link);
              EmailDispatcher::pendMails($pendingEnrolment->loyalty_number, "FLEX BIG ON THE FIDELITY GREEN REWARDS PROGRAMME", EmailDispatcher::buildEnrolmentTemplate(self::$placeholders, $values), 'no-reply@fdelitybank-ng.com');
              //SendNotificationService::sendMail($repsonse['Email_subject'], $repsonse['Email_body'], $repsonse['bcc_email_address']);
              Enrollment::where('member_cif', $pendingEnrolment->member_cif)->update(['enrollment_status' => 1]);
              $data['message'] = 'data migrated ' . $success_count;
            } else {
              Enrollment::where('member_cif', $pendingEnrolment->member_cif)->update(['tries' => $pendingEnrolment->tries + 1]);
              //Log::info('failed to migrate '. $failure_count);
              $data['message'] = 'data failed ' . $failure_count;
            }
          } else {
            $data['message'] = "no response from server";
          }
        }else{
          $data['format'] = "not json serialized";
        }
    }
    }

  }else{
          $data['message'] = "no un-enroled customers found";

       }
    return json_encode($data);
}






}
?>
