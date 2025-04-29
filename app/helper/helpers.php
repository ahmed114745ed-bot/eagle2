<?php

use Carbon\Carbon;
use App\Helpers\Common;
use Encore\Admin\Admin;
use App\Classes\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use App\Services\AgoraRtmTokenBuilder;
use Yasser\AgoraToken\RtmTokenBuilder;
use BoogieFromZk\AgoraToken\RtcTokenBuilder2;;


const LUCKY_REDIS_KEY = "thresholds_lucky_prices";
const PK_IMAGE = 'custom_image/pk.png';
const CINEMA_IMAGE = 'custom_image/back-black.png';
const GAME_COINS_PLAY = 'game_coins_play_#';




function generateRtcToken($channelName, $uid, $expiresInSeconds = 86400)
{
    $appID = config('app.agora_app_id');
    $appCertificate = config('app.agora_certificate');
    $role = RtcTokenBuilder2::ROLE_PUBLISHER;

    $token = RtcTokenBuilder2::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $expiresInSeconds);

    return $token;
}

function generateAgoraRtmToken($channelName, $rtmUid)
{

    if (!$rtmUid) {
        return null;
    }

    $appId = config('services.agora.app_id');
    $appCertificate = config('services.agora.app_certificate');
    $user = $rtmUid;
    $expireTimeInSeconds = 86400;
    $role = RtcTokenBuilder2::ROLE_PUBLISHER;

    $expireTimeInSeconds = 86400;


    $token = RtcTokenBuilder2::buildTokenWithRtm(
        $appId,
        $appCertificate,
        $channelName,
        $rtmUid,
        $role,
        time() + 86400,
        time() + 86400,
    );


    return $token;
}



function translate($typeArray)
{
    $arr = [];
    foreach ($typeArray as $key => $type) {
        $arr[$key]  = __($type);
    }
    return $arr;
}

function translateCategory($typeArray)
{
    $arr = [];
    foreach ($typeArray as $key => $type) {
        $arr[$type]  = __($type);
    }
    return $arr;
}


function generateSignature($nonce, $appKey, $timestamp)
{
    $data = sprintf("%s%s%d", $nonce, $appKey, $timestamp);
    return md5($data);
}

function generatesignatureNonce()
{
    $tempByte = random_bytes(8);
    $signatureNonce = bin2hex($tempByte);
    return $signatureNonce;
}

function getNonce()
{
    return Str::random(16);
}

if (!function_exists('check')) {
    function check()
    {
        $guards = array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            if (auth()->guard($guard)->check()) {
                return auth()->guard($guard);
            }
        }
    }
}

if (!function_exists('calculateUserUsd')) {
    function calculateUserUsd($diamonds,  $value)
    {
        $coins = Common::getMaxCoins() ?? 1;

        $endFormatted = $diamonds / $coins;
        $endFormatted = is_numeric($endFormatted) ? floatval($endFormatted) : 0;
        $value = is_numeric($value) ? floatval($value) : 0;

        $userUsd = ($endFormatted * $value) / 100;

        return common::roundToTwoDecimalPlaces($userUsd);
    }
}

if (!function_exists('checkStoredProcedureExists')) {
    function checkStoredProcedureExists($procedureName)
    {
        $databaseName = config('database.connections.mysql.database'); // Get the database name from the environment file

        $result = \DB::select(
            'SELECT COUNT(*) as count
        FROM information_schema.ROUTINES
        WHERE ROUTINE_TYPE = ?
        AND ROUTINE_SCHEMA = ?
        AND ROUTINE_NAME = ?',
            ['PROCEDURE', $databaseName, $procedureName]
        );

        return $result[0]->count > 0;
    }


    function generatesignatureNonces()
    {
        $tempByte = random_bytes(8);
        $signatureNonce = bin2hex($tempByte);
        return $signatureNonce;
    }

    function generateSignatures($nonce, $appKey, $timestamp)
    {
        $data = sprintf("%s%s%d", $nonce, $appKey, $timestamp);
        return md5($data);
    }
}

