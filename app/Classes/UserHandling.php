<?php

namespace App\Classes;

use Carbon\Carbon;
use App\Models\Ban;
use Utd\Vip\Entities\Vip;
use Utd\Gifts\Entities\Gift;
use Utd\Vip\Entities\OVip;
use App\Models\User;
use App\Models\Config;
use App\Helpers\Common;
use App\Models\BanType;
use Utd\Gifts\Entities\GiftLog;
use Utd\Vip\Entities\UserVip;
use App\Models\LiveTime;
use App\Models\RealtimeProject;
use App\Models\UserSallary;
use App\Models\UsersJoinedAgency;
use Illuminate\Support\Facades\DB;
use Utd\Agency\Entities\AgencyUserJob;
use Utd\Vip\Helpers\VipCommon;
use App\Support\PackageHelper;

class UserHandling
{

    public function calcTime($uid)
    {

        // case 1 : up_mic and go_mic in the same day
        $user  = User::find($uid);
        $uid         = $user->id;
        $timer =
            LiveTime::query()->where('uid', $uid)->whereDate('created_at', today())->where('end_time', null)->orderByDesc('id')->first();

        if ($timer) {
            $second = (time() - $timer->start_time);
            $hours           = round((time() - $timer->start_time) / (60 * 60), 2);
            $timer->end_time = time();
            $timer->hours    = $hours;
            $timer->save();

            $user_hours =
                LiveTime::query()->where('uid', $uid)->whereYear('created_at', '=', Carbon::now()->year)->whereMonth('created_at', '=', Carbon::now()->month)->whereDay('created_at', '=', Carbon::now()->day)->sum('hours');

            $this->realtimeProject($second);
            $hours = (int)$user_hours;
            $num = \Cache::get('hours_days') ?? 2;

            if ($hours >= $num && $user->today_days == 0) {
                DB::statement("
                UPDATE users
                SET today_days = 1
                WHERE id = :id
            ", ['id' => $uid]);
            }
        }
    }

    public function realtimeProject($second)
    {
        $realtimeProject = RealtimeProject::where('type', 'audio')->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->first();
        if (!$realtimeProject) {
            $realtimeProject = RealtimeProject::create([
                'type' => 'audio',
            ]);
        };
        $realtimeProject->used += $second;
        $realtimeProject->save();
    }

    public function checkIfUserHostByIds(array $userIds): array
    {
        return User::query()
            ->whereIn('id', $userIds)
            ->where(fn($q) => $q->where('agency_id', '!=', 0)->where('agency_id', '!=', null))
            ->where('type_user', '!=', 0)
            ->pluck('id')->toArray();
    }
    public function AddUserVip(User $user, $type = null,$receiveType=null)
    {
        // $vip = OVip::query()->whereLevel(2)->first();

        // if ($vip) {

        //     VipCommon::createUserVip($vip ,$user ,$vip->expire , null ,$type,1,0,0,$receiveType);

        // }
    }
    public function kickUserFromAgency(User &$user, $isApp = 0): void
    {
        $this->handleUserSalaries($user);
        $this->clearUserAgencyLogs($user);
        $this->updateUserJoinedAgency($user, $isApp);
        $this->resetUserAgencyData($user);

    }

    private function resetUserAgencyData(User &$user)
    {
        $user->total_diamond_received -= $user->monthly_diamond_received;
        $user->is_host = User::TYPE_REGULAR;
        $user->agency_id = 0;
        $user->type_user = User::TYPE_REGULAR;
        $user->monthly_days = 0;
        $user->save();
        app(\App\Contracts\MilestoneHelperContract::class)->removeReward($user, 'host');

        if (PackageHelper::isInstalled('achievement')) {
            uploadMonthlyDiamondReceive($user->id, 0);
        }
    }

    private function handleUserSalaries(User $user)
    {
   
            $agencyId = $user->agency_id;
            $timezone = getTimezone();
            $currentMonth = now( $timezone)->month;
            $currentYear = now( $timezone)->year;
    
            $userSalaries = UserSallary::query()
                ->where('user_id', $user->id)
                ->where('user_agency_id', $agencyId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('is_finished', 0)
                ->first();
             
            if (!$userSalaries) return;
           
            if ($userSalaries->month == $currentMonth && $userSalaries->year == $currentYear) {
              
                $userSalaries->update(['is_finished' => 1]);
            }
        
    }

    private function clearUserAgencyLogs(User $user)
    {
        $agencyId = $user->agency_id;
        GiftLog::query()->where('receiver_id', $user->id)
        ->where('agency_id', $agencyId)->update(['is_finished' => 1]);
        AgencyUserJob::where(['user_id' => $user->id, 'agency_id' => $agencyId])->delete();
    }

    private function updateUserJoinedAgency(User $user, $isApp)
    {
        $agencyId = $user->agency_id;
        $joined = UsersJoinedAgency::where([
            'user_id' => $user->id,
            'agency_id' =>  $agencyId,
            'type' => 2,
        ])->whereNull('leave_date')->first();

        if (!$joined) return;

        $joined->leave_date = now();
        $joined->status = 'kick off';
        if ($isApp) {
            $joined->kicked_by_app = auth()->id();
        } else {
            $joined->kicked_by_admin = auth()->id();
        }
        $joined->save();
    }

    public function kickOfAllUsersFromAgency(\App\Models\Agency $agency)
    {
        $agencyId = $agency->id;
    
        $users = User::where('agency_id', $agencyId)
            ->get();
    
        if ($users->isEmpty()) {
            return; 
        }
    
        DB::transaction(function () use ($users, $agencyId) {
    
            foreach ($users as $user) {
                self::kickUserFromAgency($user, 0);
            }

    
            DB::table('agency_sallaries')->where('agency_id', $agencyId)->delete();
        });
    }
    


    public static function checkIfUserOwnerOfAgency(User $user): bool
    {
        return \App\Models\Agency::query()->where('owner_id', $user->id)->orWhere('app_owner_id', $user->id)->exists();
    }

    public function checkIfUserOwnerOfFamily(int $userId): bool
    {
        return app(\App\Contracts\FamilyContract::class)->isFamilyOwner($userId);
    }

    public function removeMonthlyLiveTimes(int $userId): bool
    {
        LiveTime::query()->where('uid', $userId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->delete();
        return true;
    }

    public function haveBan(string $uuid, $request): bool
    {
        $now = now();
        return Ban::query()
            ->where(fn($q) => $q->where('uid', $uuid)->where('type', '!=', 'action')->orWhere(fn($q) => $q->where('ip', '!=', null)->where('ip', $request->ip()))->orWhere(fn($q) => $q->where('device_number', '!=', null)->where('device_number', $request->header('device'))))
            ->whereRaw("DATE_ADD(created_at, INTERVAL duration HOUR) > '$now'")
            ->exists();
    }

    public function getUserBan(string $uuid, $request)
    {
        $now = now();

        return Ban::query()->where('type', '!=', 'action')
            ->where(fn($q) => $q->where('uid', $uuid)
                ->orWhere(fn($q) => $q->where('ip', '!=', null)->where('ip', $request->ip()))
                ->orWhere(fn($q) => $q->where('device_number', '!=', null)->where('device_number', $request->header('x-device-token'))))
            ->whereRaw("DATE_ADD(created_at, INTERVAL duration HOUR) > '$now'")
            ->first();
    }

    public function getUserBanType(string $uuid, $request)
    {
        $now = now();
        $url = $request->url();
        $method = $request->method();
        $path = parse_url($url, PHP_URL_PATH);
        // Remove the leading slash
        $path = ltrim($path, '/');
        // Split the path into segments
        $segments = explode('/', $path);
        // Get the desired result
        $route = implode('/', array_slice($segments, 1));
        $message = null;
        $banType  = BanType::where('route', $route)->where(function ($query) use ($method) {
            $query->where('method', $method)->orWhereNull('method');
        })->first();
        if (!$banType) return  $message;
        $ban = Ban::where('ban_type_id', $banType->id)->whereNotNull('ban_type_id')->where('uid', $uuid)
            ->with('banType')->where('type', 'action')->whereRaw("DATE_ADD(created_at, INTERVAL duration HOUR) > '$now'")->first();
        if ($ban) {
            $message = __('api.type_ban', [
                'type'        => app()->getLocale() == 'ar' ? ($banType->name_ar ?? '') : ($banType->name_en ?? ''),
                'time'        => $ban?->duration,
                'description' => app()->getLocale() == 'ar' ? $ban?->description_ar : $ban?->description_en
            ]);
        }

        return  $message;
    }

    public function hasReasonOfBan(?string $uuid, $request): ?string
    {
        if (is_null($uuid)) {
            return false;
        }
        $banFounded = $this->getUserBan($uuid, $request);
        $message = null;
        if ($banFounded) {
            $type        = $banFounded->type;
            $description = app()->getLocale() == 'ar' ? $banFounded?->description_ar : $banFounded?->description_en;
            $duration    = $banFounded?->duration;
            $banType     = match ($type) {
                'normal' => __('api.account'),
                'device' => __('api.device'),
                default => __('api.ip'),
            };

            $message = __('api.login_ban', [
                'type'        => $banType,
                'time'        => $duration,
                'description' => $description
            ]);
        }
        return $message;
    }

    public function chickLevelToPlay(User $user, $configValue = null): int
    {
        $configValue = $configValue ?? (Common::getConfig('min_level_to_play') ?? 10);

        $levelSub = $user->total_sender_level;

        if ($user->can_play == 3) {
            return (0);
        } else {
            if ($user->can_play == 2) {
                return 1;
            } else {
                //                $isHavePhone = $user->phone != null || $user->phone != '';
                return (/*$isHavePhone &&*/(($user->haveVip()->exists()) || $user->mangerType || ($user->type_user != 0 && $user->agency_id != 0))) ? 1 : (($levelSub >= $configValue /*&& $isHavePhone*/) ? 1 : 0);
            }
        }
    }



    public function getLevel(int $type, int $totalCoins)
    {
        return PackageHelper::isInstalled('vip') ? Vip::query()->where(['type' => $type])->where('exp', '<=', $totalCoins)->orderByDesc('exp')->limit(1)->first() : null;
    }

    public function getTopThreeSupport($userId)
    {
        $results = GiftLog::query()
            ->with('sender.profile')
            ->select('sender_id')
            ->selectRaw('SUM(giftNum * giftPrice) AS total')
            ->selectRaw('CAST(SUM(giftNum * giftPrice) AS DECIMAL(10, 2)) AS total')
            ->where('receiver_id', $userId)
            ->groupBy('sender_id')
            ->orderByDesc('total')  
            ->take(3)
            ->get();

        $data = $results->map(function ($result) {
            $image = optional(optional($result->sender)->profile)->avatar ?? '';

            return [
                'id' => $result->sender_id,
                'image' => $image,
            ];
        })->all();

        return $data;
    }


    public function soundEffect(User $user, $column = "color")
    {
        return @$user->soundEffect?->ware?->$column;
    }
}
