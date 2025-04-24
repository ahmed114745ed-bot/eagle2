<?php

namespace App\Helpers;

use App\Models\Setting;
use App\Models\Vip;
use App\Models\Pack;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use App\Models\Ware;
use App\Models\Agency;
use App\Models\Config;
use App\Models\Follow;
use App\Models\Target;
use Encore\Admin\Show;
use GuzzleHttp\Client;
use App\Models\Country;
use App\Models\GiftLog;
use App\Models\PackLog;
use App\Models\UserVip;
use App\Models\Background;
use App\Models\UserSallary;
use Illuminate\Support\Str;
use App\Models\ChargeWinner;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Factory;
use Illuminate\Support\Carbon;
use App\Models\OfficialMessage;
use Encore\Admin\Facades\Admin;
use App\Models\Owner_pid_target;
use App\Models\UsersJoinedAgency;
use Illuminate\Support\Facades\DB;
use Modules\Events\Entities\Winner;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Modules\Events\Entities\PkEvent;

use Illuminate\Support\Facades\Cache;
use Modules\Events\Entities\PkWinner;
use App\Models\AgencyMangerPullingOut;
use App\Notifications\AgencyOwnerRole;
use App\Traits\HelperTraits\InfoTrait;
use App\Traits\HelperTraits\RoomTrait;
use App\Traits\HelperTraits\ZegoTrait;
use Twilio\Rest\Client as TwilioClint;
use App\Http\Resources\CountryResource;
use App\Traits\HelperTraits\AdminTrait;
use App\Traits\HelperTraits\CalcsTrait;
use App\Traits\HelperTraits\MoneyTrait;
use Illuminate\Support\Facades\Storage;
use Modules\Events\Entities\WeeklyStar;
use App\Traits\HelperTraits\FilterTrait;
use App\Traits\HelperTraits\AttributesTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Classes\Facades\Agency as FacadesAgency;

class Common
{

    use CalcsTrait, AdminTrait, MoneyTrait, RoomTrait, AttributesTrait, ZegoTrait, InfoTrait, FilterTrait;


    public static function switch_events($event_type)
    {

        $avatar = null;

        if ($event_type == 'pk_event') {

            $event = PkEvent::PreviousEvent()->latest()->first();

            if ($event) {
                $pk_winner = PkWinner::with('user')->where('pk_event_id', $event->id)
                    ->where('pk_type', 'pk-king')
                    ->where('level', 1)
                    ->first();

                if ($pk_winner) {
                    $avatar = $pk_winner?->user?->profile?->avatar;
                }
            }
        } else if ($event_type == 'weekly_star') {
            $event = WeeklyStar::PreviousNewEvent()->first();

            if ($event) {
                $weekly_star = Winner::with('user')->where('weekly_star_id', $event->id)
                    ->where('level', 1)
                    ->first();

                if ($weekly_star) {
                    $avatar = $weekly_star->user->profile->avatar;
                }
            }
        } else if ($event_type == 'charge_event') {
            $now = Carbon::now();
            $pastMonth = $now->copy()->subMonth(); // Get the past month date

            $charge = ChargeWinner::with('user')
                ->where('year', $pastMonth->year)
                ->where('month', $pastMonth->month)
                ->OrderBy('total_charge', 'desc')
                ->first();

            if ($charge) {
                $avatar = $charge->user->profile->avatar;
            }
        }

        return $avatar;
    }

    public static function level_center_min($user_id)
    {
        $user = User::query()->find($user_id);
        if (!$user) {
            return new \stdClass();
        }

        $star_level = $user->received_level + $user->sub_receiver_level;
        $firstVip = Vip::where('level', $star_level)->where('type', 1)->first();
        $data['receiver_img'] = !is_null($firstVip) ? $firstVip->img : '';

        $gold_level = $user->sender_level + $user->sub_sender_level;
        $firstVip_type2 = Vip::where('level', $gold_level)->where('type', 2)->first();
        $data['sender_img'] = !is_null($firstVip_type2) ? $firstVip_type2->img : '';

        return $data;
    }
    public static function backgroundCount($oldBackgroundId = 0, $newBackGroundId = 0)
    {
        $oldBackground = Background::where("id", $oldBackgroundId)->orWhere("img", $oldBackgroundId)->first();
        if ($oldBackground != null) {
            $oldBackground->use_count -= 1;
            $oldBackground->save();
        }

        $newBackground = Background::find($newBackGroundId);
        if ($newBackground != null) {
            $newBackground->use_count += 1;
            $newBackground->save();
        }
    }

    public static function getLevels($levels): Collection
    {
        return Vip::query()->whereIn('type', [1, 2])->whereIn('level', $levels)->select(['id', 'type', 'img', 'level'])->get();
    }