if (!function_exists('human_file_size')) {
    function human_file_size($bytes, $decimals = 2)
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}

if (!function_exists('get_file_details')) {
    function get_file_details($path)
    {
        return app('upload.manager')->fileDetails($path);
    }


    if (!function_exists('numToString')) {
        function numToString($number)
        {
            $units = ['', 'K', 'M', 'B', 'T', 'D', 'E', 'F'];
            for ($i = 0; $number >= 1000; $i++) {
                $number /= 1000;
            }
            return round($number, 2) . $units[$i];
        }
    }

    if (!function_exists('numToStringNew')) {
        function numToStringNew($number)
        {
            $units = ['', 'K', 'M', 'B', 'T', 'D', 'E', 'F'];
            for ($i = 0; $number >= 1000 && $i < count($units) - 1; $i++) {
                $number /= 1000;
            }
            $number = floor($number * 10) / 10;
            return $number . $units[$i];
        }
    }

    if (!function_exists('dispatchJobToQueue')) {
        function dispatchJobToQueue($job, $queueName = 'database')
        {
            $connection = config('queue.default');
            $queueNames = config('queue.connections.' . $queueName . '.queue');

            $minQueueSize  = null;
            $selectedQueue = null;

            foreach ($queueNames as $queueName) {
                $queueSize = Queue::connection($connection)->size($queueName);
                //            $queueSize = \DB::table('jobs')->where('queue', $queueName)->count();
                //            $jobCount  = \DB::table('job_statistics')->where('queue', $queueName)->value('job_count');

                if ($minQueueSize === null || $queueSize  < $minQueueSize) {
                    $minQueueSize  = $queueSize;
                    $selectedQueue = $queueName;
                }
            }

            \Illuminate\Support\Facades\Queue::connection($connection)->pushOn($selectedQueue, $job);
        }
    }

    if (!function_exists('settings')) {

        function settings(): AppSetting
        {
            return new AppSetting();
        }
    }

    if (!function_exists('getOfficialMessage')) {

        function getOfficialMessage($message): string
        {
            //api.welcome#name-محمد#app_name-Laravel
            return '';
        }
    }
    if (!function_exists('getImagePath')) {

        function getImagePath(?string $path = null): ?string
        {
            return $path == null ? null : getDriverUrl() . '/' . $path;
        }
    }

    if (!function_exists('isImageExists')) {

        function isImageExists($url)
        {
            if (empty($url)) {
                return false; // Prevent empty path error
            }

            $context = stream_context_create([
                'http' => ['timeout' => 2] // Set a 2-second timeout
            ]);
            $headers = @get_headers($url, 1, $context);
            return $headers && strpos($headers[0], '200') !== false;
        }
    }

    if (!function_exists('httpImage')) {
        function httpImage($image)
        {
            $response =  http::get($image);

            $folder = 'images/' . basename($image);
            Storage::disk(\config('filesystems.default'))->put($folder,  $response->body());
            return $folder;
        }
    }

    if (!function_exists('getDriverUrl')) {

        function getDriverUrl(): ?string
        {
            return config('filesystems.disks.' . \config('filesystems.default') . '.url');
        }
    }

    if (!function_exists('dispatchRoomsRedis')) {

        function dispatchRoomsRedis(int $roomId, int $userId, $coins = 0, $data = null, string $type = 'charisma'): void
        {

            $key = 'CharismaGift_' . $type . '_' . $userId . '_' . $roomId . '_' . implode($data);
            $data = serialize($data);

            try {

                $rData = Redis::get($key);

                if ($rData) {
                    $rData = unserialize($rData);
                    if (isset($coins)) {
                        $rData['coins'] += $coins;
                        Redis::set($key, serialize($rData));
                    }
                } else {

                    $values = [
                        'user_id'    => $userId,
                        'room_id'    => $roomId,
                        'data'       => $data,
                        'type'       => $type,
                        'coins'      => $coins,
                        'created_at' => now(),
                        'updated_at' => now(),

                    ];
                    \Illuminate\Support\Facades\Redis::set($key, serialize($values));
                }

                //            \Illuminate\Support\Facades\DB::table('room_jobs')->insert($values);
            } catch (Exception $exception) {
            }
        }
    }
}


