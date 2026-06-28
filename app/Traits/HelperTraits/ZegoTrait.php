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
    public static function sendToZego($Action, $RoomId, $FromUserId, $MessageContent, $IsTest = null)
    {

        $roomId = $RoomId ?? request()->room_id;
        $IsTest = $IsTest ?? (app()->environment('production') ? 'false' : 'true');
        $url = 'https://rtc-api.zego.im';
        $AppId = self::zegoData('zego_app_id');
        $serverSecret = self::zegoData('zego_server_secret');
        $SignatureNonce = self::getSignatureNonce();
        $Timestamp = time();
        $str = $AppId . $SignatureNonce . $serverSecret . $Timestamp;
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
            'SignatureVersion' => $SignatureVersion,
            'IsTest' => $IsTest
        ];
        $headers = [];
        try {

            $response = Http::withHeaders($headers)->acceptJson()->timeout(20)->get($url, $params)->json();

            if ($response === null || (isset($response['Code']) && $response['Code'] != 0)) {
                Log::channel('zego')->warning('ZegoTrait::sendToZego failed', [
                    'action' => $Action,
                    'roomId' => $roomId,
                    'fromUserId' => $FromUserId,
                    'response' => $response,
                ]);
            }

            return $response;
        } catch (\Exception $exception) {
            Log::channel('zego')->error('ZegoTrait::sendToZego exception', [
                'action' => $Action,
                'roomId' => $RoomId,
                'fromUserId' => $FromUserId,
                'error' => $exception->getMessage(),
            ]);
        }

        return null;
    }


    public static function utdStream($Action, $RoomId, $FromUserId, $MessageContent, $IsTest = null)
    {

        $roomId = $RoomId ?? request()->room_id;
        $IsTest = $IsTest ?? (app()->environment('production') ? 'false' : 'true');
        $url = 'https://rtc-api.zego.im';
        $AppId = self::zegoData('zego_app_id');
        $serverSecret = self::zegoData('zego_server_secret');
        $SignatureNonce = self::getSignatureNonce();
        $Timestamp = time();
        $str = $AppId . $SignatureNonce . $serverSecret . $Timestamp;
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
            'SignatureVersion' => $SignatureVersion,
            'IsTest' => $IsTest
        ];
        $headers = [];
        try {

            $response = Http::withHeaders($headers)->acceptJson()->timeout(20)->get($url, $params)->json();

            if ($response === null || (isset($response['Code']) && $response['Code'] != 0)) {
                Log::channel('zego')->warning('ZegoTrait::sendToZego failed', [
                    'action' => $Action,
                    'roomId' => $roomId,
                    'fromUserId' => $FromUserId,
                    'response' => $response,
                ]);
            }

            return $response;
        } catch (\Exception $exception) {
            Log::channel('zego')->error('ZegoTrait::sendToZego exception', [
                'action' => $Action,
                'roomId' => $RoomId,
                'fromUserId' => $FromUserId,
                'error' => $exception->getMessage(),
            ]);
        }

        return null;
    }

    public static function sendToZego_2($Action, $RoomId, $UserId, $UserName, $MessageContent, $IsTest = null)
    {
        $IsTest = $IsTest ?? (app()->environment('production') ? 'false' : 'true');
        $url = 'https://rtc-api.zego.im';
        $AppId = self::zegoData('zego_app_id');
        $serverSecret = self::zegoData('zego_server_secret');
        $SignatureNonce = self::getSignatureNonce();
        $Timestamp = time();
        //  $str = $AppId . $SignatureNonce . self::getConf('zego_server_secret') . $Timestamp;
        $str = $AppId . $SignatureNonce . $serverSecret . $Timestamp;
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

            $response = Http::withHeaders($headers)->acceptJson()->timeout(10)->get($url, $params)->json();
            if ($response === null || (isset($response['Code']) && $response['Code'] != 0)) {
                Log::channel('zego')->warning('ZegoTrait::sendToZego failed', [
                    'action' => $Action,
                    'roomId' => $RoomId,
                    'fromUserId' => $UserId,
                    'response' => $response,
                ]);
            }

            return $response;
        } catch (\Exception $exception) {
            Log::channel('zego')->error('ZegoTrait::sendToZego exception', [
                'action' => $Action,
                'roomId' => $RoomId,
                'fromUserId' => $UserId,
                'error' => $exception->getMessage(),
            ]);
        }
        return;
    }

    public static function sendToZego_3($Action, $RoomId, $UserId, $IsTest = null)
    {
        $IsTest = $IsTest ?? (app()->environment('production') ? 'false' : 'true');
        $url = 'https://rtc-api.zego.im';
        $AppId = self::zegoData('zego_app_id');
        $serverSecret = self::zegoData('zego_server_secret');
        $SignatureNonce = self::getSignatureNonce();
        $Timestamp = time();
        //  $str = $AppId . $SignatureNonce . self::getConf('zego_server_secret') . $Timestamp;
        $str = $AppId . $SignatureNonce . $serverSecret . $Timestamp;
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
            if ($res === null || (isset($res['Code']) && $res['Code'] != 0)) {
                Log::channel('zego')->warning('ZegoTrait::sendToZego_3 failed', [
                    'action' => $Action,
                    'roomId' => $RoomId,
                    'fromUserId' => $UserId,
                    'response' => $res,
                ]);
            }
        } catch (\Exception $exception) {
            Log::channel('zego')->error('ZegoTrait::sendToZego_3 exception', [
                'action' => $Action,
                'roomId' => $RoomId,
                'fromUserId' => $UserId,
                'error' => $exception->getMessage(),
            ]);
        }

        return $res;
    }

    public static function sendToZego_4($Action, $RoomId, $fromUserId, $toUserId, $MessageContent, $IsTest = null)
    {
        $IsTest = $IsTest ?? (app()->environment('production') ? 'false' : 'true');
        $url = 'https://rtc-api.zego.im';
        $AppId = self::zegoData('zego_app_id');
        $serverSecret = self::zegoData('zego_server_secret');
        $SignatureNonce = self::getSignatureNonce();
        $Timestamp = time();
        // $str = $AppId . $SignatureNonce . self::getConf('zego_server_secret') . $Timestamp;
        $str              = $AppId . $SignatureNonce . $serverSecret . $Timestamp;
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
            if ($res === null || (isset($res['Code']) && $res['Code'] != 0)) {
                Log::channel('zego')->warning('ZegoTrait::sendToZego_4 failed', [
                    'action' => $Action,
                    'roomId' => $RoomId,
                    'fromUserId' => $fromUserId,
                    'toUserId' => $toUserId,
                    'response' => $res,
                ]);
            }
        } catch (\Exception $exception) {
            Log::channel('zego')->error('ZegoTrait::sendToZego_4 exception', [
                'action' => $Action,
                'roomId' => $RoomId,
                'fromUserId' => $fromUserId,
                'toUserId' => $toUserId,
                'error' => $exception->getMessage(),
            ]);
        }



        return $res;
    }
}
