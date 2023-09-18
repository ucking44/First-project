<?php
namespace App\Services;
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
use App\Models\Transaction;
use App\Models\Enrollment;
use App\Models\TransactionReportLog ;
use App\Services\EmailDispatcher;
use App\Services\UserService;
use App\Services\CurlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionMigrationService extends MigrationService{
    public static $key = '!QAZXSW@#EDCVFR$';
    public static $iv = '123456789101112';
    public static $username = 'firstbank@1234';
    public static $link = "https://fbn-customer-portal.vercel.app";
    public static $placeholders = array('$first_name', '$last_name', '$points_earned','$current_balance', '$program', '$Membership_ID', '$link', '$product_name');
    //public static $password =  self::$password = parent::string_encrypt('Di@mond10$#', self::$key,self::$iv);
    public function __construct()
    {

    }

    public static function migrateTransaction1():void{
      $success_count = 0;  $failure_count = 0;
      $data = [];
      $arrayToPush = array('Company_username'=>self::$username,
      'Company_password'=>parent::passwordReturn(), 'API_flag'=>'stran');
      $pendingTransactions = Transaction::where('status', 0)->where('cron_id', 1)->select('member_cif', 'status', 'product_code', 'branch_code', 'account_number', 'transaction_date', 'dumped_date', 'transaction_reference', 'channel', 'product_code', 'transaction_type')->limit(1000);
      if($pendingTransactions->count() > 0){
        foreach($pendingTransactions->get() as $pendingTransaction){
          //$pendingTransaction->quantity  = 1;
          $membership_id_resolved = parent::string_encrypt(parent::resolveMemberReference($pendingTransaction->member_cif), self::$key,self::$iv);
            $arrayToPush = array(
              'Company_username'=>self::$username,
              'Company_password'=>parent::passwordReturn(),
              'Membership_ID'=>$membership_id_resolved,
              'Transaction_Date'=>$pendingTransaction->transaction_date,
              'Transaction_Type_code'=>$pendingTransaction->transaction_type,
              'Transaction_channel_code'=>$pendingTransaction->channel,
              'Transaction_amount'=>$pendingTransaction->amount,
              'Branch_code'=>$pendingTransaction->branch_code,
              'Transaction_ID'=>$pendingTransaction->transaction_reference,
              'Dumped_Date'=>$pendingTransaction->dumped_date,
              'Product_Code' =>$pendingTransaction->product_code,
              'Product_Quantity' =>$pendingTransaction->quantity,
              'API_flag' => 'stran'
              );

              //print_r($pendingTransaction->member_cif);
              $member =  Enrollment::where('member_cif', trim($pendingTransaction->member_cif))->first();
              $checkAccNos = Enrollment::where('account_number',$pendingTransaction->account_number)
                             ->where('member_cif', $pendingTransaction->member_cif)->select('member_cif','account_number')->first();
              if($checkAccNos == null){
        //Hit endpoint to insert into new account number table to be created. Sending this acc_nos as param "$pendingTransaction->account_number"
            //    $newCustAccount = array('membership_id'=>$membership_id_resolved,'account_number'=>$pendingTransaction->account_number,'API_flag' => 'new_account');
            //    $resp = parent::pushToPERXAcc(parent::$url, $newCustAccount, parent::$headerPayload);

             //CHECK MEMBER_CIF EXISTS. IF YES, PUSH TO ACCOUNT_NUMBER TABLE ON PERX
             $accDataToPush = array(
                'Company_username'=>self::$username,//$company_details->username? $company_details->username: 0,
                'Company_password'=>parent::passwordReturn(),//$company_details->password?$company_details->password:0,
                'Membership_ID'=>$membership_id_resolved,
                'Account_number'=>$checkAccNos->account_number,
                'API_flag'=>'attachAcountNumber',

               );

               parent::pushToPERX(parent::$url, $accDataToPush, parent::$headerPayload);
              }
              //print_r($member);
              $product_name = json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/get_product_group_name?product_code=".$pendingTransaction->product_code), true);
              if (isset($member->first_name)){
              $product_name = $product_name['product_brand_name'];
              $values = array($member->first_name, $member->last_name, 0, 0, parent::$program, $member->loyalty_number, self::$link, $product_name);
              $resp = parent::pushToPERX(parent::$url, $arrayToPush, parent::$headerPayload);
              //print_r($resp);
              $repsonse = json_decode($resp, true);
          if ($repsonse){
          if ($repsonse['status'] == 1001){
              //print_r($repsonse);
              $values[2] = $repsonse['Loyalty_points']?number_format($repsonse['Loyalty_points']):0;
              $customer_balance = CurlService::makeGet("https://perxapi.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$member->loyalty_number);//, true); //["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;
              $customer_balance = json_decode($customer_balance, true);
              $values[3] = number_format($customer_balance['profile'][0]['Current_balance']);//json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;


              //SendNotificationService::sendMail($repsonse['Email_subject'], $repsonse['Email_body'], $repsonse['bcc_email_address']);
              Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 1]);
              if(intval($values[2])>0){
                  EmailDispatcher::pendMails(parent::resolveMemberReference(trim($pendingTransaction->member_cif)), "YOU JUST EARNED LOYALTY POINTS ON THE FIRST BANK LOYALTY PROGRAMME", EmailDispatcher::buildTransactionTemplate(self::$placeholders, $values), 'no-reply@greenrewards.com');
              }
              TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
              'status_code'=>$repsonse['status'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date, 'status_message'=>$repsonse['Status_message']]);

          }else{
              //Transaction::where('member_cif', $pendingTransaction->member_cif)->update(['tries'=>$pendingTransaction->tries+ 1 ]);
              Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 3]);
              TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
              'status_code'=>$repsonse['status'], 'status_message'=>$repsonse['Status_message'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date]);


          }
      }else{


      }
    }
    else{
      //return "failed to find member by membership_id: " . $pendingTransaction->member_cif . "<br>";
  }
  }
       // }
  } else{
      $data['message'] = "no transactions on queue for migration";
      //print_r($data);
  }
  }

  public static function migrateTransaction2():void{
    $success_count = 0;  $failure_count = 0;
    $data = [];
    $arrayToPush = array('Company_username'=>self::$username,
    'Company_password'=>parent::passwordReturn(), 'API_flag'=>'stran');
    $pendingTransactions = Transaction::where('status', 0)->select('member_cif', 'status', 'product_code', 'branch_code', 'transaction_reference', 'channel', 'product_code', 'transaction_type')->limit(1000);
    if($pendingTransactions->count() > 0){
      foreach($pendingTransactions->get() as $pendingTransaction){
        //$pendingTransaction->quantity  = 1;
        $membership_id_resolved = parent::string_encrypt(parent::resolveMemberReference($pendingTransaction->member_cif), self::$key,self::$iv);
          $arrayToPush = array(
            'Company_username'=>self::$username,
            'Company_password'=>parent::passwordReturn(),
            'Membership_ID'=>$membership_id_resolved,
            'Transaction_Date'=>$pendingTransaction->transaction_date,
            'Transaction_Type_code'=>$pendingTransaction->transaction_type,
            'Transaction_channel_code'=>$pendingTransaction->channel,
            'Transaction_amount'=>$pendingTransaction->amount,
            'Branch_code'=>$pendingTransaction->branch_code,
            'Transaction_ID'=>$pendingTransaction->transaction_reference,
            'Product_Code' =>$pendingTransaction->product_code,
            'Product_Quantity' =>$pendingTransaction->quantity,
            'API_flag' => 'stran'
            );

            //print_r($pendingTransaction->member_cif);
            $member =  Enrollment::where('member_cif', trim($pendingTransaction->member_cif))->first();
            //print_r($member);
            $product_name = json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/get_product_group_name?product_code=".$pendingTransaction->product_code), true);
            if (isset($member->first_name)){
            $product_name = $product_name['product_brand_name'];
            $values = array($member->first_name, $member->last_name, 0, 0, parent::$program, $member->loyalty_number, self::$link, $product_name);
            $resp =
            parent::pushToPERX(parent::$url, $arrayToPush, parent::$headerPayload);
            //print_r($resp);
            $repsonse = json_decode($resp, true);
        if ($repsonse){
        if ($repsonse['status'] == 1001){
            //print_r($repsonse);
            $values[2] = $repsonse['Loyalty_points']?number_format($repsonse['Loyalty_points']):0;
            $customer_balance = CurlService::makeGet("https://perxapi.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$member->loyalty_number);//, true); //["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;
            $customer_balance = json_decode($customer_balance, true);
            $values[3] = number_format($customer_balance['profile'][0]['Current_balance']);//json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;


            //SendNotificationService::sendMail($repsonse['Email_subject'], $repsonse['Email_body'], $repsonse['bcc_email_address']);
            Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 1]);
            if(intval($values[2])>0){
                EmailDispatcher::pendMails(parent::resolveMemberReference(trim($pendingTransaction->member_cif)), "YOU JUST EARNED LOYALTY POINTS ON THE FIRST BANK LOYALTY PROGRAMME", EmailDispatcher::buildTransactionTemplate(self::$placeholders, $values), 'no-reply@greenrewards.com');
            }
            TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
            'status_code'=>$repsonse['status'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date, 'status_message'=>$repsonse['Status_message']]);

        }else{
            //Transaction::where('member_cif', $pendingTransaction->member_cif)->update(['tries'=>$pendingTransaction->tries+ 1 ]);
            Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 3]);
            TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
            'status_code'=>$repsonse['status'], 'status_message'=>$repsonse['Status_message'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date]);


        }
    }else{


    }
  }
  else{
    //return "failed to find member by membership_id: " . $pendingTransaction->member_cif . "<br>";
}
}
     // }
} else{
    $data['message'] = "no transactions on queue for migration";
    //print_r($data);
}
}