    public static function apiResponse2(bool $success, $message, $data = null, $statusCode = null, $paginates = null, $isPagination = false)
    {

        if ($success == false && $statusCode == null) {
            $statusCode = 422;
        }

        if ($success == true && $statusCode == null) {
            $statusCode = 200;
        }



        $arr = [
            'success' => $success,

            'message' => __($message),

            //                'extra_data'=> [
            //                    'storage_base_url'=>self::getConf ('storage_base_url') ?:asset ('storage'),
            //                    'countries'=>$countries
            //                ],


            'paginates' => $paginates
        ];


        if ($isPagination) {

            $arr = array_merge($arr, $data->toArray());
        } else {
            $arr['data']  = $data;
        }


        return response()->json(
            $arr,
            $statusCode
        );
    }


    public static function apiResponse(bool $success, $message, $data = null, $statusCode = null, $paginates = null, $paginationKey = null)
    {
        if ($statusCode === null) {
            $statusCode = $success ? 200 : 422;
        }

        $paginationData = null;

        // Check if data is a collection directly or a paginated resource
        if ($paginationKey === null) {
            if ($data instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection) {
                $resourceData = $data->resource;

                if ($resourceData instanceof LengthAwarePaginator) {
                    $paginationData = self::paginationData($resourceData);
                    $data = $resourceData->getCollection();
                }
            } elseif ($data instanceof LengthAwarePaginator) {
                $paginationData = self::paginationData($data);
                $data = $data->getCollection();
            }
        }
        // Check if data contains the pagination key and it's paginated
        elseif (isset($data[$paginationKey])) {
            $dataForPagination = $data[$paginationKey];

            if ($dataForPagination instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection) {
                $resourceData = $dataForPagination->resource;

                if ($resourceData instanceof LengthAwarePaginator) {
                    $paginationData = self::paginationData($resourceData);
                    $data[$paginationKey] = $dataForPagination->getCollection();
                }
            } elseif ($dataForPagination instanceof LengthAwarePaginator) {
                $paginationData = self::paginationData($dataForPagination);
                $data[$paginationKey] = $dataForPagination->getCollection();
            }
        }
        if ($paginates) {

            foreach ($paginates as $paginationKey => $paginationCollection) {
                if ($paginationCollection instanceof LengthAwarePaginator) {
                    $paginationData = self::paginationData($paginationCollection);
                    $data[$paginationKey] = $paginationCollection->getCollection();
                }
            }
        }


        return response()->json(
            [
                'success'   => $success,
                'message'   => __($message),
                'data'      => $data,
                'paginates' => $paginationData,
            ],
            $statusCode
        );
    }

    // Pagination data formatting function remains unchanged
    public static function paginationData($data)
    {
        return [
            'meta' => [
                'current_page'  => $data->currentPage(),
                'from'          => $data->firstItem(),
                'last_page'     => $data->lastPage(),
                'path'          => $data->path(),
                'per_page'      => $data->perPage(),
                'to'            => $data->lastItem(),
                'total'         => $data->total(),
            ],
            'links' => [
                'first' => $data->url(1),
                'last'  => $data->url($data->lastPage()),
                'prev'  => $data->previousPageUrl(),
                'next'  => $data->nextPageUrl(),
            ],
        ];
    }

    public static function          getPaginates($collection)
    {
        return [
            'per_page' => $collection->perPage(),
            'path' => $collection->path(),
            'total' => $collection->total(),
            'current_page' => $collection->currentPage(),
            'next_page_url' => $collection->nextPageUrl(),
            'previous_page_url' => $collection->previousPageUrl(),
            'last_page' => $collection->lastPage(),
            'has_more_pages' => $collection->hasMorePages(),
            'from' => $collection->firstItem(),
            'to' => $collection->lastItem(),
        ];
    }
    /*

     public static function apiResponse(bool $success, $message, $data = null, $statusCode = null, $paginates = null)
     {

         if ($success == false && $statusCode == null) {
             $statusCode = 422;
         }

         if ($success == true && $statusCode == null) {
             $statusCode = 200;
         }



         $dataForPaginationCheck = $data;

         $isPagination = false;



         if ($data instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection) {

             $dataForPaginationCheck = $data->resource;


             $isPagination = true;

             if ($data instanceof LengthAwarePaginator ) {
                 $isPagination = true;

                 $data = $data->getCollection();
             }

             if ($dataForPaginationCheck instanceof \Illuminate\Support\Collection ) {
                 $isPagination = false;

 //                $data = $data;
             }
         }

         if ($data instanceof LengthAwarePaginator ) {
             $isPagination = true;

             $data = $data->getCollection();
         }

         return response()->json(
             [
                 'success'   => $success,
                 'message'   => __($message),
                 'data'      => $data,
                 'paginates' => $isPagination ? self::paginationData($dataForPaginationCheck) : null,
             ],
             $statusCode
         );
     }*/
    //    public static  function paginationData($data)
    //    {
    //        $result['meta'] =  [
    //            'current_page'  => $data->currentPage(),
    //            'from'          => $data->firstItem(),
    //            'last_page'     => $data->lastPage(),
    //            'path'          => $data->path(),
    //            'per_page'      => $data->perPage(),
    //            'to'            => $data->lastItem(),
    //            'total'         => $data->total(),
    //        ];
    //
    //        $result['links'] = [
    //            'first' => $data->url(1),
    //            'last'  => $data->url($data->lastPage()),
    //            'prev'  => $data->previousPageUrl(),
    //            'next'  => $data->nextPageUrl(),
    //        ];
    //
    //        return $result;
    //    }



