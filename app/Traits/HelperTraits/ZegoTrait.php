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

    protected static function buildZegoParams(array $baseParams): array
    {
        
        $roomId = $RoomId ?? request()->room_id;
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
            'RoomId' => $roomId,
            'FromUserId' => $FromUserId,
            'MessageContent' => $MessageContent,
            'AppId' => $AppId,
            'SignatureNonce' => $SignatureNonce,
            'Timestamp' => $Timestamp,
            'Signature' => $signature,
            'SignatureVersion' => '2.0',
        ];
    }

    protected static function sendZegoRequest(array $params, string $url = 'https://rtc-api.zego.im', int $timeout = 20)
    {
        try {
          
            $response = Http::withHeaders($headers)->acceptJson()->timeout(20)->get($url, $params)->json();

            if ($response === null || (isset($response['Code']) && $response['Code'] != 0)) {
                Log::warning('ZegoTrait::sendToZego failed', [
                    'action' => $Action,
                    'roomId' => $roomId,
                    'fromUserId' => $FromUserId,
                    'response' => $response,
                ]);
            }
            
            return $response;
        } catch (\Exception $exception) {
            Log::error('ZegoTrait::sendToZego exception', [
                'action' => $Action,
                'roomId' => (string)$RoomId,
                'fromUserId' => (string)$FromUserId,
                'message' => $MessageContent,
                'isTest' => $IsTest,
            ]);
        }

        $params = self::buildZegoParams([
            'Action' => $Action,
            'RoomId' => $RoomId,
            'FromUserId' => $FromUserId,
            'MessageContent' => $MessageContent,
            'IsTest' => $IsTest,
        ]);

        $response = self::sendZegoRequest($params);

        if ($response === null || (isset($response['Code']) && $response['Code'] != 0)) {
            Log::warning('ZegoTrait::sendToZego failed', [
                'action' => $Action,
                'roomId' => $RoomId,
                'fromUserId' => $FromUserId,
                'response' => $response,
            ]);
        }

        return $response;
    }

    public static function sendToZego_2($Action, $RoomId, $UserId, $UserName, $MessageContent, $IsTest = 'false')
    {
        $provider = Common::getConfig('sound_library');

        if ($provider == '3') {
            $utdKeys = Common::getUtdData();
            return self::sendUtdRequest([
                'apiKey' => $utdKeys['utd_app_id'] ?? null,
                'action' => $Action,
                'roomId' => (string)$RoomId,
                'fromUserId' => (string)$UserId,
                'userName' => $UserName,
                'message' => $MessageContent,
                'isTest' => $IsTest,
            ]);
        }

        $params = self::buildZegoParams([
            'Action' => $Action,
            'RoomId' => $RoomId,
            'UserId' => $UserId,
            'UserName' => $UserName,
            'MessageCategory' => 1,
            'MessageContent' => $MessageContent,
            'IsTest' => $IsTest,
        ]);

        self::sendZegoRequest($params, timeout: 10);
    }

    public static function sendToZego_3($Action, $RoomId, $UserId, $IsTest = 'false')
    {
        $provider = Common::getConfig('sound_library');

        if ($provider == '3') {
            $utdKeys = Common::getUtdData();
            return self::sendUtdRequest([
                'apiKey' => $utdKeys['utd_app_id'] ?? null,
                'action' => $Action,
                'roomId' => (string)$RoomId,
                'toUserId' => (array)$UserId,
                'isTest' => $IsTest,
            ]);
        }

        $params = self::buildZegoParams([
            'Action' => $Action,
            'RoomId' => $RoomId,
            'UserId[]' => $UserId,
            'IsTest' => $IsTest,
        ]);

        return self::sendZegoRequest($params, timeout: 10);
    }

    public static function sendToZego_4($Action, $RoomId, $fromUserId, $toUserId, $MessageContent, $IsTest = 'false')
    {
        $provider = Common::getConfig('sound_library');

        if ($provider == '3') {
            $utdKeys = Common::getUtdData();
            return self::sendUtdRequest([
                'apiKey' => $utdKeys['utd_app_id'] ?? null,
                'action' => $Action,
                'roomId' => (string)$RoomId,
                'fromUserId' => (string)$fromUserId,
                'toUserId' => (array)$toUserId,
                'message' => $MessageContent,
                'isTest' => $IsTest,
            ]);
        }

        $params = self::buildZegoParams([
            'Action' => $Action,
            'RoomId' => $RoomId,
            'FromUserId' => $fromUserId,
            'ToUserId[]' => $toUserId,
            'MessageContent' => $MessageContent,
            'IsTest' => $IsTest,
        ]);

        return self::sendZegoRequest($params, timeout: 10);
    }

}