public static function migrateTransaction3():void{
  $success_count = 0;  $failure_count = 0;
  $data = [];
  $arrayToPush = array('Company_username'=>self::$username,
  'Company_password'=>parent::passwordReturn(), 'API_flag'=>'stran');
  $pendingTransactions = Transaction::where('status', 0)->select('member_cif', 'status', 'product_code', 'branch_code', 'transaction_reference', 'channel', 'product_code', 'transaction_type')->limit(1000);
  if($pendingTransactions->count() > 0){
    foreach($pendingTransactions->get() as $pendingTransaction){
      //$pendingTransaction->quantity  = 1;
      $membership_id_resolved = parent::string_encrypt(parent::resolveMemberReference($pendingTransaction->member_cif), self::$key,self::$iv);
        $arrayToPush = array(
          'Company_username'=>self::$username,
          'Company_password'=>parent::passwordReturn(),
          'Membership_ID'=>$membership_id_resolved,
          'Transaction_Date'=>$pendingTransaction->transaction_date,
          'Transaction_Type_code'=>$pendingTransaction->transaction_type,
          'Transaction_channel_code'=>$pendingTransaction->channel,
          'Transaction_amount'=>$pendingTransaction->amount,
          'Branch_code'=>$pendingTransaction->branch_code,
          'Transaction_ID'=>$pendingTransaction->transaction_reference,
          'Product_Code' =>$pendingTransaction->product_code,
          'Product_Quantity' =>$pendingTransaction->quantity,
          'API_flag' => 'stran'
          );

          //print_r($pendingTransaction->member_cif);
          $member =  Enrollment::where('member_cif', trim($pendingTransaction->member_cif))->first();
          //print_r($member);
          $product_name = json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/get_product_group_name?product_code=".$pendingTransaction->product_code), true);
          if (isset($member->first_name)){
          $product_name = $product_name['product_brand_name'];
          $values = array($member->first_name, $member->last_name, 0, 0, parent::$program, $member->loyalty_number, self::$link, $product_name);
          $resp =
          parent::pushToPERX(parent::$url, $arrayToPush, parent::$headerPayload);
          //print_r($resp);
          $repsonse = json_decode($resp, true);
      if ($repsonse){
      if ($repsonse['status'] == 1001){
          //print_r($repsonse);
          $values[2] = $repsonse['Loyalty_points']?number_format($repsonse['Loyalty_points']):0;
          $customer_balance = CurlService::makeGet("https://perxapi.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$member->loyalty_number);//, true); //["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;
          $customer_balance = json_decode($customer_balance, true);
          $values[3] = number_format($customer_balance['profile'][0]['Current_balance']);//json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;


          //SendNotificationService::sendMail($repsonse['Email_subject'], $repsonse['Email_body'], $repsonse['bcc_email_address']);
          Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 1]);
          if(intval($values[2])>0){
              EmailDispatcher::pendMails(parent::resolveMemberReference(trim($pendingTransaction->member_cif)), "YOU JUST EARNED LOYALTY POINTS ON THE FIRST BANK LOYALTY PROGRAMME", EmailDispatcher::buildTransactionTemplate(self::$placeholders, $values), 'no-reply@greenrewards.com');
          }
          TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
          'status_code'=>$repsonse['status'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date, 'status_message'=>$repsonse['Status_message']]);

      }else{
          //Transaction::where('member_cif', $pendingTransaction->member_cif)->update(['tries'=>$pendingTransaction->tries+ 1 ]);
          Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 3]);
          TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
          'status_code'=>$repsonse['status'], 'status_message'=>$repsonse['Status_message'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date]);


      }
  }else{


  }
}
else{
  //return "failed to find member by membership_id: " . $pendingTransaction->member_cif . "<br>";
}
}
   // }
} else{
  $data['message'] = "no transactions on queue for migration";
  //print_r($data);
}
}

