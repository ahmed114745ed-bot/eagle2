<?php


namespace App\Traits\HelperTraits;


use App\Helpers\Common;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use function Laravel\Prompts\error;

trait ZegoTrait
{

    public static  $serverSecret;
    public static  $appId;

    public static function getSignatureNonce()
    {
        return bin2hex(random_bytes(8));
    }

    // public static function GenerateSignature()
    // {
    //     $str = self::getConf ('zego_app_id').static::getSignatureNonce ().self::getConf('zego_server_secret').time();
    //     $signature = md5($str);
    //     return $signature;
    // }

    public static function GenerateSignature()
    {
        $str = self::zegoData('zego_app_id') . static::getSignatureNonce() . self::zegoData('zego_server_secret') . time();
        $signature = md5($str);
        return $signature;
    }

    public static function get_users_list()
    {

        $url = 'https://rtc-api.zego.im';
        //  $AppId = self::getConf ('zego_app_id');
        $AppId = self::zegoData('zego_app_id');
        $SignatureNonce = self::getSignatureNonce();
        $Timestamp = time();
        //$str = 	$AppId.$SignatureNonce.self::getConf('zego_server_secret').$Timestamp;
        $str =     $AppId . $SignatureNonce . self::zegoData('zego_server_secret') . $Timestamp;
        $signature = md5($str);
        $SignatureVersion = '2.0';
        $params = [
            'Action' => 'DescribeUserList',
            'RoomId' => 916,
            'AppId' => $AppId,
            'SignatureNonce' => $SignatureNonce,
            'Timestamp' => $Timestamp,
            'Signature' => $signature,
            'SignatureVersion' => $SignatureVersion,
            //'IsTest'=>$IsTest
        ];
        $headers = [];
        try {
            return  Http::withHeaders($headers)->acceptJson()->timeout(20)->get($url, $params)->json();
        } catch (\Exception $exception) {
        }

        return null;
    }
    public static function sendToZego($Action, $RoomId, $FromUserId, $MessageContent, $IsTest = 'false')
    {
        $phoneRequest = [
        'phone_to_server_url' => request()->fullUrl(), // الرابط الذي طلبه الهاتف
        'phone_ip' => request()->ip(),                 // IP الهاتف
        'phone_method' => request()->method(),         // POST أو GET
        'phone_payload' => request()->all(),           // البيانات التي أرسلها الهاتف للسيرفر
        ];
        $url = 'https://rtc-api.zego.im';
        // $AppId = self::getConf('zego_app_id');
        $AppId = self::zegoData('zego_app_id');
        $SignatureNonce = self::getSignatureNonce();
        $Timestamp = time();
        // $str = $AppId . $SignatureNonce . self::getConf('zego_server_secret') . $Timestamp;
        $str = $AppId . $SignatureNonce . self::zegoData('zego_server_secret') . $Timestamp;
        $signature = md5($str);
        $SignatureVersion = '2.0';
        $params = [
            'Action' => $Action,
            'RoomId' => $RoomId,
            'FromUserId' => $FromUserId,
            'MessageContent' => $MessageContent,
            'AppId' => $AppId,
            'SignatureNonce' => $SignatureNonce,
            'Timestamp' => $Timestamp,
            'Signature' => $signature,
            'SignatureVersion' => $SignatureVersion,
            'IsTest' => $IsTest
        ];
        $headers = [];
        try {
          
            $response = Http::withHeaders($headers)->acceptJson()->timeout(20)->get($url, $params)->json();
            $pendingRequest = Http::withHeaders($headers)->acceptJson()->timeout(20);
        
                
                $fullUrlWithParams = $url . '?' . http_build_query($params); 
                $requestHeaders = $headers;

            // Log failed Zego API calls for debugging
            if ($response === null || (isset($response['Code']) && $response['Code'] != 0)) {
                Log::warning('ZegoTrait::sendToZego failed', [
                    'action' => $Action,
                    'roomId' => $RoomId,
                    'fromUserId' => $FromUserId,
                    'response' => $response,
                ]);
              Log::warning('⚠️ الشرح التفصيلي للخطأ:', [
                'STEP_1_PHONE_TO_SERVER' => $phoneRequest, 
                'STEP_2_SERVER_TO_ZEGO' => [               
                    'endpoint' => $url . '?' . http_build_query($params),
                    'response' => $response->json(),
                ]
            ]);
            }
            
            return $response;
        } catch (\Exception $exception) {
            Log::error('ZegoTrait::sendToZego exception', [
                'action' => $Action,
                'roomId' => $RoomId,
                'fromUserId' => $FromUserId,
                'error' => $exception->getMessage(),
            ]);
        }

        return null;
    }