if (!function_exists('isSubdomain')) {

    function isSubdomain($host = null)
    {
        if (!$host) $host = \request()->getHost();
        $hostParts = explode('.', $host);

        if (count($hostParts) < 2) {
            return false;
        }
        //        $baseDomain = implode('.', array_slice($hostParts, -2));

        return count($hostParts) > 2;
    }
}
if (!function_exists('adjustColor')) {

    function adjustColor($hex, $rOffset = -30, $gOffset = -90, $bOffset = -60): string
    {
        // Convert the hex color to RGB
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");

        // Apply the offsets and ensure values are within 0-255
        $newR = max(0, min(255, $r - $rOffset));
        $newG = max(0, min(255, $g - $gOffset));
        $newB = max(0, min(255, $b - $bOffset));

        // Convert the new RGB values back to hex format
        return sprintf("#%02x%02x%02x", $newR, $newG, $newB);
    }

    function getInverseColor($hex): string
    {
        // Convert the hex color to RGB
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");

        // Calculate the inverse by subtracting each component from 255
        $inverseR = 255 - $r;
        $inverseG = 255 - $g;
        $inverseB = 255 - $b;

        // Convert the inverted RGB values back to hex format
        return sprintf("#%02x%02x%02x", $inverseR, $inverseG, $inverseB);
    }


    function adjustTextColor($hex, $lightnessFactor = 0.8, $darknessFactor = 0.2)
    {
        // Convert hex color to RGB
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");

        // Calculate brightness (perceived luminance)
        $brightness = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        if ($brightness > 0.5) {
            // If color is light, make it darker
            $newR = intval($r * $darknessFactor);
            $newG = intval($g * $darknessFactor);
            $newB = intval($b * $darknessFactor);
        } else {
            // If color is dark, make it lighter
            $newR = intval($r + (255 - $r) * $lightnessFactor);
            $newG = intval($g + (255 - $g) * $lightnessFactor);
            $newB = intval($b + (255 - $b) * $lightnessFactor);
        }

        // Convert back to hex
        return sprintf("#%02x%02x%02x", $newR, $newG, $newB);
    }
    function getLighterColor($hex, $lightness  = 0.9)
    {
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");

        // Calculate a lighter color closer to white by increasing each component
        $newR = intval($r + (255 - $r) * $lightness);
        $newG = intval($g + (255 - $g) * $lightness);
        $newB = intval($b + (255 - $b) * $lightness);

        // Convert back to hex
        return sprintf("#%02x%02x%02x", $newR, $newG, $newB);
    }
}


