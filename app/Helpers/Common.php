<?php

namespace App\Helpers;

use App\Classes\Facades\Agency as FacadesAgency;
use App\Enums\Charges\UserTypeEnum;
use App\Facades\UserHandling;
use App\Jobs\SendFirebaseNotificationJob;
use App\Models\Agency;
use App\Models\AgencyMangerPullingOut;
use App\Models\Background;
use App\Models\Ban;
use App\Models\ChargeWinner;
use App\Models\Config;
use App\Models\Follow;
use App\Models\GameProviderSetting;
use App\Models\GiftLog;
use App\Models\OfficialMessage;
use App\Models\Owner_pid_target;
use App\Models\Pack;
use App\Models\PackLog;
use App\Models\Pk;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomVisitor;
use App\Models\Setting;
use App\Models\ShippingAgency;
use App\Models\Target;
use App\Models\User;
use App\Models\UserCoinLog;
use App\Models\UserSallary;
use App\Models\UsersJoinedAgency;
use App\Models\Ware;
use App\Traits\HelperTraits\AdminTrait;
use App\Traits\HelperTraits\AttributesTrait;
use App\Traits\HelperTraits\CalcsTrait;
use App\Traits\HelperTraits\FilterTrait;
use App\Traits\HelperTraits\InfoTrait;
use App\Traits\HelperTraits\MoneyTrait;
use App\Traits\HelperTraits\RoomTrait;
use App\Traits\HelperTraits\ZegoTrait;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Show;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Modules\AreaManager\Entities\AreaManager;
use Modules\AreaManager\Entities\SubAreaManager;
use Modules\Badge\Entities\Badge;
use Modules\Badge\Entities\UserBadge;
use Modules\Charizma\Http\Services\UserCharismaService;
use Modules\CP\Entities\WeeklyCpWinner;
use Modules\Events\Entities\PkEvent;
use Modules\Events\Entities\PkWinner;
use Modules\Events\Entities\WeeklyStar;
use Modules\Events\Entities\Winner;
use Modules\Vip\Entities\UserVip;
use Modules\Vip\Entities\Vip;
use stdClass;
use Twilio\Rest\Client as TwilioClint;

class Common
{

    use CalcsTrait, AdminTrait, MoneyTrait, RoomTrait, AttributesTrait, ZegoTrait, InfoTrait, FilterTrait;

    public static function getCachedWares($cacheKey, $vip, $type)
    {
        return Cache::remember($cacheKey, 60, function () use ($vip, $type) {
            return Ware::where('level', $vip->level)
                ->where('type', $type)
                ->where('get_type', 1)
                ->first();
        });
    }

    public static function switch_events($event_type)
    {

        $avatar = null;
        $avatarCp2 = null;
        $nameCpOne = '';
        $nameCpTwo = '';

        if ($event_type == 'pk_event') {

            $event = PkEvent::PreviousEvent()->latest()->first();

            if ($event) {
                $pk_winner = PkWinner::with('user')->where('pk_event_id', $event->id)
                    ->where('pk_type', 'pk-star')
                    ->where('level', 1)
                    ->first();

                if ($pk_winner) {
                    $avatar = @$pk_winner?->user?->profile?->avatar;
                }
            }
        } else if ($event_type == 'weekly_star') {
            $event = WeeklyStar::weeklyStar()->previousEvent()->first();

            if ($event) {
                $weekly_star = Winner::with('user')->where('weekly_star_id', $event->id)
                    ->where('level', 1)
                    ->first();

                if ($weekly_star) {
                    $avatar = @$weekly_star->user->profile->avatar;
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
                $avatar = @$charge->user->profile->avatar;
            }
        } else if ($event_type == 'weekly_cp') {
            $event = WeeklyStar::WeeklyCP()->previousEvent()->first();
            if ($event) {
                $weekly_star = WeeklyCpWinner::with('userTwo', 'userOne')->where('weekly_cp_id', $event->id)
                    ->where('level', 1)->first();
                if ($weekly_star) {
                    $avatar = @$weekly_star->userOne->profile->avatar;
                    $avatarCp2 = @$weekly_star->userTwo->profile->avatar;
                    $nameCpTwo = @$weekly_star->userTwo->name;
                    $nameCpOne = @$weekly_star->userOne->name;
                }
            }
        }

        return [$avatar, $avatarCp2, $nameCpOne, $nameCpTwo];
    }

    public static function userVipLevel($userId, $level)
    {
        return UserVip::where(['user_id' => $userId])->where('level', ">=", $level)->active()->exists();
    }

    public static function level_center_min($user_id)
    {
        $user = User::query()->find($user_id);
        if (!$user) {
            return new stdClass();
        }

        $star_level = $user->received_level + $user->sub_receiver_level;
        $firstVip = Vip::where('level', $star_level)->where('type', 1)->first();
        $data['receiver_img'] = !is_null($firstVip) ? $firstVip->img : '';

        $gold_level = $user->sender_level + $user->sub_sender_level;
        $firstVip_type2 = Vip::where('level', $gold_level)->where('type', 2)->first();
        $data['sender_img'] = !is_null($firstVip_type2) ? $firstVip_type2->img : '';

        return $data;
    }

    public static function level_center_min_v2($user_id)
    {
        if (gettype($user_id) == 'integer') {
            $user = User::query()->find($user_id);
            if (!$user) return new stdClass();
        } else {
            $user = $user_id;
        }

        if (!$user) {
            return [
                'receiver_img' => '',
                'sender_img' => ''
            ];
        }

        $star_level = $user->received_level + $user->sub_receiver_level;
        $firstVip = Vip::collectionBuilder()->where('level', $star_level)->where('type', 1)->first();
        $data['receiver_img'] = !is_null($firstVip) ? $firstVip->img : '';

        $gold_level = $user->sender_level + $user->sub_sender_level;
        $firstVip_type2 = Vip::collectionBuilder()->where('level', $gold_level)->where('type', 2)->first();
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
        $resourceData = null;
        $paginationData = null;

        // Check if data is a collection directly or a paginated resource
        if ($paginationKey === null) {
            if ($data instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection) {
                $resourceData = $data->resource;

                if (
                    $resourceData instanceof LengthAwarePaginator ||
                    $resourceData instanceof Paginator ||
                    $resourceData instanceof CursorPaginator
                ) {
                    $paginationData = self::paginationData($resourceData);
                    $data = $resourceData->getCollection();
                }
            } elseif (
                $resourceData instanceof LengthAwarePaginator ||
                $resourceData instanceof Paginator ||
                $resourceData instanceof CursorPaginator
            ) {
                $paginationData = self::paginationData($data);
                $data = $data->getCollection();
            }
        }
        // Check if data contains the pagination key and it's paginated
        elseif (isset($data[$paginationKey])) {
            $dataForPagination = $data[$paginationKey];

            if ($dataForPagination instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection) {
                $resourceData = $dataForPagination->resource;

                if (
                    $resourceData instanceof LengthAwarePaginator ||
                    $resourceData instanceof Paginator ||
                    $resourceData instanceof CursorPaginator
                ) {
                    $paginationData = self::paginationData($resourceData);
                    $data[$paginationKey] = $dataForPagination->getCollection();
                }
            } elseif (
                $resourceData instanceof LengthAwarePaginator ||
                $resourceData instanceof Paginator ||
                $resourceData instanceof CursorPaginator
            ) {
                $paginationData = self::paginationData($dataForPagination);
                $data[$paginationKey] = $dataForPagination->getCollection();
            }
        }
        if ($paginates) {

            foreach ($paginates as $paginationKey => $paginationCollection) {
                if (
                    $paginationCollection instanceof LengthAwarePaginator ||
                    $paginationCollection instanceof Paginator ||
                    $paginationCollection instanceof CursorPaginator
                ) {

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
    public static function paginationData($data): ?array
    {
        if ($data instanceof LengthAwarePaginator) {
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
                ]
            ];
        }

        if ($data instanceof Paginator) {
            return [
                'meta' => [
                    'current_page'  => $data->currentPage(),
                    'from'          => $data->firstItem(),
                    'path'          => $data->path(),
                    'per_page'      => $data->perPage(),
                    'to'            => $data->lastItem(),
                    'last_page'     => null,
                    'total'         => null,
                ],
                'links' => [
                    'first' => null,
                    'last'  => null,
                    'prev'  => $data->previousPageUrl(),
                    'next'  => $data->nextPageUrl(),
                ]
            ];
        }

        if ($data instanceof CursorPaginator) {
            return [
                'meta' => [
                    'per_page'      => $data->perPage(),
                    'path'          => $data->path(),
                    'next_cursor'   => optional($data->nextCursor())->encode(),
                    'prev_cursor'   => optional($data->previousCursor())->encode(),
                ],
                'links' => [
                    'prev'  => $data->previousPageUrl(),
                    'next'  => $data->nextPageUrl(),
                ]
            ];
        }

        return null;
    }

    public static function getPaginates($collection)
    {
        $isLengthAware = $collection instanceof LengthAwarePaginator;

        return [
            'per_page' => $collection->perPage(),
            'path' => $collection->path(),
            'total' =>  $isLengthAware ? $collection->total() : null,
            'current_page' => $collection->currentPage(),
            'next_page_url' => $collection->nextPageUrl(),
            'previous_page_url' => $collection->previousPageUrl(),
            'last_page' =>  $isLengthAware ? $collection->lastPage() : null,
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
        if ($key === 'enable_vip_auto') {
            return "true";
        }

        $configs = Cache::rememberForever('all_configs', function () {
            return Config::query()->pluck('value', 'name')->toArray();
        });

        return $configs[$key] ?? null;
    }

    public static function getSettingValue($key)
    {
        $value = Cache::rememberForever($key, function () use ($key) {
            return Setting::where('key', $key)->value('value');
        });
        return $value ?? null;
    }

    public static function getConfFromKey(array $keys)
    {
        $confs = Config::query()->whereIn('name', $keys)->select(['name', 'value'])->get();

        return $confs ?: null;
    }

    public static function upload($folder, $file, $disk = null)
    {
        // Validate file is uploaded file instance
        if (!($file instanceof \Illuminate\Http\UploadedFile)) {
            throw new \Exception('Invalid file upload');
        }

        // Check if file is valid
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        // Security: Validate file size (10MB max)
        $maxSize = 10485760; // 10MB
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds maximum allowed size of 10MB');
        }

        // Security: Validate MIME type (images only)
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, $allowedMimes)) {
            throw new \Exception('Invalid file type. Only images (JPG, PNG, GIF, WebP) are allowed');
        }

        // Security: Map MIME type to safe extension (don't trust client extension)
        $safeExtension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => throw new \Exception('Unsupported image format'),
        };

        // Log suspicious activity if client extension doesn't match
        $clientExtension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if ($clientExtension && !in_array($clientExtension, $allowedExtensions)) {
            \Log::warning('Suspicious file extension detected', [
                'client_extension' => $clientExtension,
                'mime_type' => $mimeType,
                'safe_extension' => $safeExtension,
                'ip' => request()->ip()
            ]);
        }

        // Generate secure filename using hash
        $hash = hash('sha256', Str::random(40) . microtime(true));
        $fileName = substr($hash, 0, 32) . '.' . $safeExtension;

        // Store file
        $config = $disk ?: config('filesystems.default');
        $file->storeAs($folder . DIRECTORY_SEPARATOR, $fileName, $config);

        return $folder . DIRECTORY_SEPARATOR . $fileName;
    }