    public static function sendToZego_2($Action, $RoomId, $UserId, $UserName, $MessageContent, $IsTest = 'false')
    {
        $url = 'https://rtc-api.zego.im';
        // $AppId = self::getConf('zego_app_id');
        $AppId = self::zegoData('zego_app_id');
        $SignatureNonce = self::getSignatureNonce();
        $Timestamp = time();
        //  $str = $AppId . $SignatureNonce . self::getConf('zego_server_secret') . $Timestamp;
        $str = $AppId . $SignatureNonce . self::zegoData('zego_server_secret') . $Timestamp;
        $signature = md5($str);
        $SignatureVersion = '2.0';
        $params = [
            'Action' => $Action,
            'RoomId' => $RoomId,
            'UserId' => $UserId,
            'UserName' => $UserName,
            'MessageCategory' => 1,
            'MessageContent' => $MessageContent,
            'AppId' => $AppId,
            'SignatureNonce' => $SignatureNonce,
            'Timestamp' => $Timestamp,
            'Signature' => $signature,
            'SignatureVersion' => $SignatureVersion,
            'IsTest' => $IsTest
        ];
        $headers = [];
        try {

            Http::withHeaders($headers)->acceptJson()->timeout(10)->get($url, $params)->json();
        } catch (\Exception $exception) {
        }
        return;
    }

    public static function sendToZego_3($Action, $RoomId, $UserId, $IsTest = 'false')
    {
        $url = 'https://rtc-api.zego.im';

        // $AppId = self::getConf('zego_app_id');
        $AppId = self::zegoData('zego_app_id');
        $SignatureNonce = self::getSignatureNonce();
        $Timestamp = time();
        //  $str = $AppId . $SignatureNonce . self::getConf('zego_server_secret') . $Timestamp;
        $str = $AppId . $SignatureNonce . self::zegoData('zego_server_secret') . $Timestamp;
        $signature = md5($str);
        $SignatureVersion = '2.0';
        $params = [
            'Action' => $Action,
            'RoomId' => $RoomId,
            'UserId[]' => $UserId,
            'AppId' => $AppId,
            'SignatureNonce' => $SignatureNonce,
            'Timestamp' => $Timestamp,
            'Signature' => $signature,
            'SignatureVersion' => $SignatureVersion,
            'IsTest' => $IsTest
        ];
        $headers = [];
        try {

            $res = Http::withHeaders($headers)->acceptJson()->timeout(10)->get($url, $params)->json();
        } catch (\Exception $exception) {
        }

        return $res;
    }

    public static function sendToZego_4($Action, $RoomId, $fromUserId, $toUserId, $MessageContent, $IsTest = 'false')
    {
        $url = 'https://rtc-api.zego.im';
       // $AppId = self::getConf('zego_app_id');
        $AppId = self::zegoData('zego_app_id');
        $SignatureNonce = self::getSignatureNonce();
        $Timestamp = time();
       // $str = $AppId . $SignatureNonce . self::getConf('zego_server_secret') . $Timestamp;
        $str = $AppId . $SignatureNonce . self::zegoData('zego_server_secret') . $Timestamp;
        $signature = md5($str);
        $SignatureVersion = '2.0';
        $params = [
            'Action' => $Action,
            'RoomId' => $RoomId,
            'FromUserId' => $fromUserId,
            'ToUserId[]' => $toUserId,
            'MessageContent' => $MessageContent,
            'AppId' => $AppId,
            'SignatureNonce' => $SignatureNonce,
            'Timestamp' => $Timestamp,
            'Signature' => $signature,
            'SignatureVersion' => $SignatureVersion,
            'IsTest' => $IsTest
        ];
        $headers = [];
        try {
            $res = Http::withHeaders($headers)->acceptJson()->timeout(10)->get($url, $params)->json();
        } catch (\Exception $exception) {
        }

        return $res;
    }
}