public static function migrateTransaction4():void{
  $success_count = 0;  $failure_count = 0;
  $data = [];
  $arrayToPush = array('Company_username'=>self::$username,
  'Company_password'=>parent::passwordReturn(), 'API_flag'=>'stran');
  $pendingTransactions = Transaction::where('status', 0)->select('member_cif', 'status', 'product_code', 'branch_code', 'transaction_reference', 'channel', 'product_code', 'transaction_type')->limit(1000);
  if($pendingTransactions->count() > 0){
    foreach($pendingTransactions->get() as $pendingTransaction){
      //$pendingTransaction->quantity  = 1;
      $membership_id_resolved = parent::string_encrypt(parent::resolveMemberReference($pendingTransaction->member_cif), self::$key,self::$iv);
        $arrayToPush = array(
          'Company_username'=>self::$username,
          'Company_password'=>parent::passwordReturn(),
          'Membership_ID'=>$membership_id_resolved,
          'Transaction_Date'=>$pendingTransaction->transaction_date,
          'Transaction_Type_code'=>$pendingTransaction->transaction_type,
          'Transaction_channel_code'=>$pendingTransaction->channel,
          'Transaction_amount'=>$pendingTransaction->amount,
          'Branch_code'=>$pendingTransaction->branch_code,
          'Transaction_ID'=>$pendingTransaction->transaction_reference,
          'Product_Code' =>$pendingTransaction->product_code,
          'Product_Quantity' =>$pendingTransaction->quantity,
          'API_flag' => 'stran'
          );

          //print_r($pendingTransaction->member_cif);
          $member =  Enrollment::where('member_cif', trim($pendingTransaction->member_cif))->first();
          //print_r($member);
          $product_name = json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/get_product_group_name?product_code=".$pendingTransaction->product_code), true);
          if (isset($member->first_name)){
          $product_name = $product_name['product_brand_name'];
          $values = array($member->first_name, $member->last_name, 0, 0, parent::$program, $member->loyalty_number, self::$link, $product_name);
          $resp =
          parent::pushToPERX(parent::$url, $arrayToPush, parent::$headerPayload);
          //print_r($resp);
          $repsonse = json_decode($resp, true);
      if ($repsonse){
      if ($repsonse['status'] == 1001){
          //print_r($repsonse);
          $values[2] = $repsonse['Loyalty_points']?number_format($repsonse['Loyalty_points']):0;
          $customer_balance = CurlService::makeGet("https://perxapi.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$member->loyalty_number);//, true); //["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;
          $customer_balance = json_decode($customer_balance, true);
          $values[3] = number_format($customer_balance['profile'][0]['Current_balance']);//json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;


          //SendNotificationService::sendMail($repsonse['Email_subject'], $repsonse['Email_body'], $repsonse['bcc_email_address']);
          Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 1]);
          if(intval($values[2])>0){
              EmailDispatcher::pendMails(parent::resolveMemberReference(trim($pendingTransaction->member_cif)), "YOU JUST EARNED LOYALTY POINTS ON THE FIRST BANK LOYALTY PROGRAMME", EmailDispatcher::buildTransactionTemplate(self::$placeholders, $values), 'no-reply@greenrewards.com');
          }
          TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
          'status_code'=>$repsonse['status'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date, 'status_message'=>$repsonse['Status_message']]);

      }else{
          //Transaction::where('member_cif', $pendingTransaction->member_cif)->update(['tries'=>$pendingTransaction->tries+ 1 ]);
          Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 3]);
          TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
          'status_code'=>$repsonse['status'], 'status_message'=>$repsonse['Status_message'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date]);


      }
  }else{


  }
}
else{
  //return "failed to find member by membership_id: " . $pendingTransaction->member_cif . "<br>";
}
}
   // }
} else{
  $data['message'] = "no transactions on queue for migration";
  //print_r($data);
}
}