if (!function_exists('getPusherConfig')) {
    function getPusherConfig()
    {
        return \Illuminate\Support\Facades\Cache::remember('pusher_config', 60 * 60 * 24, function () {
            if (!isSubdomain()) {
                return null;
            }

            $Keys = ['app_id', 'app_key', 'app_secret', 'app_cluster'];
            $configs = \App\Models\Config::whereIn('name', $Keys)->pluck('value', 'name');

            $appId = !empty($configs->get('app_id')) ? $configs->get('app_id') : Config::get('broadcasting.pusher-default.app_id');
            $appKey = !empty($configs->get('app_key')) ? $configs->get('app_key') : Config::get('broadcasting.pusher-default.key');
            $appSecret = !empty($configs->get('app_secret')) ? $configs->get('app_secret') : Config::get('broadcasting.pusher-default.secret');
            $appCluster = !empty($configs->get('app_cluster')) ? $configs->get('app_cluster') : Config::get('broadcasting.pusher-default.options.cluster');

            return [
                'app_id' => $appId,
                'app_key' => $appKey,
                'app_secret' => $appSecret,
                'app_cluster' => $appCluster,
            ];
        });
    }
}
if (!function_exists('nameRoute')) {
    function nameRoute(string $name): string
    {
        $separators = ['.', '/'];
        $separator = null;
        $requestPath = \Request::path();

        if (\Str::startsWith($requestPath, 'preview')) { //admin.route.prefix,admin.auth.controller
            foreach ($separators as $s) {
                $valuesCount = count(explode($s, $name));
                if ($valuesCount > 1) $separator = $s;
            }
        }

        return $separator ? ('preview' . $separator . $name) : $name;
    }
}
if (!function_exists('getFileExtension')) {
    function getFileExtension($url)
    {
        return pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
    }
}
if (!function_exists('handleShowImageWithTypes')) {
    function handleShowImageWithTypes(string $uniqueId, ?string $url, int $width = null, int $height = null): string
    {
        $imageType = getFileExtension($url);
        if ($imageType == 'svga' || $imageType == 'zz') {
            $model = showSvgaImage($url, $uniqueId);

            return "<div class ='rtlSvga' id='$model' style='width: {$width}px !important; height: {$height}px !important;'> </div>";
        } elseif ($imageType == 'mp4') {
            return "
                <video width='$width' height='$height' controls autoplay muted loop>
                    <source src='$url' type='video/mp4'>
                    <source src='$url' type='video/webm'>

                    Your browser does not support the video tag.
                 </video>
                ";
        }

        return "<img src='$url' style='height: {$height}px !important; width: {$width}px !important;' alt='' />";
    }
}
if (!function_exists('userType')) {
    function userType($type)
    {
        switch ($type) {
            case 0:
                $userType = __("User");
                break;
            case 1:
                $userType = __("Host");
                break;
            case 2:
                $userType = __("Host Agent");
                break;
            case 3:
                $userType = __("Shipping Agent");
                break;
            case 4:
                $userType = __("Resort & Shipping Agent");
                break;
            case 5:
                $userType = __("Admin");
                break;
            default:
                $userType = $type; // Keep the original value if no match is found
                break;
        }
    }
}

if (!function_exists('convertNumbersToWestern')) {
    function convertNumbersToWestern($string)
    {
        $newNumbers = range(0, 9);

        if (app()->getLocale() == 'hi') {
            $numbers = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
        } elseif (app()->getLocale() == 'ar') {
            $numbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        }
        // Arabic (Eastern)


        return str_replace($numbers, $newNumbers, $string);
    }
}

if (!function_exists('showSvgaImage')) {
    /**
     * @param string|null $url
     * @return string
     */
    function showSvgaImage(?string $url, ?string $uniqueKey): string
    {
        $model = 'this' . $uniqueKey;
        $model2 = 'this2' . $uniqueKey;


        Admin::script("
                    var $model = new SVGA.Player('#$model');
                    $model.loops = 100;
                    $model.clearsAfterStop = false;

                    var $model2 = new SVGA.Parser('#$model');

                    function pauseAnimation() {
                        $model.pauseAnimation();
                    }

                    function stopAnimation() {
                        $model.stopAnimation();
                    }
                ");

        // Load SVGA animation and handle potential errors
        Admin::script("
                    try {
                        $model2.load('$url', function(videoItem) {
                            $model.setVideoItem(videoItem);
                            $model.startAnimation();

                            $model.onFinished(function() {
                                // Code for when the animation finishes
                            });
                        });
                    } catch (error) {
                        console.error('An error occurred:', error.message);
                    } finally {
                        console.log('Try...catch has finished executing.');
                    }
                ");
        return $model;
    }
}