    public static function uploadProfileUser($folder, $file, $id, $count)
    {
        // Validate file is uploaded file instance
        if (!($file instanceof \Illuminate\Http\UploadedFile)) {
            throw new \Exception('Invalid file upload');
        }

        // Check if file is valid
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        // Security: Validate file size (10MB max)
        $maxSize = 10485760; // 10MB
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds maximum allowed size of 10MB');
        }

        // Security: Validate MIME type (images only)
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, $allowedMimes)) {
            throw new \Exception('Invalid file type. Only images (JPG, PNG, GIF, WebP) are allowed');
        }

        // Security: Map MIME type to safe extension (don't trust client extension)
        $safeExtension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => throw new \Exception('Unsupported image format'),
        };

        // Log suspicious activity if client extension doesn't match
        $clientExtension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if ($clientExtension && !in_array($clientExtension, $allowedExtensions)) {
            \Log::warning('Suspicious file extension detected in profile upload', [
                'client_extension' => $clientExtension,
                'mime_type' => $mimeType,
                'safe_extension' => $safeExtension,
                'user_id' => $id,
                'ip' => request()->ip()
            ]);
        }

        // Generate filename with user ID and safe extension
        $fileName = $id . '_' . $count . '.' . $safeExtension;

        // Store file
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

        if ($name === 'enable_vip_auto') {
            return "true";
        }

        $configs = Cache::get('all_configs');

        return $configs[$name] ?? null;
    }

    public static function timeZone()
    {
        return Cache::rememberForever('timezone', function () {
            $setting = Setting::where('key', 'timezone')->first();
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
    public static function getPublicGoogleAccessToken()
    {
        $credentialsFilePath = base_path(config("app.fileName"));

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
    private static function getGoogleAccessToken()
    {
        return \Cache::remember('firebase_google_access_token', 3500, function () {
            $credentialsFilePath = base_path(config("app.fileName"));

            if (!file_exists($credentialsFilePath)) {
                return null;
            }

            $client = new \Google_Client();
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $token = $client->getAccessToken();

            return $token['access_token'];
        });
    }
    private static function getUnsubscribeGoogleAccessToken(): ?string
    {
        $credentialsFilePath = base_path(config("app.fileName"));

        if (!file_exists($credentialsFilePath)) {
            return null;
        }

        try {
            $client = new \Google_Client();
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->useApplicationDefaultCredentials();
            $token = $client->fetchAccessTokenWithAssertion();

            if (!isset($token['access_token'])) {
                return null;
            }

            return $token['access_token'];
        } catch (\Throwable $e) {
            return null;
        }
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

            SendFirebaseNotificationJob::dispatch(
                tokens: $tokens,
                title: $title,
                body: $body,
                data: $data,
                messageType: $messageType,
                user: $user,
                action: $action,
                type: $type,
                id: $id,
                notification_type: $notification_type,
            )->onQueue('notification_heavy');

            return  true;
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

        //        info('icon', [$icon]);
        //        if ($icon) {
        //            $payload['notification']['icon'] = $icon;
        //        }
        if (isset($userData) && is_array($userData)) {
            $payload['data']['user'] = json_encode($userData);
        }

        if (isset($data['image']) && !empty($data['image'])) {
            $payload['notification']['image'] = $data['image'];
            $payload['android']['notification']['image'] = $data['image'];
        } else {
            $payload['notification']['image'] = 'https://kita.rstar-soft.com/storage/images/kitaimg.jpg';
            $payload['android']['notification']['image'] = 'https://kita.rstar-soft.com/storage/images/kitaimg.jpg';
        }

        $headers = [
            'Authorization' => 'Bearer ' . $api_access_key,
            'Content-Type' => 'application/json',
        ];


        $projectId = env('FIREBASE_PROJECT_NAME');

        $result = Http::withHeaders($headers)->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
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

    public static function send_firebase_notification_top($tokens, $title, $body, $icon = '', $data = [], $messageType = null, $user = null, $action = '', $type = '', $id = '', $notification_type = 'user_notification')
    {
        if (empty($tokens)) return;

        if (!is_array($tokens)) {
            $tokens = [$tokens];
        }
        $topicName = 'temp_topic_' . uniqid();

        self::subscribeToTopic($tokens, $topicName);

        $userData = [];
        if ($user) {
            $userData = [
                'user_id' => $user->id,
                'name' => $user->name,
                'uuid' => $user->uuid,
                'has_color_name' => self::hasInPack($user->id, 18, true),
                'image' => $user->profile->avatar,
            ];
        }

        $api_access_key = self::getGoogleAccessToken();
        $projectId = env('FIREBASE_PROJECT_NAME');

        $payload = [
            'message' => [
                'topic' => $topicName,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => [
                    'click_action'       => 'FLUTTER_NOTIFICATION_CLICK',
                    'message-type'       => (string) ($messageType ?? ''),
                    'action'             => $action,
                    'type'               => $type,
                    'id'                 => $id,
                    'notification_type'  => $notification_type,
                    'data'               => !empty($data) ? json_encode($data) : "",
                    'user'               => json_encode($userData),
                ]
            ]
        ];
        sleep(5);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $api_access_key,
            'Content-Type' => 'application/json',
        ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);


        $status = $response->status();
        $body = $response->body();


        return json_decode($response->body());
    }


    public static function subscribeToTopic(array $registrationTokens, string $topic)
    {
        $factory = (new Factory)->withServiceAccount(base_path(config('app.fileName')));
        $messaging = $factory->createMessaging();


        $result = $messaging->subscribeToTopic($topic, $registrationTokens);


        //            'topic' => $topic,
        //            'result' => $result,
        //        ]);

        return $result;
    }

    public static function unsubscribeFromTopic(array $registrationTokens, string $topic)
    {
        try {
            $factory = (new Factory)->withServiceAccount(base_path(config("app.fileName")));
            $messaging = $factory->createMessaging();


            $response = $messaging->unsubscribeFromTopic($topic, $registrationTokens);


            //                'topic'          => $topic,
            //                'tokensCount'    => count($registrationTokens),
            //                'response'       => $response,
            //            ]);

            // تحليل النتائج (اختياري)
            $result = $response[$topic->value()] ?? [];
            $successCount = 0;
            $failureCount = 0;

            foreach ($result as $token => $status) {
                if ($status === 'OK') {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            }

            return [
                'success' => true,
                'successCount' => $successCount,
                'failureCount' => $failureCount,
                'details' => $result
            ];
        } catch (\Throwable $e) {
            //            logger()->error('❌ Unsubscribe Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
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

    /**
     * Send Firebase notification for room sharing with image support
     */
    public static function send_firebase_notification_with_room_image($tokens, $title, $body, $roomImage = '', $roomId = null, $data = [], $messageType = 'share-room', $user = null)
    {
        try {
            if ($tokens == null) {
                return false;
            }

            $api_access_key = self::getGoogleAccessToken();
            if (!$api_access_key) {
                return false;
            }

            $isGroup = false;
            $userData = [];
            $key = time();

            if (gettype($tokens) == 'string') {
                $tokens = [$tokens];
            }

            $notification = [
                'title' => $title,
                'body' => $body,
            ];

            if (count($tokens) == 1) {
                $token = $tokens[0];
            } else {
                if ($tokens instanceof \Illuminate\Support\Collection) $tokens = $tokens->toArray();



                SendFirebaseNotificationJob::dispatch(
                    tokens: $tokens,
                    title: $title,
                    body: $body,
                    data: array_merge($data, [
                        'room_id' => $roomId,
                        'room_image' => $roomImage,
                        'image' => $roomImage
                    ]),
                    messageType: $messageType,
                    user: $user,
                )->onQueue('notification_heavy');

                return true;
            }

            if ($user) {
                $userData = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'uuid' => $user->uuid,
                    'has_color_name' => self::hasInPack($user->id, 18, true),
                    'image' => $user->profile->avatar,
                ];
            }

            // Merge room data with existing data
            $mergedData = array_merge($data, [
                'room_id' => $roomId,
                'room_image' => $roomImage
            ]);

            $payload = [
                'token' => $token,
                'notification' => $notification,
                'data' => [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'message-type' => json_encode($messageType ?? ''),
                    'data' => json_encode($mergedData),
                ],
            ];

            if (isset($userData) && is_array($userData)) {
                $payload['data']['user'] = json_encode($userData);
            }

            // Add room image to notification
            if ($roomImage && !empty($roomImage)) {
                $payload['notification']['image'] = $roomImage;
            } else {
                $payload['notification']['image'] = 'https://kita.rstar-soft.com/storage/images/kitaimg.jpg';
                // Log::warning('send_firebase_notification_with_room_image: Using default image', [
                //     'room_image_was' => $roomImage
                // ]);
            }

            $headers = [
                'Authorization' => 'Bearer ' . $api_access_key,
                'Content-Type' => 'application/json',
            ];

            $projectId = env('FIREBASE_PROJECT_NAME');

            $result = Http::withHeaders($headers)->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => $payload
            ]);

            $resultDecoded = json_decode($result->body());

            if ($result->successful()) {
            } else {
            }

            // Remove group with $key if is group
            if ($resultDecoded && $isGroup) {
                self::removeGroupName($key, $token, $tokens, $api_access_key);
            }

            return $resultDecoded;
        } catch (\Throwable $e) {
            // Log::error('send_firebase_notification_with_room_image: Exception occurred', [
            //     'room_id' => $roomId,
            //     'error' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString()
            // ]);
            return false;
        }
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
        $userIds = is_array($user_id) ? $user_id : [$user_id];

        $data = [];

        foreach ($userIds as $id) {
            $data[] = [
                'title'        => $title,
                'title_ar'     => $titleAr,
                'user_id'      => $id,
                'content'      => $content,
                'sub_type'     => $sub_type,
                'type'         => $type,
                'img'          => $image,
                'from_user_id' => $fromUserId,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];

            //                'id' => $id,
            //            ]);
        }

        if (!empty($data)) {
            OfficialMessage::insert($data);

            //                'user_ids' => $userIds,
            //            ]);
        }

        //        logger()->warning('[sendOfficialMessage] No valid user IDs to insert message.');


        // OfficialMessage::query()->create(
        //     [
        //         'title' => $title,
        //         'title_ar' => $titleAr,
        //         'user_id' => $user_id,
        //         'content' => $content,
        //         'sub_type' => @$sub_type,
        //         'type' => $type,
        //         'img' => $image,
        //         'from_user_id' => $fromUserId,
        //     ]
        // );
    }

    public static function fireBaseFactory()
    {
        return (new Factory)
            ->withServiceAccount(storage_path('app/credentials/firebase_credentials.json'))
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
        // $AppId = self::getConf('zego_app_id');
        $AppId = self::zegoData('zego_app_id');
        $SignatureNonce = self::getSignatureNonce();
        $Timestamp = time();
        //   $str = $AppId . $SignatureNonce . self::getConf('zego_server_secret') . $Timestamp;
        $str = $AppId . $SignatureNonce . self::zegoData('zego_server_secret') . $Timestamp;

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
    public static function hasColorInPack($user_id, $type, $use_status = false)
    {
        $query = Pack::query()
            ->with('ware')
            ->where('user_id', $user_id)
            ->where('type', $type)
            ->where(function ($q) {
                $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
            });

        if ($use_status) {
            $query->where('is_used', 1);
        }

        $pack = $query->first();

        return $pack?->ware?->color ?? '';
    }

    public static function hasColorInPackV2($userPacks, $type, $use_status = false)
    {
        $ch = self::checkPackV2($userPacks, $type);

        if ($use_status) {
            $ch = $ch->where('is_used', 1);
        }

        $pack = $ch->first();

        return $pack?->ware?->color ?? '';
    }

    public static function hasInPackV2($userPacks, $type, $use_status = false)
    {
        $ch =  self::checkPackV2($userPacks, $type);
        if ($use_status) {
            $ch = $ch->where('is_used', 1);
        }

        return $ch->isNotEmpty();
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

    public static function checkUserPacks($packs, $type)
    {
        return $packs->where('type', $type)
            ->where('is_used', 1)
            ->filter(function ($item) {
                return $item->expire == 0 || $item->expire >= time();
            });
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
            //   $AppId            = self::getConf('zego_app_id');
            $AppId            = self::zegoData('zego_app_id');
            $SignatureNonce   = self::getSignatureNonce();
            $Timestamp        = time();
            //  $str              = $AppId . $SignatureNonce . self::getConf('zego_server_secret') . $Timestamp;
            $str              = $AppId . $SignatureNonce . self::zegoData('zego_server_secret') . $Timestamp;
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
            //  Log::error($e->getMessage());
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
        $level = Vip::collectionBuilder()->where('level', $amount)->orderByDesc('exp')->first();
        return $level;
    }
    public static function zegoData($key = null)
    {
        $zegoClientId =  Common::getConf('zego_key');
        $zego_token = Common::getConf('zego_token');
        $sounZego = Common::getConf('sound_library');
        $vedioZego = Common::getConf('video_library');

        $zego_server_secret = Common::getConfig('zego_server_secret');
        $zego_app_id = Common::getConfig('zego_app_id');
        $app_sign = Common::getConfig('app_sign');


        if ((!$zego_token && !$zegoClientId) && ($sounZego === '3' && $vedioZego === '3')) {
            $zegoData = [
                'zego_app_id'        =>  '',
                'zego_server_secret' =>  '',
                'zego_app_sign'      =>  '',
            ];
        }

        if ($sounZego == '1' || $vedioZego == '1') {

            $zegoData = [
                'zego_app_id'        =>  $zego_app_id ?? '',
                'zego_server_secret' => $zego_server_secret ?? '',
                'zego_app_sign'      =>  $app_sign ?? '',
            ];
            // dd($zegoData);
        } else {
            $data = decryptToArray($zego_token, $zegoClientId);
            $zegoData = [
                'zego_app_id'        => $data['app_id'] ?? '',
                'zego_server_secret' => $data['server_secret'] ?? '',
                'zego_app_sign'      => $data['app_sign'] ?? '',
            ];
        }


        // If a key is provided, return that specific value
        if ($key) {
            return $zegoData[$key] ?? null;
        }

        // Otherwise return all values
        return $zegoData;
    }
    public static  function createUserAdmin($appOwnerId)
    {
        if (!$appOwnerId) return;
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
        if (!$role) {
            Role::create([
                'slug' => 'agency-owner',
                'name' => 'agency owner',
            ]);
        }
        $role = Role::where('slug', 'agency-owner')->first();
        if ($admin && $role) {
            DB::table('admin_role_users')->insert([
                'user_id' => $admin->id,
                'role_id' => $role->id,
            ]);
        }
        // if ($user->email != null) {
        //     Notification::route('mail',  $user->email)->notify(new AgencyOwnerRole($user->uuid, $password));
        // }
        return true;
    }

    public static function userJoinAgency($originalOwnerId, $newOwnerId, $agencyId)
    {
        $agencyUserJoined = UsersJoinedAgency::where([
            'user_id' => $originalOwnerId,
            'agency_id' =>  $agencyId,
            'type' => 2,
        ])->where('leave_date', null)->first();
        if ($agencyUserJoined) {
            $agencyUserJoined->leave_date = now();
            $agencyUserJoined->status = 'from admin';
            $agencyUserJoined->save();
        }
        $checkAgencyUser = UsersJoinedAgency::where([
            'user_id' => $newOwnerId,
            'agency_id' =>  $agencyId,
            'type' => 2,
        ])->where('leave_date', null)->exists();
        if (!$checkAgencyUser) {
            UsersJoinedAgency::create([
                'user_id' => $newOwnerId,
                'agency_id' =>  $agencyId,
                'type' => 2,
                'join_date' => now(),
                'status' => 'Joined'
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
        //$convertDiamond =  Common::getSettingValue('convert_diamonds') ?? 'zones_coins';
        //$coins = Common::getSettingValue($convertDiamond) ?? 1;

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

        $usd = $diamonds / $coins;
        $userUsd = $usd *  $percentage  / 100;
        $usd = Common::roundToTwoDecimalPlaces($userUsd);

        return $usd;
    }


    public static function getByCode($code)
    {
        return  Cache::remember(
            'game_provider_' . $code,
            now()->addHours(2),
            function () use ($code) {
                return GameProviderSetting::where('provider_code', $code)->first();
            }
        );
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

        $coins = $zones_coins;

        return $coins;
    }

    public  static function getCoinsValue($key)
    {
        $value = Cache::rememberForever($key, function () use ($key) {
            return Setting::where('key', $key)->value('value') ?? 1;
        });
        return $value;
    }

    public static function getSettingsValue($key, $forceRefresh = true)
    {
        if ($forceRefresh) {
            Cache::forget($key);
        }
        $value = Cache::rememberForever($key, function () use ($key) {
            return Setting::where('key', $key)->value('value');
        });
        return $value ?? null;
    }

    public  static function getDiamondsPercentage()
    {
        $num = (int)self::getSettingsValue('diamonds');
        $per = $num / 100;
        return $per;
    }


    public static function ifRoomHasband($owner_id, $roomType)
    {
        $room = Room::where('uid', $owner_id)->where('type', $roomType)->first();

        if ($room) {
            $ban = $room->bans()
                ->whereRaw("created_at + INTERVAL duration HOUR > ?", [now()])
                ->first();
            return $ban ? true : false;
        }
        return false;
    }


    public static function banDuration($owner_id, $roomType)
    {
        $room = Room::where('uid', $owner_id)->where('type', $roomType)->first();
        $deuration = 0;
        $remaining = 0;

        if ($room) {
            $ban = $room->bans()
                ->whereRaw("created_at + INTERVAL duration HOUR > ?", [now()])
                ->first();

            $deuration = $ban->duration;
            $remaining = self::remaining($ban);
        }

        return [$deuration, $remaining];
    }

    public static function remaining($ban)
    {
        $timezone = getTimezone();

        // Get raw UTC datetime
        $createdAt = \Carbon\Carbon::parse($ban->getAttributes()['created_at'], 'UTC');

        // Add ban duration and convert to user's timezone
        $banExpiration = $createdAt->addHours($ban->duration)->setTimezone($timezone);

        $now = now($timezone);

        // Get total remaining minutes
        $diffInMinutes = $now->diffInMinutes($banExpiration, false);

        if ($diffInMinutes <= 0) {
            return 'منتهي'; // Expired
        }

        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        if ($hours >= 1) {
            return "{$hours}h:{$minutes}m";
        } else {
            return "{$minutes}" . ' ' . __('minute');
        }
    }


    public static function roundToTwoDecimalPlaces($number)
    {
        return floor($number * 100) / 100;
    }

    public static function searchAgency($id)
    {
        $agency = ShippingAgency::find($id);
        return $agency ?? 0;
    }



    public static function renderWalletMessage(string $messageKey, string|array|null $messageData): string
    {
        $messageData = is_string($messageData) ? json_decode($messageData, true) ?? [] : $messageData;
        $fullKey = 'messages.' . $messageKey;

        if (str_contains($messageKey, 'user')) {
            $userId = $messageData['receiver_id'] ?? $messageData['user_id'] ?? null;
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $messageData['name'] = $user->name;
                    $messageData['target'] = $user->name;
                }
            }
        }

        if (str_contains($messageKey, 'agency')) {
            $agencyId = $messageData['agency_id'] ?? null;
            if ($agencyId) {
                $agency = \App\Models\Agency::find($agencyId);
                if ($agency) {
                    $messageData['name'] = $agency->name;
                    $messageData['target'] = $agency->name;
                }
            }
        }

        $messageData['name'] = $messageData['name'] ?? $messageData['target'] ?? __('unknown');
        $messageData['target'] = $messageData['target'] ?? $messageData['name'] ?? __('unknown');

        return __($fullKey, $messageData);
    }


    public static function kickOfAllUsersRoom(\App\Models\Room $room)
    {
        $usersIdInRooms = RoomVisitor::query()->where(['room_id' => $room->id])->pluck('user_id')->toArray();
        User::whereIn('id', $usersIdInRooms)->update(['now_room_uid' => 0]);
        RoomVisitor::query()->where(['room_id' => $room->id])->delete();

        //reset carisma
        if ($room->charizma_status) self::handleCharismaStatusOnLogout($room, $usersIdInRooms, $room->uid);

        //leave mic

        //        foreach ($usersIdInRooms as $userId) {
        //            if (isset($room->microphone)) {
        //
        //                $microphones = explode(',', $room->microphone);
        //                if (in_array($userId, $microphones)) {
        //                    UserHandling::calcTime($userId);
        //                }
        //            }
        //            self::quit_hand_2($room->uid, $userId);
        //        }

        $micUserIds = $room->microphones()->pluck('user_id')->filter()->all();

        foreach ($usersIdInRooms as $userId) {
            if (in_array($userId, $micUserIds, true)) {
                UserHandling::calcTime($userId);
            }

            self::quit_hand_2($room->uid, $userId);
        }

        $room->update(['is_live' => false]);
        if ($room->room_admin == null) {
            $room->update(['is_afk' => 0]);
        }
        Pk::where('room_id', $room->id)->where('status', 1)->update(['status' => 0]);
    }

    private static function handleCharismaStatusOnLogout($room, $users, $ownerId)
    {
        $userCharismaService = new UserCharismaService();
        $userCharismaService->removeRoomCharisma($room->id);
        $userDataWithCharisma = $userCharismaService->addTotalEarnedCoinsInUserRoom($room, $users);
        $userDataWithCharisma = $userCharismaService->getUserResetData2($room, $users);

        $ms = [
            'messageContent' => [
                "message" => "updateCharisma",
                'data' => $userDataWithCharisma
            ]
        ];
        $json = json_encode($ms);

        Common::sendToZego('SendCustomCommand', $room->id, $ownerId, $json);
    }



    public static function applyTimezoneToDateValue(\Carbon\Carbon $date, ?string $timezone = null): \Carbon\Carbon
    {
        $timezone = $timezone ?? self::timeZone();
        return $date->setTimezone($timezone);
    }

    public static function getChargerInfo($resource)
    {
        if (request()->is('superadmin/*')) {
            $prefix = 'superadmin';
        } elseif (request()->is('areamanager/*')) {
            $prefix = 'areamanager';
        } else {
            $prefix = 'admin';
        }
        switch ($resource->charger_type) {
            case 'dash':
                $admin = $resource->admin;
                return [
                    'name' => $admin->name ?? '',
                    'image' => $admin->avatar ?? '',
                    'uuid' => $admin->id ?? '',
                    'id' => $admin->id ?? '',
                    'type' => 'dash',
                    'url' => $admin ? url("admin/auth/users/{$admin->id}") : '#',
                    'image_color' => null,
                    'id_image' => '',
                    'colored_name' => '',
                ];

            case UserTypeEnum::AREA_MANAGER:
                $areaManager = $resource->areaManager;
                return [
                    'name' => $areaManager->name ?? '',
                    'image' => $areaManager->avatar ?? '',
                    'uuid' => $areaManager->id ?? '',
                    'id' => $areaManager->id ?? '',
                    'type' => 'dash',
                    'url' => $areaManager ? url("admin/auth/users/{$areaManager->id}") : '#',
                    'image_color' => null,
                    'id_image' => '',
                    'colored_name' => '',
                ];

            case 'agency':
                $agency = $resource->senderShippingAgency;
                $owner = $agency->owner ?? null;

                return [
                    'name' => $agency->name ?? '',
                    'image' => $agency->img ?? '',
                    'uuid' => $agency->id ?? '',
                    'id' => $agency->id ?? '',
                    'type' => 'agency',
                    'url' => $agency ? url("admin/shipping-agencies/profile/{$agency->id}") : '#',
                    'image_color' => $owner->color_image ?? null,
                    'id_image' => $owner?->specialId?->ware?->show_img ?? '',
                    'colored_name' =>  '',
                ];

            case 'host_agency':
                $agency = $resource->senderAgency;
                $owner = $agency->owner ?? null;
                $hasColor = $owner ? Common::hasInPack($owner->id, 18, true) : false;

                return [
                    'name' => $agency->name ?? '',
                    'image' => $agency->img ?? '',
                    'uuid' => $agency->id ?? '',
                    'id' => $agency->id ?? '',
                    'type' => 'host_agency',
                    'url' => $agency ? url("admin/agencies/profile/{$agency->id}") : '#',
                    'image_color' => $owner->color_image ?? null,
                    'id_image' => $owner?->specialId?->ware?->show_img ?? '',
                    'colored_name' => (fn($c) => is_string($c) ? $c : '')($hasColor ? Common::wareUserVip($owner->id, 18, 'color') : null),
                ];

            case 'bd':
                $bd = $resource->bd;
                return [
                    'name' => $bd->username ?? '',
                    'image' => $bd->avatar ?? '',
                    'uuid' => $bd->id ?? '',
                    'id' => $bd->id ?? '',
                    'type' => 'bd',
                    'url' => $bd ? url("admin/usersBd/{$bd->id}") : '#',
                    'image_color' => null,
                    'id_image' => '',
                    'colored_name' => '',
                ];

            case UserTypeEnum::SUB_AREA_MANAGER:
                $subAreaManager = $resource->subAreaManager;
                return [
                    'name' => $subAreaManager->name ?? '',
                    'image' => $subAreaManager->avatar ?? '',
                    'uuid' => $subAreaManager->id ?? '',
                    'id' => $subAreaManager->id ?? '',
                    'type' => 'dash',
                    'url' => $subAreaManager ? url($prefix . "/auth/users/{$subAreaManager->id}") : '#',
                    'image_color' => null,
                    'id_image' => '',
                    'colored_name' => '',
                ];
                case UserTypeEnum::SUPER_ADMIN:
                $subAreaManager = $resource->superAdmin;
                return [
                    'name' => $subAreaManager->name ?? '',
                    'image' => $subAreaManager->avatar ?? '',
                    'uuid' => $subAreaManager->id ?? '',
                    'id' => $subAreaManager->id ?? '',
                    'type' => 'dash',
                    'url' => $subAreaManager ? url($prefix . "/auth/users/{$subAreaManager->id}") : '#',
                    'image_color' => null,
                    'id_image' => '',
                    'colored_name' => '',
                ];

                case UserTypeEnum::SUB_ADMIN:
                $subAreaManager = $resource->subSuperAdmin;
                return [
                    'name' => $subAreaManager->name ?? '',
                    'image' => $subAreaManager->avatar ?? '',
                    'uuid' => $subAreaManager->id ?? '',
                    'id' => $subAreaManager->id ?? '',
                    'type' => 'dash',
                    'url' => $subAreaManager ? url($prefix . "/auth/users/{$subAreaManager->id}") : '#',
                    'image_color' => null,
                    'id_image' => '',
                    'colored_name' => '',
                ];

            case 'user':
                $user = $resource->senderUser;
                $hasColor = $user ? Common::hasInPack($user->id, 18, true) : false;

                return [
                    'name' => $user->name ?? '',
                    'image' => $user->profile->avatar ?? '',
                    'uuid' => $user->uuid ?? '',
                    'id' => $user->id ?? '',
                    'type' => 'user',
                    'url' => $user ? url("admin/users/{$user->id}") : '#',
                    'image_color' => $user->color_image ?? null,
                    'id_image' => $user?->specialId?->ware?->show_img ?? '',
                    'colored_name' => (fn($c) => is_string($c) ? $c : '')($hasColor ? Common::wareUserVip($user->id, 18, 'color') : null),
                ];

            default:
                return [
                    'name' => '',
                    'image' => '',
                    'uuid' => '',
                    'id' => '',
                    'type' => '',
                    'type_name' => '',
                    'url' => '#',
                    'image_color' => null,
                    'id_image' => '',
                    'colored_name' => '',
                ];
        }
    }


    public static function getChargerInfoII($resource)
    {
        if (request()->is('superadmin/*')) {
            $prefix = 'superadmin';
        } elseif (request()->is('areamanager/*')) {
            $prefix = 'areamanager';
        } else {
            $prefix = 'admin';
        }
        switch ($resource->charger_type) {
            case 'dash':
                $admin = $resource->admin;
                return [
                    'name' => $admin->name ?? '',
                    'image' => $admin->avatar ?? '',
                    'uuid' => $admin->id ?? '',
                    'id' => $admin->id ?? '',
                    'type' => 'dash',
                    'url' => $admin ? url("admin/auth/users/{$admin->id}") : '#',

                ];

            case UserTypeEnum::AREA_MANAGER:
                $areaManager = $resource->areaManager;
                return [
                    'name' => $areaManager->name ?? '',
                    'image' => $areaManager->avatar ?? '',
                    'uuid' => $areaManager->id ?? '',
                    'id' => $areaManager->id ?? '',
                    'type' => 'dash',
                    'url' => $areaManager ? url("admin/auth/users/{$areaManager->id}") : '#',

                ];

            case 'agency':
                $agency = $resource->senderShippingAgency;

                return [
                    'name' => $agency->name ?? '',
                    'image' => $agency->img ?? '',
                    'uuid' => $agency->id ?? '',
                    'id' => $agency->id ?? '',
                    'type' => 'agency',
                    'url' => $agency ? url("admin/shipping-agencies/profile/{$agency->id}") : '#',

                ];

            case 'host_agency':
                $agency = $resource->senderAgency;

                return [
                    'name' => $agency->name ?? '',
                    'image' => $agency->img ?? '',
                    'uuid' => $agency->id ?? '',
                    'id' => $agency->id ?? '',
                    'type' => 'host_agency',
                    'url' => $agency ? url("admin/agencies/profile/{$agency->id}") : '#',

                ];

            case 'bd':
                $bd = $resource->bd;
                return [
                    'name' => $bd->username ?? '',
                    'image' => $bd->avatar ?? '',
                    'uuid' => $bd->id ?? '',
                    'id' => $bd->id ?? '',
                    'type' => 'bd',
                    'url' => $bd ? url("admin/usersBd/{$bd->id}") : '#',

                ];

            case UserTypeEnum::SUB_AREA_MANAGER:
                $subAreaManager = $resource->subAreaManager;
                return [
                    'name' => $subAreaManager->name ?? '',
                    'image' => $subAreaManager->avatar ?? '',
                    'uuid' => $subAreaManager->id ?? '',
                    'id' => $subAreaManager->id ?? '',
                    'type' => 'dash',
                    'url' => $subAreaManager ? url($prefix . "/auth/users/{$subAreaManager->id}") : '#',

                ];

            case 'user':
                $user = $resource->senderUser;

                return [
                    'name' => $user->name ?? '',
                    'image' => $user->profile->avatar ?? '',
                    'uuid' => $user->uuid ?? '',
                    'id' => $user->id ?? '',
                    'type' => 'user',
                    'url' => $user ? url("admin/users/{$user->id}") : '#',
                ];

            default:
                return [
                    'name' => '',
                    'image' => '',
                    'uuid' => '',
                    'id' => '',
                    'type' => '',
                    'type_name' => '',
                    'url' => '#',
                ];
        }
    }





    public static function getReceiverInfo($resource)
    {
        if (request()->is('superadmin/*')) {
            $prefix = 'superadmin';
        } elseif (request()->is('areamanager/*')) {
            $prefix = 'areamanager';
        } else {
            $prefix = 'admin';
        }

        switch ($resource->user_type) {
            case 'agency':
                return [
                    'name' => $resource->receiveragency->name ?? '',
                    'image' => $resource->receiveragency->img ?? '',
                    'uuid' => $resource->receiveragency->id ?? '',
                    'id' => $resource->receiveragency->id ?? '',
                    'type' => 'agency',
                    'url' => $resource->receiveragency ? url($prefix . "/shipping-agencies/profile/{$resource->receiveragency->id}") : '#',
                    'image_color'          => @$resource->receiveragency->owner->color_image,
                    'id_image'             => @$resource->receiveragency->owner->specialId?->ware?->show_img ?? '',
                    'colored_name' =>  '',

                ];
            case 'sub_area_manager':
                return [
                    'name' => $resource->receiverSubAreaManager->username ?? '',
                    'image' => $resource->receiverSubAreaManager->avatar ?? '',
                    'uuid' => $resource->receiverSubAreaManager->id ?? '',
                    'id' => $resource->receiverSubAreaManager->id ?? '',
                    'type' => 'sub_area_manager',
                    'url' => $resource->receiverSubAreaManager ? url($prefix . "/shipping-agencies/profile/{$resource->receiverSubAreaManager->id}") : '#',
                    'image_color'          => @$resource->receiverSubAreaManager->owner->color_image,
                    'id_image'             => @$resource->receiverSubAreaManager->owner->specialId?->ware?->show_img ?? '',
                    'colored_name' =>  '',

                ];
            case 'super_admin':
                return [
                    'name' => $resource->receiverSuperAdmin->username ?? '',
                    'image' => $resource->receiverSuperAdmin->avatar ?? '',
                    'uuid' => $resource->receiverSuperAdmin->id ?? '',
                    'id' => $resource->receiverSuperAdmin->id ?? '',
                    'type' => 'super_admin',
                    'url' => $resource->receiverSuperAdmin ? url($prefix . "/shipping-agencies/profile/{$resource->receiverSuperAdmin->id}") : '#',
                    'image_color'          => @$resource->receiverSuperAdmin->owner->color_image,
                    'id_image'             => @$resource->receiverSuperAdmin->owner->specialId?->ware?->show_img ?? '',
                    'colored_name' =>  '',
                ];
            case 'sub_super_admin':
                return [
                    'name' => $resource->receiverSubSuperAdmin->username ?? '',
                    'image' => $resource->receiverSubSuperAdmin->avatar ?? '',
                    'uuid' => $resource->receiverSubSuperAdmin->id ?? '',
                    'id' => $resource->receiverSubSuperAdmin->id ?? '',
                    'type' => 'sub_super_admin',
                    'url' => $resource->receiverSubSuperAdmin ? url($prefix . "/users/profile/{$resource->receiverSubSuperAdmin->id}") : '#',
                    'image_color'          => @$resource->receiverSubSuperAdmin->owner->color_image,
                    'id_image'             => @$resource->receiverSubSuperAdmin->owner->specialId?->ware?->show_img ?? '',
                    'colored_name' =>  '',
                ];
            case 'user':
                return [
                    $hasColor = Common::hasInPack(@$resource->receiver?->id, 18, true),

                    'id' => $resource->receiver->id ?? '',
                    'name' => $resource->receiver->name ?? '',
                    'image' => $resource->receiver->profile->avatar ?? '',
                    'uuid' => $resource->receiver->uuid ?? '',
                    'type' => 'user',
                    'url' => $resource->receiver ? url($prefix . "/users/{$resource->receiver->id}") : '#',
                    'image_color'          => @$resource->receiver->color_image,
                    'id_image'             => @$resource->receiver->specialId?->ware?->show_img ?? '',
                    'colored_name' => (fn($c) => is_string($c) ? $c : '')($hasColor ? common::wareUserVip(@$resource->receiver->id, 18, 'color') : null),

                ];
            default:
                return [
                    'name' => '',
                    'image' => '',
                    'uuid' => '',
                    'id' => '',
                    'type' => '',
                    'url' => '#',
                    'image_color'          => null,
                    'id_image'             => '',
                    'colored_name'         => '',
                ];
        }
    }

    public static function getReceiverInfoII($resource)
    {
        if (request()->is('superadmin/*')) {
            $prefix = 'superadmin';
        } elseif (request()->is('areamanager/*')) {
            $prefix = 'areamanager';
        } else {
            $prefix = 'admin';
        }

        switch ($resource->user_type) {
            case 'agency':
                return [
                    'name' => $resource->receiveragency->name ?? '',
                    'image' => $resource->receiveragency->img ?? '',
                    'uuid' => $resource->receiveragency->id ?? '',
                    'id' => $resource->receiveragency->id ?? '',
                    'type' => 'agency',
                    'url' => $resource->receiveragency ? url($prefix . "/shipping-agencies/profile/{$resource->receiveragency->id}") : '#',

                ];
            case 'sub_area_manager':
                return [
                    'name' => $resource->receiverSubAreaManager->username ?? '',
                    'image' => $resource->receiverSubAreaManager->avatar ?? '',
                    'uuid' => $resource->receiverSubAreaManager->id ?? '',
                    'id' => $resource->receiverSubAreaManager->id ?? '',
                    'type' => 'sub_area_manager',
                    'url' => $resource->receiverSubAreaManager ? url($prefix . "/shipping-agencies/profile/{$resource->receiverSubAreaManager->id}") : '#',

                ];
            case 'super_admin':
                return [
                    'name' => $resource->receiverSuperAdmin->username ?? '',
                    'image' => $resource->receiverSuperAdmin->avatar ?? '',
                    'uuid' => $resource->receiverSuperAdmin->id ?? '',
                    'id' => $resource->receiverSuperAdmin->id ?? '',
                    'type' => 'super_admin',
                    'url' => $resource->receiverSuperAdmin ? url($prefix . "/shipping-agencies/profile/{$resource->receiverSuperAdmin->id}") : '#',
                ];
            case 'sub_super_admin':
                return [
                    'name' => $resource->receiverSubSuperAdmin->username ?? '',
                    'image' => $resource->receiverSubSuperAdmin->avatar ?? '',
                    'uuid' => $resource->receiverSubSuperAdmin->id ?? '',
                    'id' => $resource->receiverSubSuperAdmin->id ?? '',
                    'type' => 'sub_super_admin',
                    'url' => $resource->receiverSubSuperAdmin ? url($prefix . "/users/profile/{$resource->receiverSubSuperAdmin->id}") : '#',
                ];
            case 'user':
                return [

                    'id' => $resource->receiver->id ?? '',
                    'name' => $resource->receiver->name ?? '',
                    'image' => $resource->receiver->profile->avatar ?? '',
                    'uuid' => $resource->receiver->uuid ?? '',
                    'type' => 'user',
                    'url' => $resource->receiver ? url($prefix . "/users/{$resource->receiver->id}") : '#',

                ];
            default:
                return [
                    'name' => '',
                    'image' => '',
                    'uuid' => '',
                    'id' => '',
                    'type' => '',
                    'url' => '#',
                ];
        }
    }


    public static function chargerRelationsQuery()
    {
        return [
            'admin',
            'bd',
            'senderUser',
            'senderUser.packs' => function ($q) {
                $q->whereIn('type', [25])
                    ->where('is_used', true)
                    ->with('ware:id,value');
            },
            'senderUser.profile',
            'senderAgency',
            'senderShippingAgency',
            'receiverUser',
            'receiverUser.profile',
            'receiverUser.packs' => function ($q) {
                $q->whereIn('type', [25])
                    ->where('is_used', true)
                    ->with('ware:id,value');
            },
            'receiveragency',
        ];
    }


    public static function getUserMediaStats($userId, $type, $agencyId)
    {
        if (!in_array($type, ['moment', 'reel'])) {
            return null;
        }

        $record = UserSallary::where('user_agency_id', $agencyId)->where('user_id', $userId)->latest()->first();

        if (! $record || empty($record->extras)) {
            return null;
        }

        $extras = json_decode($record->extras, true);

        if (! isset($extras[$type])) {
            return null;
        }

        return $extras[$type];
    }


    public static function isReliableTransferEnabled(): bool
    {
        return settings()->get('transfer_salary_reliable_shipping_agency') == 1;
    }

    public static function canTransferToAgency($agency): bool
    {
        if (!self::isReliableTransferEnabled()) {
            return true;
        }
        $agency = is_numeric($agency) ? ShippingAgency::find($agency) : $agency;

        if (!$agency) {
            return false;
        }

        $ownerId = $agency->app_owner_id ?? null;
        if (!$ownerId) {
            return false;
        }
        return User::where('id', $ownerId)
            ->where('appear_charger_agency', 1)
            ->exists();
    }

    public static function getEffectiveJoinPeriod($userId, $agencyId, $fromDate)
    {
        $from = Carbon::parse($fromDate)->startOfDay();

        $latestJoin = UsersJoinedAgency::where('user_id', $userId)
            ->where('agency_id', $agencyId)
            ->where('type', 2)
            ->orderByDesc('join_date')
            ->value('join_date');

        $startDate = $latestJoin && Carbon::parse($latestJoin)->gt($from)
            ? Carbon::parse($latestJoin)->startOfDay()
            : $from;

        $endDate = $from->copy()->endOfMonth();

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }


    public static function getCurrentBalance(int $userId): int
    {
        $balance = User::where('id', $userId)->value('di') ?? 0;
        return $balance;
    }

    public static function getCoinSubTypes()
    {
        return UserCoinLog::query()
            ->select('sub_type')
            ->distinct()
            ->whereNotNull('sub_type')
            ->pluck('sub_type')
            ->toArray();
    }


    public static function checkUserAgencyFrozen(User $user): void
    {
        $ownedAgency = ShippingAgency::withoutGlobalScopes()
            ->where('app_owner_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if ($ownedAgency && $ownedAgency->is_frozen) {
            throw new \Exception(__('frozen_agency_by_admin'));
        }
        if ($user->agency_id) {
            $hostAgency = ShippingAgency::withoutGlobalScopes()
                ->where('id', $user->agency_id)
                ->whereNull('deleted_at')
                ->first();

            if ($hostAgency && $hostAgency->is_frozen) {
                throw new \Exception(__('frozen_agency_by_admin'));
            }
        }
    }


    public static function isUserBannedFromRoute(string $uuid, string $routeName, string $method)
    {
        $isBanned =  Ban::where('uid', $uuid)
            ->whereHas('banType', function ($query) use ($routeName, $method) {
                $query->where('route', $routeName)
                    ->where(function ($q) use ($method) {
                        $q->whereNull('method')
                            ->orWhere('method', strtoupper($method));
                    });
            })
            ->exists();
        return $isBanned ? self::bannedResponse() : null;
    }



    public static function bannedResponse(): JsonResponse
    {
        return Common::apiResponse(1, __('banned_from_action'), [], 377);
    }


    public static function userBadge($userId, $badgeId, $days, $type)
    {
        $badge = Badge::find($badgeId);
        if (!$badge) {
            return;
        }
        $badgeUser = UserBadge::where('user_id', $userId)->where('badge_id', $badgeId)->active()->first();
        if ($badgeUser && $badgeUser->expire != 0) {
            $badgeUser->expire += (($days) * 86400);
            $badgeUser->receive_type = $type;
            $badgeUser->save();
        } elseif (!$badgeUser) {
            $data = [
                'user_id' => $userId,
                'badge_id' => $badgeId,
                'expire' => $days == 0 ? 0 : time() + (($days) * 86400),
                'receive_type' => $type,
            ];

            UserBadge::query()->create($data);
        }
    }

    public static function areaCountries(): array
    {
        $adminId = session('area_manager_id') ?? auth()->id();

        $authAdmin = AreaManager::find($adminId)
            ?? SubAreaManager::find($adminId);

        if (!$authAdmin) {
            return [];
        }

        $sessionCountryId = session('area_manager_country_id');
        if ($sessionCountryId) {
            return (array)$sessionCountryId;
        }

        if (method_exists($authAdmin, 'countriesQuery')) {
            return $authAdmin->countriesQuery()->pluck('id')->toArray();
        }

        return [];
    }

    public static function areaCountriesV2($adminId): array
    {
        $authAdmin = AreaManager::find($adminId)
            ?? SubAreaManager::find($adminId);

        if (!$authAdmin) {
            return [];
        }

        $sessionCountryId = session('area_manager_country_id');
        if ($sessionCountryId) {
            return (array)$sessionCountryId;
        }

        if (method_exists($authAdmin, 'countriesQuery')) {
            return $authAdmin->countriesQuery()->pluck('id')->toArray();
        }

        return [];
    }


    /**
     * ─────────────────────────────────────────────────────────────────
     *  Upload & Optimize Image/GIF (async via Queue)
     * ─────────────────────────────────────────────────────────────────
     *
     *  Usage:
     *    // Profile image → resize 512px, compress, WebP, generate thumbnail
     *    $path = Common::uploadOptimized('profile', $img, 'profile', Profile::class, $profile->id, 'avatar');
     *
     *    // Room cover → resize 1024px, compress, WebP
     *    $path = Common::uploadOptimized('rooms', $file, 'room', Room::class, $room->id, 'room_cover');
     *
     *    // General image → no model update, just optimize
     *    $path = Common::uploadOptimized('images', $file, 'general');
     *
     * @param string      $folder     Storage folder (e.g. 'profile', 'rooms', 'images')
     * @param mixed       $file       UploadedFile instance
     * @param string      $context    Optimization context: profile|room|banner|gift|general
     * @param string|null $modelClass Model class to update after optimization (e.g. App\Models\Profile)
     * @param int|null    $modelId    Model ID to update
     * @param string|null $column     Model column to store the new path
     * @param string|null $disk       Storage disk (null = default)
     * @return string|false           Storage path or false on failure
     */
    public static function uploadOptimized(
        string  $folder,
        $file,
        string  $context = 'general',
        ?string $modelClass = null,
        ?int    $modelId = null,
        ?string $column = null,
        ?string $disk = null
    ): string|false {
        if (!$file || !$file->isValid()) {
            return false;
        }

        // Step 1: Upload original immediately (fast response to user)
        $path = self::upload($folder, $file, $disk);

        if (!$path) {
            return false;
        }

        // Step 2: Dispatch optimization job to queue (async)
        \App\Jobs\OptimizeMediaJob::dispatch(
            storagePath: $path,
            folder: $folder,
            context: $context,
            modelClass: $modelClass,
            modelId: $modelId,
            column: $column,
        )->onQueue('optimization-images');

        return $path;
    }

    /**
     * Validate an uploaded image/GIF file before processing.
     *
     * @param mixed  $file    UploadedFile
     * @param string $context profile|room|banner|gift|general
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public static function validateMedia($file, string $context = 'general'): array
    {
        if (!$file instanceof \Illuminate\Http\UploadedFile) {
            return ['valid' => false, 'error' => 'Not a valid upload file.'];
        }

        if (!$file->isValid()) {
            return ['valid' => false, 'error' => 'Uploaded file is corrupted.'];
        }

        $ext  = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $size = $file->getSize();

        // Allowed types
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed)) {
            return ['valid' => false, 'error' => "File type '{$ext}' not allowed."];
        }

        // MIME check
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowedMimes)) {
            return ['valid' => false, 'error' => "Invalid MIME type: {$mime}"];
        }

        // Size limits
        $maxSize = ($ext === 'gif') ? 8 * 1024 * 1024 : 5 * 1024 * 1024;
        if ($size > $maxSize) {
            $maxMB = $maxSize / 1024 / 1024;
            return ['valid' => false, 'error' => "File too large. Max: {$maxMB}MB"];
        }

        // Verify image readable
        $info = @getimagesize($file->getPathname());
        if ($info === false) {
            return ['valid' => false, 'error' => 'File is not a valid image.'];
        }

        // Max dimensions
        if ($info[0] > 6000 || $info[1] > 6000) {
            return ['valid' => false, 'error' => 'Image dimensions too large. Max: 6000px.'];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * ─────────────────────────────────────────────
     *  🖼️ Get Image URL with version support
     * ─────────────────────────────────────────────
     *  Convention-based: builds version path from original path
     *
     *  Original: profile/abc123.webp
     *  Thumb:    profile/versions/abc123_thumb.webp
     *  Medium:   profile/versions/abc123_medium.webp
     *  Large:    profile/versions/abc123_large.webp
     *
     * @param string|null $path    Original storage path
     * @param string      $size    Size: 'original', 'thumb', 'medium', 'large'
     * @return string              Full URL or empty string
     */
    public static function getImageUrl(?string $path, string $size = 'medium'): string
    {
        if (!$path) {
            return '';
        }
        if ($size === 'original') {
            return Storage::url($path);
        }
        $pathInfo = pathinfo($path);
        $versionPath = $pathInfo['dirname'] . '/versions/'
            . $pathInfo['filename'] . '_' . $size . '.'
            . $pathInfo['extension'];
        // Check if exists, fallback to original
        if (Storage::exists($versionPath)) {
            return Storage::url($versionPath);
        }
        return Storage::url($path);
    }

    /**
     * Get all image versions as an array (for API responses)
     *
     * @param string|null $path  Original storage path
     * @return array             ['original' => url, 'thumb' => url, 'medium' => url, 'large' => url]
     */
    public static function getImageVersions(?string $path): array
    {
        if (!$path) {
            return [
                'original'  => '',
                'thumb' => '',
                'medium'    => '',
                'large'     => '',
            ];
        }

        return [
            'original'  => self::getImageUrl($path, 'original'),
            'thumb' => self::getImageUrl($path, 'thumb'),
            'medium'    => self::getImageUrl($path, 'medium'),
            'large'     => self::getImageUrl($path, 'large'),
        ];
    }

    public static function getRoleAuthId($userId)
    {
        $userId = $userId ?? auth()->id();
        $user = DB::table('admin_users')->where('id', $userId)->first();
        if (! $user) {
            return null;
        }
        if ($user->type === 'area-manager') {
            return $user->id;
        }
        if ($user->type === 'sub_area_manager') {
            return $user->parent_id;
        }
        return $user->id;
    }
}