public static function migrateTransaction5():void{
  $success_count = 0;  $failure_count = 0;
  $data = [];
  $arrayToPush = array('Company_username'=>self::$username,
  'Company_password'=>parent::passwordReturn(), 'API_flag'=>'stran');
  $pendingTransactions = Transaction::where('status', 0)->select('member_cif', 'status', 'product_code', 'branch_code', 'transaction_reference', 'channel', 'product_code', 'transaction_type')->limit(1000);
  if($pendingTransactions->count() > 0){
    foreach($pendingTransactions->get() as $pendingTransaction){
      //$pendingTransaction->quantity  = 1;
      $membership_id_resolved = parent::string_encrypt(parent::resolveMemberReference($pendingTransaction->member_cif), self::$key,self::$iv);
        $arrayToPush = array(
          'Company_username'=>self::$username,
          'Company_password'=>parent::passwordReturn(),
          'Membership_ID'=>$membership_id_resolved,
          'Transaction_Date'=>$pendingTransaction->transaction_date,
          'Transaction_Type_code'=>$pendingTransaction->transaction_type,
          'Transaction_channel_code'=>$pendingTransaction->channel,
          'Transaction_amount'=>$pendingTransaction->amount,
          'Branch_code'=>$pendingTransaction->branch_code,
          'Transaction_ID'=>$pendingTransaction->transaction_reference,
          'Product_Code' =>$pendingTransaction->product_code,
          'Product_Quantity' =>$pendingTransaction->quantity,
          'API_flag' => 'stran'
          );

          //print_r($pendingTransaction->member_cif);
          $member =  Enrollment::where('member_cif', trim($pendingTransaction->member_cif))->first();
          //print_r($member);
          $product_name = json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/get_product_group_name?product_code=".$pendingTransaction->product_code), true);
          if (isset($member->first_name)){
          $product_name = $product_name['product_brand_name'];
          $values = array($member->first_name, $member->last_name, 0, 0, parent::$program, $member->loyalty_number, self::$link, $product_name);
          $resp =
          parent::pushToPERX(parent::$url, $arrayToPush, parent::$headerPayload);
          //print_r($resp);
          $repsonse = json_decode($resp, true);
      if ($repsonse){
      if ($repsonse['status'] == 1001){
          //print_r($repsonse);
          $values[2] = $repsonse['Loyalty_points']?number_format($repsonse['Loyalty_points']):0;
          $customer_balance = CurlService::makeGet("https://perxapi.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$member->loyalty_number);//, true); //["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;
          $customer_balance = json_decode($customer_balance, true);
          $values[3] = number_format($customer_balance['profile'][0]['Current_balance']);//json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;


          //SendNotificationService::sendMail($repsonse['Email_subject'], $repsonse['Email_body'], $repsonse['bcc_email_address']);
          Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 1]);
          if(intval($values[2])>0){
              EmailDispatcher::pendMails(parent::resolveMemberReference(trim($pendingTransaction->member_cif)), "YOU JUST EARNED LOYALTY POINTS ON THE FIRST BANK LOYALTY PROGRAMME", EmailDispatcher::buildTransactionTemplate(self::$placeholders, $values), 'no-reply@greenrewards.com');
          }
          TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
          'status_code'=>$repsonse['status'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date, 'status_message'=>$repsonse['Status_message']]);

      }else{
          //Transaction::where('member_cif', $pendingTransaction->member_cif)->update(['tries'=>$pendingTransaction->tries+ 1 ]);
          Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 3]);
          TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
          'status_code'=>$repsonse['status'], 'status_message'=>$repsonse['Status_message'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date]);


      }
  }else{


  }
}
else{
  //return "failed to find member by membership_id: " . $pendingTransaction->member_cif . "<br>";
}
}
   // }
} else{
  $data['message'] = "no transactions on queue for migration";
  //print_r($data);
}
}

  public static function runSpecificTransaction($data){
    $success_count = 0;  $failure_count = 0;
    $pendingTransactions = Transaction::whereIn('member_cif', $data);
    if($pendingTransactions->count() > 0){
        foreach($pendingTransactions->get() as $pendingTransaction){
          //$pendingTransaction->quantity  = 1;
          $membership_id_resolved = parent::string_encrypt(parent::resolveMemberReference($pendingTransaction->member_cif), self::$key,self::$iv);
            $arrayToPush = array(
              'Company_username'=>self::$username,
              'Company_password'=>parent::passwordReturn(),
              'Membership_ID'=>$membership_id_resolved,
              'Transaction_Date'=>$pendingTransaction->transaction_date,
              'Transaction_Type_code'=>$pendingTransaction->transaction_type,
              'Transaction_channel_code'=>$pendingTransaction->channel,
              'Transaction_amount'=>$pendingTransaction->amount,
              'Branch_code'=>$pendingTransaction->branch_code,
              'Transaction_ID'=>$pendingTransaction->transaction_reference,
              'Product_Code' =>$pendingTransaction->product_code,
              'Product_Quantity' =>$pendingTransaction->quantity,
              'API_flag' => 'stran'
              );

              //print_r($pendingTransaction->member_cif);
              $member =  Enrollment::where('member_cif', trim($pendingTransaction->member_cif))->first();
              //print_r($member);
              $product_name = json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/get_product_group_name?product_code=".$pendingTransaction->product_code), true);
              if (isset($member->first_name)){
              $product_name = $product_name['product_brand_name'];
              $values = array($member->first_name, $member->last_name, 0, 0, parent::$program, $member->loyalty_number, self::$link, $product_name);
              $resp =
              parent::pushToPERX(parent::$url, $arrayToPush, parent::$headerPayload);
              //print_r($resp);
              $repsonse = json_decode($resp, true);
          if ($repsonse){
          if ($repsonse['status'] == 1001){
              //print_r($repsonse);
              $values[2] = $repsonse['Loyalty_points']?number_format($repsonse['Loyalty_points']):0;
              $customer_balance = CurlService::makeGet("https://perxapi.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$member->loyalty_number);//, true); //["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;
              $customer_balance = json_decode($customer_balance, true);
              $values[3] = number_format($customer_balance['profile'][0]['Current_balance']);//json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;


              //SendNotificationService::sendMail($repsonse['Email_subject'], $repsonse['Email_body'], $repsonse['bcc_email_address']);
              Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 1]);
              if(intval($values[2])>0){
                  EmailDispatcher::pendMails(parent::resolveMemberReference(trim($pendingTransaction->member_cif)), "YOU JUST EARNED LOYALTY POINTS ON THE FIRST BANK LOYALTY PROGRAMME", EmailDispatcher::buildTransactionTemplate(self::$placeholders, $values), 'no-reply@greenrewards.com');
              }
              TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
              'status_code'=>$repsonse['status'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date, 'status_message'=>$repsonse['Status_message']]);

          }else{
              //Transaction::where('member_cif', $pendingTransaction->member_cif)->update(['tries'=>$pendingTransaction->tries+ 1 ]);
              Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 3]);
              TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
              'status_code'=>$repsonse['status'], 'status_message'=>$repsonse['Status_message'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date]);


          }
      }else{


      }
    }
    else{
      //return "failed to find member by membership_id: " . $pendingTransaction->member_cif . "<br>";
  }
  }
       // }
  } else{
      $data['message'] = "no transactions on queue for migration";
      //print_r($data);
  }
  }


  public static function migrateTransactionCron($cron_id):void{
    $success_count = 0;  $failure_count = 0;
    $data = [];
    $arrayToPush = array('Company_username'=>self::$username,
    'Company_password'=>parent::passwordReturn(), 'API_flag'=>'stran');
    $pendingTransactions = Transaction::where('status', 0)->where('cron_id', $cron_id)->select('member_cif', 'status', 'product_code', 'branch_code', 'transaction_reference', 'channel', 'product_code', 'transaction_type')->limit(1000);
    if($pendingTransactions->count() > 0){
      foreach($pendingTransactions->get() as $pendingTransaction){
        //$pendingTransaction->quantity  = 1;
        $membership_id_resolved = parent::string_encrypt(parent::resolveMemberReference($pendingTransaction->member_cif), self::$key,self::$iv);
          $arrayToPush = array(
            'Company_username'=>self::$username,
            'Company_password'=>parent::passwordReturn(),
            'Membership_ID'=>$membership_id_resolved,
            'Transaction_Date'=>$pendingTransaction->transaction_date,
            'Transaction_Type_code'=>$pendingTransaction->transaction_type,
            'Transaction_channel_code'=>$pendingTransaction->channel,
            'Transaction_amount'=>$pendingTransaction->amount,
            'Branch_code'=>$pendingTransaction->branch_code,
            'Transaction_ID'=>$pendingTransaction->transaction_reference,
            'Product_Code' =>$pendingTransaction->product_code,
            'Product_Quantity' =>$pendingTransaction->quantity,
            'API_flag' => 'stran'
            );

            //print_r($pendingTransaction->member_cif);
            $member =  Enrollment::where('member_cif', trim($pendingTransaction->member_cif))->first();
            //print_r($member);
            $product_name = json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/get_product_group_name?product_code=".$pendingTransaction->product_code), true);
            if (isset($member->first_name)){
            $product_name = $product_name['product_brand_name'];
            $values = array($member->first_name, $member->last_name, 0, 0, parent::$program, $member->loyalty_number, self::$link, $product_name);
            $resp =
            parent::pushToPERX(parent::$url, $arrayToPush, parent::$headerPayload);
            //print_r($resp);
            $repsonse = json_decode($resp, true);
        if ($repsonse){
        if ($repsonse['status'] == 1001){
            //print_r($repsonse);
            $values[2] = $repsonse['Loyalty_points']?number_format($repsonse['Loyalty_points']):0;
            $customer_balance = CurlService::makeGet("https://perxapi.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$member->loyalty_number);//, true); //["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;
            $customer_balance = json_decode($customer_balance, true);
            $values[3] = number_format($customer_balance['profile'][0]['Current_balance']);//json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"] > 0?json_decode(CurlService::makeGet("https://perxapi2.perxclm.com/api/profile?token=LSLonlypass&membership_id=".$membership_id_resolved), true)["profile"][0]["Current_balance"]:0.00;


            //SendNotificationService::sendMail($repsonse['Email_subject'], $repsonse['Email_body'], $repsonse['bcc_email_address']);
            Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 1]);
            if(intval($values[2])>0){
                EmailDispatcher::pendMails(parent::resolveMemberReference(trim($pendingTransaction->member_cif)), "YOU JUST EARNED LOYALTY POINTS ON THE FIRST BANK LOYALTY PROGRAMME", EmailDispatcher::buildTransactionTemplate(self::$placeholders, $values), 'no-reply@greenrewards.com');
            }
            TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
            'status_code'=>$repsonse['status'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date, 'status_message'=>$repsonse['Status_message']]);

        }else{
            //Transaction::where('member_cif', $pendingTransaction->member_cif)->update(['tries'=>$pendingTransaction->tries+ 1 ]);
            Transaction::where('transaction_reference', $pendingTransaction->transaction_reference)->update(['status' => 3]);
            TransactionReportLog::create(['customer_reference'=>$pendingTransaction->member_cif, 'branch_code'=>$pendingTransaction->branch_code,
            'status_code'=>$repsonse['status'], 'status_message'=>$repsonse['Status_message'], 'account_number'=>$pendingTransaction->account_number, 'transaction_date'=>$pendingTransaction->transaction_date]);


        }
    }else{


    }
  }
  else{
    //return "failed to find member by membership_id: " . $pendingTransaction->member_cif . "<br>";
}
}
     // }
} else{
    $data['message'] = "no transactions on queue for migration";
    //print_r($data);
}
}




}

?>
