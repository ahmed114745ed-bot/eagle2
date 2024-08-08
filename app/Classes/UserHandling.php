<?php

namespace App\Classes;

use App\Models\OVip;
use App\Models\UserVip;
use App\Models\Vip;
use Carbon\Carbon;
use App\Models\Ban;
use App\Models\User;
use App\Models\Config;
use App\Helpers\Common;
use App\Models\BanType;
use App\Models\Gift;
use App\Models\GiftLog;
use App\Models\LiveTime;
use App\Models\UserSallary;
use Illuminate\Support\Facades\DB;
use Modules\AgencyApp\Entities\AgencyUserJob;

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
            $hours           = round((time() - $timer->start_time) / (60 * 60), 2);
            $timer->end_time = time();
            $timer->hours    = $hours;
            $timer->save();

            $user_hours =
                LiveTime::query()->where('uid', $uid)->whereYear('created_at', '=', Carbon::now()->year)->whereMonth('created_at', '=', Carbon::now()->month)->whereDay('created_at', '=', Carbon::now()->day)->sum('hours');


            $hours = (int)$user_hours;

            if ($hours >= 1 && $user->today_days == 0) {
                DB::statement("
                UPDATE users
                SET today_days = 1
                WHERE id = :id
            ", ['id' => $uid]);
            }
        }
    }

    public function AddUserVip(User $user, $type = null)
    {
        $vip = OVip::query()->whereLevel(2)->first();
        UserVip::query()->create(
            [
                'type' => 1,
                'sender_id' => 0,
                'user_id' => $user->id,
                'vip_id' => $vip->id,
                'level' => $vip->level,
                'expire' => Carbon::now()->addDays($vip->expire ?: 1)->timestamp,
                'qty' => 1,
                'price' => 0,
                'total' => 0,
                'type_send' => $type,
            ]
        );
        Common::handelVip($vip, $user);
    }
    public function kickUserFromAgency(User &$user)
    {
        // decrement total diamond with monthly diamond when user not in agency
        $user->total_diamond_received -= $user->monthly_diamond_received;
        // set diamond to zero
        $user->monthly_diamond_received = 0;

        $agencyId = $user->agency_id;
        $user->is_host = 0;
        $user->agency_id = 0;
        $user->type_user = 0;


        // set user salary this month to zero
        $values = [
            'sallary'        => 0,
            'cut_amount'     => 0,
            // 'agency_sallary' => 0
        ];

        $user_sallaries = UserSallary::query()
            ->where([
                'user_id' => $user->id,
            ])
            ->orderBy('id', 'desc')
            ->take(2)
            ->get();

        if ($user_sallaries->isNotEmpty()) {
            if ($user_sallaries[0]->month == now()->month && $user_sallaries[0]->year == now()->year && count($user_sallaries) >= 2) {
                $user_salary_this_month = $user_sallaries[0];
                $user_salary_befor_month = $user_sallaries[1];
                if ($user_salary_this_month->cut_amount >= $user_salary_this_month->sallary) {
                    $user_salary_befor_month->cut_amount += ($user_salary_this_month->cut_amount - $user_salary_this_month->sallary);
                    $user_salary_befor_month->save();
                }
                $user_salary_this_month->update($values);
            } elseif ($user_sallaries[0]->month == now()->month && $user_sallaries[0]->year == now()->year && count($user_sallaries) < 2) {
                $user_sallaries[0]->update($values);
            }
        }

        GiftLog::query()->where('receiver_id', $user->id)->where('agency_id', $agencyId)->update(['agency_id' => 0]);
        LiveTime::query()->where('uid', $user->id)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->delete();

        $user->monthly_days = 0;
        $user->save();
        AgencyUserJob::where(['user_id' => $user->id , 'agency_id' => $agencyId])->delete();

    }

    public function kickOfAllUsersFromAgency(\App\Models\Agency $agency)
    {
        $agency_id = $agency->id;

        $usersIds = User::query()->where('agency_id', $agency_id)->pluck('id');
        // update in user_sallaries table
        foreach ($usersIds as $user_id) {
            // set user salary this month to zero
            $values = [
                'sallary'        => 0,
                'cut_amount'     => 0,

            ];

            $user_sallaries = UserSallary::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->take(2)->get();
            if (count($user_sallaries) > 0) {
                if (count($user_sallaries) >= 2 && $user_sallaries[0]->month == now()->month && $user_sallaries[0]->year == now()->year) {
                    $user_salary_this_month = $user_sallaries[0];
                    $user_salary_befor_month = $user_sallaries[1];
                    if ($user_salary_this_month->cut_amount >= $user_salary_this_month->sallary) {
                        $user_salary_befor_month->cut_amount += ($user_salary_this_month->cut_amount - $user_salary_this_month->sallary);
                        $user_salary_befor_month->save();
                    }
                    $user_salary_this_month->update($values);
                } elseif (count($user_sallaries) < 2 && $user_sallaries[0]->month == now()->month && $user_sallaries[0]->year == now()->year) {
                    $user_sallaries[0]->update($values);
                }
            }
        }

        // update in users tables
        DB::table('users')
            ->where('agency_id', $agency_id)
            ->update([
                'total_diamond_received' => DB::raw('CASE WHEN total_diamond_received < 0 THEN 0 ELSE total_diamond_received - monthly_diamond_received  END'),
                'monthly_diamond_received' => 0,
                'is_host' => 0,
                'agency_id' => 0,
                'monthly_days' => 0,
                'type_user' => 0
            ]);


        DB::table('live_times')->whereIn('uid', $usersIds)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->delete();

        DB::table('agency_sallaries')->where('agency_id', $agency_id)->delete();

        AgencyUserJob::where(['agency_id' => $agency_id])->delete();

    }


    public function checkIfUserOwnerOfAgency(User $user): bool
    {
        return \App\Models\Agency::query()->where('owner_id', $user->id)->orWhere('app_owner_id', $user->id)->exists();
    }

    public function checkIfUserOwnerOfFamily(int $userId): bool
    {
        return \App\Models\Family::query()->where('user_id', $userId)->exists();
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
            ->where(fn ($q) => $q->where('uid', $uuid)->where('type', '!=', 'action')->orWhere(fn ($q) => $q->where('ip', '!=', null)->where('ip', $request->ip()))->orWhere(fn ($q) => $q->where('device_number', '!=', null)->where('device_number', $request->header('device'))))
            ->whereRaw("DATE_ADD(created_at, INTERVAL duration HOUR) > '$now'")
            ->exists();
    }

    public function getUserBan(string $uuid, $request)
    {
        $now = now();
        return Ban::query()->where('type', '!=', 'action')
            ->where(fn ($q) => $q->where('uid', $uuid)->orWhere(fn ($q) => $q->where('ip', '!=', null)->where('ip', $request->ip()))->orWhere(fn ($q) => $q->where('device_number', '!=', null)->where('device_number', $request->header('device'))))
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

    public function hasReasonOfBan(string $uuid, $request): ?string
    {
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
        $configValue = $configValue ?? (Common::getConf('min_level_to_play') ?? 10);

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
        return Vip::query()->where(['type' => $type])->where('exp', '<=', $totalCoins)->orderByDesc('exp')->limit(1)->first();
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
            ->orderByDesc('total')  // Now 'total' is correctly treated as a numeric type
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
