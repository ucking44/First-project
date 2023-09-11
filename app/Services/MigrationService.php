<?php

namespace App\Services;
use App\Models\Enrollment;
class MigrationService {
    public static $key = '!QAZXSW@#EDCVFR$';
    public static $iv = '5666685225155700';
    protected static $program = "Fidelity Green Rewards Loyalty Programme";
    protected static $placeholders = array('$memberID', '$first_name', '$last_name', '$pin', '$points', '$email', '$program');
    protected static $link = "https://loyalty.fidelitybank.ng/login.php";


    protected static $headerPayload = array(
        //'Content-Type: application/json',
    );
    protected static $url = "https://greenrewards.perxclm.com/api/v1/index.php";

    public function __construct()
    {

    }
    protected static function pushToPERX($url="https://greenrewards.perxclm.com/api/v1/index.php", $postFields, $payload)
    {

        $curl= curl_init();
        $ch = $curl;
        $timeout = 0; // Set 0 for no timeout.
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type= application/json"));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $result = curl_exec($ch);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch). "^^";
}else{
	$error_msg = '';
}

        curl_close($ch);

        return $result;
    }

    protected static function pushToPERXAcc($url="https://greenrewards.perxclm.com/api/v1/index.php", $params, $payload)
    {

        $curl= curl_init();
        $ch = $curl;
        $timeout = 0; // Set 0 for no timeout.
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type= application/json"));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $result = curl_exec($ch);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch). "^^";
}else{
	$error_msg = '';
}

        curl_close($ch);

        return $result;
    }

    public static function string_encrypt($string, $key, $iv) : string{
        $ciphering = "AES-128-CTR";
        $encryption_iv = '1234567891011121';
        $key = "SmoothJay";
        $options = 0;
        $encryption = openssl_encrypt($string, $ciphering,
            $key, $options, $iv);
        return $encryption;
    }

    public static function string_decrypt($encryption, $key, $iv) : string{
         $ciphering = "AES-128-CTR";
        $decryption_iv = '1234567891011121';
        $key = "SmoothJay";
        $options = 0;
        $decryption=openssl_decrypt($encryption, $ciphering,
        $key, $options, $iv);
        return $decryption;
    }

public static function passwordReturn(){
    return self::string_encrypt('Di@mond10$#', self::$key,self::$iv);
}
   // self::$password = parent::string_encrypt('Di@mond10$#', self::$key,self::$iv);



public static function resolveMemberReference($member_reference){
    $loyalty_number = Enrollment::where('member_reference', $member_reference)->select('loyalty_number')->first();
    return $loyalty_number ? $loyalty_number->loyalty_number:null;
}

public static function  isJSON($string){
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
 }


}
?>