    public static function getConf($key)
    {
        if ($conf = Config::query()->where('name', $key)->first()) {
            return $conf->value;
        }
        return null;
    }

    public static function getConfFromKey(array $keys)
    {
        $confs = Config::query()->whereIn('name', $keys)->select(['name', 'value'])->get();

        return $confs ?: null;
    }

    public static function upload($folder, $file)
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::random(10) . '.' . $extension;
        $file->storeAs($folder . DIRECTORY_SEPARATOR, $fileName, config('filesystems.default'));
        return $folder . DIRECTORY_SEPARATOR . $fileName;
    }

    public static function deleteImage($filePath)
    {
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
            return true;
        }
        return false;
    }


    public static function paginate($req, $data)
    {
        if ($req->pp) {
            return static::getPaginates($data);
        }
        return null;
    }

    // هل اتابعه

    public static function IsFollow($user_id = null, $followed_user_id = null)
    {
        if (!$user_id || !$followed_user_id) return 0;
        if ($user_id == $followed_user_id)   return 1;
        $id = Follow::query()->where('user_id', $user_id)->where('status', 1)->where('followed_user_id', $followed_user_id)->value('id');
        return $id ? 1 : 0;
    }



    public static function getConfig($name = null)
    {
        if (!$name) {
            return '';
        }
        $val = DB::table('configs')->where('name', $name)->value('value');
        return $val;
    }

    public static function timeZone()
    {
        $cacheKey = 'timezone';
        return \Cache::rememberForever($cacheKey, function () {
            $setting = \App\Models\Setting::where('key', 'timezone')->first();
            return $setting?->value ?? 'UTC';
        });
    }

    public static function gmOrderDataFormat($data, $type = 1)
    {
        if (!$data) {
            return [];
        }
        $f_yj_ratio = self::getConfig('f_yj_ratio');
        foreach ($data as $k => &$v) {
            //            $skill = $redisMod->getRedisData('skill', 'getSkillDetails', 18000, $v['skill_id']);
            //            $v['skill_img'] = isset($skill['image']) ? $skill['image'] :$this->auth->setFilePath($this->getConfig('logo'));
            //            $v['skill_name'] = isset($skill['name']) ? $skill['name'] :'暂无';
            if (in_array($type, [1, 3])) {
                $v->user_name = self::getUserField($v->master_id, 'nickname');
                $v->avatar = self::getUserField($v->master_id, 'avatar');
            } elseif ($type == 2) {
                $v->user_name = self::getUserField($v->user_id, 'nickname');
                $v->avatar = self::getUserField($v->user_id, 'avatar');
            }
            if ($v->status == 1) {
                $sysj = $v->addtime + 1200 - time();
                $v->sysj = $sysj > 0 ? $sysj : 0;
            }
            if ($type == 3) {
                $v->real_price = round($v->num * $v->price * $f_yj_ratio, 2);
            }
            $v->type = $type;
            $v->status_text = self::getGmOrdersText($v->status, $type);
            $v->start_time = $v->start_time ? date('Y.m.d H:i', $v->start_time) : '';
            $v->refusetime = $v->refusetime ? date('Y.m.d H:i', $v->refusetime) : '';
            $v->finishtime = $v->finishtime ? date('Y.m.d H:i', $v->finishtime) : '';
            $v->paytime = $v->paytime ? date('Y.m.d H:i', $v->paytime) : '';
            $v->addtime = $v->addtime ? date('Y.m.d H:i', $v->addtime) : '';
        }
        return $data;
    }


    //تصنيف حالة ترتيب اللعبة
    //type 1 users 2 master
    public static function getGmOrdersText($val = null, $type = 1)
    {
        $user = [
            1 => 'to be paid',
            2 => 'Pending orders',
            3 => 'to be served',
            31 => 'The other side applies for immediate service',
            4 => 'in progress',
            5 => 'Completed',
            6 => 'Cancelled',
            7 => 'Rejected',

            81 => 'refund application',
            82 => 'Refund successful',
            83 => 'Refund failed',
            84 => 'Appealing',
        ];

        $master = [
            1 => 'to be paid',
            2 => 'Pending orders',
            3 => 'to be served',
            31 => 'Applied for immediate service',
            4 => 'in progress',
            5 => 'Completed',
            6 => 'The other party has canceled',
            7 => 'Rejected',

            81 => 'refund application',
            82 => 'Agree to refund',
            83 => 'Refused to refund',
            84 => 'The other party is appealing',
        ];
        if ($type == 1) {
            return $val ? $user[$val] : $user;
        } elseif (in_array($type, [2, 3])) {
            return $val ? $master[$val] : $master;
        } else {
            return '';
        }
    }
    // public static function sendNotificationAndMessage($user_id,$tokens, $title, $body, $icon = '', $data = [], $action = '', $type = '', $id = '', $notification_type = 'user_notification', $titleAr = null)
    // {
    //     self::send_firebase_notification($tokens, $title, $body, $icon, $data, $action, $type, $id, $notification_type);
    //     self::sendOfficialMessage($user_id, $title, $body, $type, null, $titleAr);
    // }

    // public static function send_firebase_notification($tokens, $title, $body, $icon = '', $data = [], $messageType = null, $action = '', $type = '', $id = '', $notification_type = 'user_notification')
    // {

    //     $api_access_key =
    //         'AAAAYyrfZ8U:APA91bHcAaUhToEPWGpd_DfsUVv6aZLKttTDem_WF0rXJbHZMVERG9MP11G_TzcJKW_xnvzZx2R0t4Y-kCvCZn7UqfX8f6mJmVzTNAJ10lsMLMpje9AXdrCeSQ8l98H_sozao1vw9UeW';

    //     if (gettype($tokens) == 'string'){
    //         $tokens = [$tokens];
    //     }

    //     $notification = [
    //         'title'        => $title,
    //         'body'         => $body,
    //         'sound'        => 'tiknotifi',
    //         'visibility' => 'public',
    //         "alert" => true,

    //     ];

    //     $payload = [
    //         'registration_ids' => $tokens,
    //         'notification'     => $notification,
    //        // 'priority'         => 'high',
    //         'visibility' => 'private',
    //         //'sound'        => 'tiknotifi',
    //         'data' => [
    //             'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
    //             'message-type' => json_encode($messageType ?? ''),
    //             'data' => !empty($data) ? json_encode($data) : "",
    //         ],
    //     ];

    //     if (!empty($icon)) {
    //         $payload['notification']['icon'] = $icon;
    //     }

    //     if (isset($data['image']) && !empty($data['image'])) {
    //         $payload['notification']['image'] = $data['image'];
    //     } else {
    //         // $payload['notification']['image'] = 'https://kita.rstar-soft.com/storage/images/kitaimg.jpg';
    //     }

    //     $headers = [
    //         'Authorization: key=' . $api_access_key,
    //         'Content-Type: application/json',
    //     ];

    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST , 'POST');
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    //     $result = curl_exec($ch);
    //     curl_close($ch);

    //     return $result;


    // }

    private static function getGoogleAccessToken()
    {
        $credentialsFilePath = base_path(config("app.fileName"));

        // التحقق من وجود الملف
        if (!file_exists($credentialsFilePath)) {
            return;
        }

        $client = new \Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        return $token['access_token'];
    }

    public static function send_firebase_notification($tokens, $title, $body, $icon = '', $data = [], $messageType = null, $user = null, $action = '', $type = '', $id = '', $notification_type = 'user_notification')
    {
        if ($tokens == null) return;
        $api_access_key = self::getGoogleAccessToken();

        $isGroup = false;
        $userData = [];
        $key = time();

        if (gettype($tokens) == 'string') {
            $tokens = [$tokens];
        }

        $notification = [
            'title'        => $title,
            'body'         => $body,
            //            'sound'        => 'default',
        ];
        if (count($tokens) == 1) {
            $token = $tokens[0];
        } else {

            if ($tokens instanceof \Illuminate\Support\Collection) $tokens = $tokens->toArray();
            //make group and get token
            $token = self::makeGroup($tokens, $key,  $api_access_key);

            $isGroup = true;
        }

        if ($user) {
            $userData = [
                'user_id' => $user->id,
                'name' => $user->name,
                'uuid' => $user->uuid,
                'has_color_name'       => self::hasInPack($user->id, 18, true),
                'image' => $user->profile->avatar,
                // Any other user-specific data
            ];
        }

        $payload = [
            'token' => $token,
            'notification'     => $notification,
            //            'priority'         => 'high',
            'data' => [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'message-type' => json_encode($messageType ?? ''),
                'data' => !empty($data) ? json_encode($data) : "",
            ],
        ];

        //        if (!empty($icon)) {
        //            $payload['notification']['icon'] = $icon;
        //        }
        if (isset($userData) && is_array($userData)) {
            $payload['data']['user'] = json_encode($userData);
        }

        if (isset($data['image']) && !empty($data['image'])) {
            $payload['notification']['image'] = $data['image'];
        } else {
            // $payload['notification']['image'] = 'https://kita.rstar-soft.com/storage/images/kitaimg.jpg';
        }

        $headers = [
            'Authorization' => 'Bearer ' . $api_access_key,
            'Content-Type' => 'application/json',
        ];


        $projectName = app()->getLocale() == 'ar' ? Cache::get('app_title_ar') : Cache::get('app_title_en');
        $result = Http::withHeaders($headers)->post('https://fcm.googleapis.com/v1/projects/' . $projectName . '/messages:send', [
            'message' => $payload
        ]);

        $result = json_decode($result);


        //remove group with $key if is group
        if ($result  && $isGroup) {
            self::removeGroupName($key, $token, $tokens, $api_access_key);
        }
        return $result;
    }

    public static function makeGroup(array $registrationIds, string $notificationKeyName, $accessToken, string $operation = 'create')
    {
        $url = 'https://fcm.googleapis.com/fcm/notification';
        $senderId = config("app.senderId");

        if ($registrationIds == null) return;
        $headers = [
            'Content-Type: application/json',
            'access_token_auth: true',
            'Authorization: Bearer ' . $accessToken,
            'project_id: ' . $senderId,
        ];

        $payload = [
            'operation' => $operation,
            'notification_key_name' => $notificationKeyName,
            'registration_ids' => $registrationIds,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);
        if (!curl_errno($ch)) {

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode == 200) {
                $response = json_decode($response);
                return $response->notification_key;
            }
        }


        return null;
    }

    private static function removeGroupName($notificationKeyName, $token, $tokens, $accessToken)
    {
        $url = 'https://fcm.googleapis.com/fcm/notification';
        if ($token == null) return;
        $senderId = config("app.senderId");
        $payload = [
            'operation' => 'remove',
            'notification_key_name' => json_encode($notificationKeyName),
            'notification_key' => $token,
            'registration_ids' => $tokens
        ];

        $headers = [
            'Content-Type: application/json',
            'access_token_auth: true',
            'Authorization: Bearer ' . $accessToken,
            'project_id: ' . $senderId,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);
        if (!curl_errno($ch)) {

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode == 200) {
                $result = json_decode($result);
                return $result->notification_key;
            }
        }

        return $result;
    }


    public static function handelVip($vip, $user, $expire = null)
    {
        $wares = Ware::query()->where('get_type', 1)->where('enable', 1)->where('level', $vip->level)->where('is_active_for_vip', 1)->get();

        foreach ($wares as $ware) {
            Pack::query()->where('user_id', $user->id)
                ->where('expire', '<', now()->timestamp)
                ->where('expire', '!=', 0)
                ->delete();
            $pack =  Pack::query()
                ->where('user_id', $user->id)
                ->where('get_type', 1)
                ->where('target_id', $ware->id)
                ->where(function ($q) {
                    $q->where('expire', '>=', now()->timestamp)
                        ->orWhere('expire', 0);
                })->first();

            if ($expire == null) {
                $expire = $vip->expire;
            }
            if ($pack) {
                if ($pack->expire == 0) {
                    //                    throw new \Exception('already exists');
                } else {

                    $pack->expire = $vip->expire ? $pack->expire + ($expire * 86400) : 0;
                    $pack->save();
                }
            } else {
                Pack::query()->create(
                    [
                        'user_id' => $user->id,
                        'get_type' => $ware->get_type,
                        'type' => $ware->type,
                        'target_id' => $ware->id,
                        'num' => 1,
                        'expire' => $vip->expire ? now()->addDays($expire)->timestamp : 0,
                        'use_num' => $ware->num
                    ]
                );
            }
        }
        $uvip = UserVip::query()->where('user_id', $user->id)->where(function ($q) {
            $q->where("is_used", 1)->where(fn($q) => $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp));
        })->orderBy('level', 'desc')->first();
        if ($uvip) {
            $user->update(['vip' => $uvip->id]);
        }

        /* $users_vips = UserVip::with('OVip')->where('user_id',$user->id)->first();
        $preveliage = $users_vips->OVip->preveliage;
        $wareIds = Ware::where('type', $preveliage)->where('get_type',1)->where('is_active_for_vip', 1)->pluck('id')->toArray();
        $packs = Pack::where('user_id', $user->id)->whereIn('target_id', $wareIds)->get();
        $exception_packs = $packs->pluck('id')->toArray();
        Pack::where('user_id', $user->id)->whereNotIn('id', $exception_packs)->update(['is_used'=> 0]);
        Pack::whereIn('id', $exception_packs)->update(['is_used' => 1]); */
    }


    public static function setHourHot($uid)
    {
        $hot = GiftLog::query()->where('roomowner_id', $uid)
            ->where('created_at', '>', now()->subHour())
            ->selectRaw('SUM(giftPrice * giftNum) as total_gift_value')
            ->first();
        DB::table('rooms')->where('uid', $uid)->update(['hour_hot' => (int)$hot->total_gift_value]);
    }


    public static function sendSMS($phone, $message)
    {
        $account_sid = Common::getConf('twilio_sid');
        $auth_token = Common::getConf('twilio_api_key');
        $twilio_number = Common::getConf('twilio_from');
        $twilio_service = Common::getConf('twilio_service');
        try {
            $client = new TwilioClint($account_sid, $auth_token);
            if ($twilio_service) {
                $arr = [
                    //                    'from' => $twilio_number,
                    "messagingServiceSid" => $twilio_service,
                    'body' => $message
                ];
            } else {
                $arr = [
                    'from' => $twilio_number,
                    'body' => $message
                ];
            }
            return $client->messages->create(
                // Where to send a text message (your cell phone?)
                $phone,
                $arr
            );
        } catch (\Exception $exception) {
        }
    }

    public static function sendOfficialMessage($user_id, $content = '', $title = '', $type = 1, $sub_type = null, $titleAr = null, string $image = null, $fromUserId = null)
    {

        OfficialMessage::query()->create(
            [
                'title' => $title,
                'title_ar' => $titleAr,
                'user_id' => $user_id,
                'content' => $content,
                'sub_type' => @$sub_type,
                'type' => $type,
                'img' => $image,
                'from_user_id' => $fromUserId,
            ]
        );
    }

    public static function fireBaseFactory()
    {
        return (new Factory)
            ->withServiceAccount(public_path('firebase_credentials.json'))
            ->withDatabaseUri('https://yay-chat-c2333-default-rtdb.firebaseio.com');
    }

    public static function fireBaseDatabase($path, $obj, $type = 'set')
    {
        $factory = self::fireBaseFactory();
        $database = $factory->createDatabase();
        if ($type == 'set') {
            $database->getReference($path)->set($obj);
        } else {
            return $database->getReference($path)->getSnapshot()->getValue();
        }
    }

    public static function sendToZegoWithArrayOfRooms($Action, array $RoomIds, $FromUserId, $MessageContent, ?int $exceptRoomId = null, $IsTest = 'false')
    {
        $client = new Client();

        $url = 'https://rtc-api.zego.im';
        $AppId = self::getConf('zego_app_id');
        $SignatureNonce = self::getSignatureNonce();
        $Timestamp = time();
        $str = $AppId . $SignatureNonce . self::getConf('zego_server_secret') . $Timestamp;
        $signature = md5($str);
        $SignatureVersion = '2.0';
        $params = [
            'Action'           => $Action,
            'FromUserId'       => $FromUserId,
            'AppId'            => $AppId,
            'MessageContent'   => $MessageContent,
            'SignatureNonce'   => $SignatureNonce,
            'Timestamp'        => $Timestamp,
            'Signature'        => $signature,
            'SignatureVersion' => $SignatureVersion,
            'IsTest'           => $IsTest
        ];
        $promises = [];
        $headers = [];
        foreach ($RoomIds as $roomId) {
            if ($exceptRoomId && $roomId == $exceptRoomId) continue;
            $params['RoomId'] = $roomId;
            $promises[rand(111, 999)] = $client->getAsync($url, ['query' => $params]);
        }


        return $promises;
    }
    public static function handelFirebase($request, $type = 'follow')
    {
        $f_add = 0;
        $fr_add = 0;
        $vi_add = 0;
        $id = (int)$request->user_id;
        $snap = self::fireBaseDatabase($id, '', 'get');
        $followers_count = @(int)$snap['followers'] ?: 0;
        $followings_count = @(int)$snap['followings'] ?: 0;
        $friends_count = @(int)$snap['friends'] ?: 0;
        $visitors_count = @(int)$snap['visitors'] ?: 0;
        $path = $id;

        if ($type == 'follow') {
            if (in_array($request->user_id, $request->user()->followers_ids()->toArray())) {
                $fr_add = 1;
            }
            $f_add = 1;
        } elseif ($type == 'visit') {
            $vi_add = 1;
        }

        $obj = [
            'followers' => $followers_count + $f_add,
            'followings' => $followings_count,
            'friends' => $friends_count + $fr_add,
            'visitors' => $visitors_count + $vi_add
        ];

        self::fireBaseDatabase($path, $obj);





        $f_add = 0;
        $fr_add = 0;
        $vi_add = 0;
        $id = (int)$request->user()->id;
        $snap = self::fireBaseDatabase($id, '', 'get');
        $followers_count = @(int)$snap['followers'] ?: 0;
        $followings_count = @(int)$snap['followings'] ?: 0;
        $friends_count = @(int)$snap['friends'] ?: 0;
        $visitors_count = @(int)$snap['visitors'] ?: 0;
        $path = $id;

        if ($type == 'follow') {
            if (in_array($request->user_id, $request->user()->followers_ids()->toArray())) {
                $fr_add = 1;
            }
            $f_add = 1;
        }
        $obj = [
            'followers' => $followers_count,
            'followings' => $followings_count + $f_add,
            'friends' => $friends_count + $fr_add,
            'visitors' => $visitors_count + $vi_add
        ];

        self::fireBaseDatabase($path, $obj);
    }

    public static function hasInPack($user_id, $type, $use_status = false)
    {
        $ch =  self::checkPack($user_id, $type);
        if ($use_status) {
            $ch = $ch->where('is_used', 1);
        }

        return $ch->exists();
    }

    public static function hasProfileFramePack($user_id, $type, $use_status = false)
    {
        $ch =  self::checkPack($user_id, $type);
        if ($use_status) {
            $ch = $ch->where('is_used', 1);
        }
        $ch = $ch->first();
        return $ch->ware->img2 ?? '';
    }

    /*
      * 19 vip package
      * */
    public static function checkPackPrev($user_id, $type)
    {
        return Pack::query()->where('user_id', $user_id)->where('type', $type)->where(function ($q) {
            $q->where('expire', 0)->orWhere('expire', '>=', time());
        })->where('is_used', 1)->exists();
    }



    public static function AddUsdToHistoryForsUsers($user_id, $usd)
    {
        $month = date('m');
        $year = date('Y');
        $Agancy = User::where('id', $user_id)->first();
        $ownerPidTarget = Owner_pid_target::updateOrCreate(
            ['user_id' => $user_id, 'month' => $month, 'year' => $year, 'agency_id' => $Agancy->agency_id],
            ['total' => DB::raw("total + {$usd}")]
        );
        return $ownerPidTarget;
    }

    public static function AddUsdToHistoryForsOwners($user_id, $usd)
    {
        $month = date('m');
        $year = date('Y');
        $Agancy = User::where('id', $user_id)->first();
        $ownerPidTarget = Owner_pid_target::updateOrCreate(
            ['user_id' => $user_id, 'month' => $month, 'year' => $year, 'agency_id' => $Agancy->agency_id],
            ['totalowner' => DB::raw("totalowner + {$usd}")]
        );
        return $ownerPidTarget;
    }


    public static function CurantUsdHistoryOwner($user_id, $month = null, $year = null)
    {
        if ($month == null) {
            $month = date('m');
        }

        if ($year == null) {
            $year = date('Y');
        }

        $dataQuery = User::where('id', $user_id);

        $id = $dataQuery->first();
        $Agancy = Agency::where('id', @$id->agency_id)->first();
        $Qa = UserSallary::where('user_agency_id', @$Agancy->id)
            ->where('year', $year)
            ->where('month', $month);
        $target =  $Qa->sum('agency_sallary');
        $minValue = Target::where('usd', '<', $target)
            ->orderBy('usd', 'desc')
            ->first();

        if (@$Agancy->app_owner_id == $user_id) {
            if ($month = date('m') && $year = date('Y')) {

                $total = (@$minValue->agency_share / 100) * $target;   // v 1
                return $total;
            }
            $target->sum('sallary');

            return  $target;
        } else {
            //            $result =$Qa->first();
            $result = UserSallary::where('user_agency_id', @$Agancy->id)
                ->where('year', $year)
                ->where('month', $month)
                ->where("user_id", $user_id)
                ->first();
            return @$result->sallary ?? 0;
        }
    }

    public static function sendToZego3($Action, $RoomId, $FromUserId, $MessageContents = [], $IsTest = 'false')
    {



        try {
            $client           = new Client();
            $url              = 'https://rtc-api.zego.im';
            $AppId            = self::getConf('zego_app_id');
            $SignatureNonce   = self::getSignatureNonce();
            $Timestamp        = time();
            $str              = $AppId . $SignatureNonce . self::getConf('zego_server_secret') . $Timestamp;
            $signature        = md5($str);
            $SignatureVersion = '2.0';
            $params           = [
                'Action'           => $Action,
                'RoomId'           => $RoomId,
                'FromUserId'       => $FromUserId,
                'AppId'            => $AppId,
                'SignatureNonce'   => $SignatureNonce,
                'Timestamp'        => $Timestamp,
                'Signature'        => $signature,
                'SignatureVersion' => $SignatureVersion,
                'IsTest'           => $IsTest
            ];


            $promises         = [];
            $headers          = [];
            foreach ($MessageContents as $messageContent) {
                $params['MessageContent'] = $messageContent;
                $promises[rand(1, 999) . ''] = $client->getAsync($url, ['query' => $params]);
            }
            return $promises;
        } catch (\Exception $e) {
        }
    }



    public static function AgencyMangerCash($loggedInUserId = null)
    {
        if (!isset($loggedInUserId)) {
            $loggedInUserId = Admin::user()->app_id;
        }

        $fromconfig = Config::where('name', 'agency_manager_percentage')->first();
        $AgencyMangerPullingOut = AgencyMangerPullingOut::where('agency_manger_id', $loggedInUserId)->get()->pluck('amount')->sum();
        $pulling_out            = $AgencyMangerPullingOut ?? 0;

        $sum = Agency::where('agency_manger_id', $loggedInUserId)
            ->with('agencySalary') // Eager load the UserTarget relationship
            ->get()
            // ->pluck('UserTarget.*.agency_obtain')
            // ->flatten()
            ->sum('agencySalary.sallary');

        $result  = $sum * (intval($fromconfig->value) / 100);
        $curnt = $result - $pulling_out;
        if (is_float($curnt)) {
            $curnt = floor($curnt);
        }

        return $curnt;
    }

    public  static function totalTime($TotalHours)
    {
        $hoursInt = (int) $TotalHours;
        $hours   = $TotalHours;
        $minutes = ceil(((float)$TotalHours - $hoursInt) * 60);
        return sprintf('%02d:%02d:00', $hours, $minutes);
    }


    public  static function getImageTotalReceiverOrSender($amount)
    {
        $level = Vip::query()->where('level', $amount)->orderByDesc('exp')->first();
        return $level;
    }

    public static  function createUserAdmin($appOwnerId)
    {
        $user = User::find($appOwnerId);
        if (!$user) return true;
        $password = Str::random(8);
        $checkAccount = \App\Models\Admin::where('username', $user->uuid)->first();
        if ($checkAccount) return true;
        $admin = \App\Models\Admin::create([
            'username' => $user->uuid,
            'password' => Hash::make($password),
            'name' => $user->name,
        ]);
        $role = Role::where('slug', 'agency-owner')->first();
        DB::table('admin_role_users')->insert([
            'user_id' =>  $admin->id,
            'role_id' => $role->id,
        ]);
        if ($user->email != null) {
            Notification::route('mail',  $user->email)->notify(new AgencyOwnerRole($user->uuid, $password));
        }
        return true;
    }

    public static function userJoinAgency($originalOwnerId, $newOwnerId, $agencyId)
    {
        $agencyUserJoined = UsersJoinedAgency::where([
            'user_id' => $originalOwnerId,
            'agency_id' =>  $agencyId,
            'type' => 1,
        ])->where('leave_date', null)->first();
        $agencyUserJoined->leave_date = now();
        $agencyUserJoined->save();
        $checkAgencyUser = UsersJoinedAgency::where([
            'user_id' => $newOwnerId,
            'agency_id' =>  $agencyId,
            'type' => 1,
        ])->where('leave_date', null)->exists();
        if (!$checkAgencyUser) {
            UsersJoinedAgency::create([
                'user_id' => $newOwnerId,
                'agency_id' =>  $agencyId,
                'type' => 1,
                'join_date' => now(),
            ]);
        }
        return true;
    }



    public static function getNotificationContent(string $key, string $language = 'en', array $variables = []): array
    {
        $notificationData = Cache::rememberForever("notification_{$key}", function () use ($key) {
            $notification = \App\Models\Notification::with('translations')->where('key', $key)->first();
            return $notification ? $notification->translations->pluck('message', 'language')->toArray() : null;
        });

        if (!$notificationData) {
            return [
                'title' => __('Notification'),
                'body'  => __('No content available'),
            ];
        }

        $body = $notificationData[$language] ?? __('No translation available');

        foreach ($variables as $varKey => $value) {
            $body = str_replace("{{$varKey}}", '  ' . $value, $body);
        }

        return ['title' => __('Notification'), 'body' => $body];
    }

    public  static function getTargetUsd($diamonds, $percentage)
    {
        // $shipping_coins = Cache::rememberForever('shipping_coins', function () {
        //     return Setting::where('key', 'shipping_coins')->value('value') ?? 1;
        // }); 
        // $super_admin_coins = Cache::rememberForever('super_admin_coins', function () {
        //     return Setting::where('key', 'super_admin_coins')->value('value') ?? 1;
        // });
        $zones_coins = Cache::rememberForever('zones_coins', function () {
            return Setting::where('key', 'zones_coins')->value('value') ?? 1;
        });
        $coins = $zones_coins;
        // $coins = max($shipping_coins, $super_admin_coins, $zones_coins);
        $usd = $diamonds / $coins;
        $userUsd = $usd *  $percentage  / 100;

        return $userUsd;
    }

    public  static function getMaxCoins()
    {
        // $shipping_coins = Cache::rememberForever('shipping_coins', function () {
        //     return Setting::where('key', 'shipping_coins')->value('value') ?? 1;
        // }); 
        // $super_admin_coins = Cache::rememberForever('super_admin_coins', function () {
        //     return Setting::where('key', 'super_admin_coins')->value('value') ?? 1;
        // });
        $zones_coins = Cache::rememberForever('zones_coins', function () {
            return Setting::where('key', 'zones_coins')->value('value') ?? 1;
        });

        // $coins = max($shipping_coins, $super_admin_coins, $zones_coins);


        return $zones_coins;
    }

    public  static function getCoinsValue($key)
    {
        $value = Cache::rememberForever($key, function () use ($key) {
            return Setting::where('key', $key)->value('value') ?? 1;
        });
        return $value;
    }
}
