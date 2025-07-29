<?php

namespace Modules\Events\Http\Controllers;

use App\Helpers\UserCoinLogHelper;
use Carbon\Carbon;
use App\Models\OVip;
use App\Models\User;
use App\Models\Ware;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Events\Entities\RewardTarget;
use Modules\Events\Entities\UserChargeEvent;
use Modules\Events\Entities\ChargeTargetEvent;
use Modules\Events\Transformers\TargetsResource;
use Modules\Events\Transformers\UserChargeResource;
use Modules\Achievement\Entities\UserAchievementLevel;
use Modules\Events\Transformers\TopUserChargeResource;

class ChargeEventController extends Controller
{
    public function wonEvent()
    {
        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $tillDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();


        $user =  User::query()
            // ->leftJoinSub(
            //     function ($query) use ($fromDate,$tillDate) {
            //         $query->select('user_id', \DB::raw('SUM(amount) as charges_sum_amount'))
            //               ->from('charges')
            //              -> whereBetween('created_at',[$fromDate,$tillDate])
            //               ->groupBy('user_id');
            //     },
            //     'charges',
            //     'users.id',
            //     'charges.user_id'
            // )
            ->leftJoinSub(
                function ($query) use ($fromDate, $tillDate) {
                    $query->select('charges.user_id', \DB::raw('SUM(charges.amount) as charges_sum_amount'))
                        ->from('charges')
                        ->where('charges.user_type', 'user')
                        ->whereBetween('charges.created_at', [$fromDate . ' 00:00:00', $tillDate . ' 23:59:59'])
                        ->groupBy('charges.user_id');
                },
                'charges',
                'users.id',
                'charges.user_id'
            )
            ->leftJoinSub(
                function ($query) use ($fromDate, $tillDate) {
                    $query->select('user_id', \DB::raw('SUM(obtained_coins) as coin_logs_sum_obtained_coins'))
                        ->from('coin_logs')
                        //   ->whereBetween('created_at',[$fromDate,$tillDate])
                        ->whereBetween('coin_logs.created_at', [$fromDate . ' 00:00:00', $tillDate . ' 23:59:59'])->where('coin_logs.status', 1)
                        ->groupBy('user_id');
                },
                'coin_logs',
                'users.id',
                'coin_logs.user_id'
            )
            ->select(['users.*', \DB::raw('IFNULL(charges_sum_amount, 0) + IFNULL(coin_logs_sum_obtained_coins, 0) as total_sum')])
            ->orderBy('total_sum', 'desc')->limit(1)->first();

        return Common::apiResponse(1, '', new TopUserChargeResource($user));
    }

    public function chargeEventRole(Request $request)
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
    
        $user = Auth::user()->load([
            'charges' => fn ($q) => $q->whereBetween('created_at', [$start, $end]),
            'coinLogs' => fn ($q) => $q->whereBetween('created_at', [$start, $end])->where('status', 1),
        ]);
    
        return Common::apiResponse(1, '', new UserChargeResource($user));
    }
    public function targets()
    {
        $targets = ChargeTargetEvent::query()->with("rewards", 'users')->orderBy('value', 'asc')->get();
        $currentMonth = now()->month;
        $user = User::withSum(['charges' => function ($query) use ($currentMonth) {
            $query->whereMonth('created_at', $currentMonth);
        }], 'amount')->withSum(['coinLogs' => function ($query) use ($currentMonth) {
            $query->whereMonth('created_at', $currentMonth)->where('status', 1);
        }], 'obtained_coins')->find(auth()->user()->id);
        return Common::apiResponse(1, '', new TargetsResource(
            $targets,
            $user->charges_sum_amount ?? 0,
            $user->coin_logs_sum_obtained_coins ?? 0
        ));
    }

    public function received_rewards(Request $request)
    {
        // return Common::apiResponse(0, __('معطل لبعض الوقت'));
        $currentMonth = now()->month;

        $user = User::query()->withSum(['charges' => function ($query) use ($currentMonth) {
            $query->whereMonth('created_at', $currentMonth);
        }], 'amount')->withSum(['coinLogs' => function ($query) use ($currentMonth) {
            $query->whereMonth('created_at', $currentMonth)->where('status', 1);
        }], 'obtained_coins')->find(auth()->user()->id);
        if ($user->type_user == 3) {
            return Common::apiResponse(0, __('api_responses.notAllowed'));
        }
        $charges_sum_amount = $user->charges_sum_amount ?? 0;
        $coin_logs_sum_obtained_coins = $user->coin_logs_sum_obtained_coins ?? 0;
        $target = ChargeTargetEvent::query()->find($request->target_id);
        if ($target == null) {
            return Common::apiResponse(0, 'لا يوجد تارجيت');
        }
        $total = $charges_sum_amount + $coin_logs_sum_obtained_coins;
        //  dd($total ,$target->value);
        // $percentage = ((($charges_sum_amount + $coin_logs_sum_obtained_coins) * 100 ) /$target->value);
        $checkChargeEvent = UserChargeEvent::query()->where(["user_id" => $user->id, 'charge_event_id' => $request->target_id])->first();

        if ($checkChargeEvent) {
            return Common::apiResponse(0, __('you_have_charge_target'));
        }

        if ($total < $target->value) {
            return Common::apiResponse(0, __('api_responses.dont_achieve_taregt'));
        }

        UserChargeEvent::create([
            'user_id' => $user->id,
            'charge_event_id' => $request->target_id,
        ]);
        $rewards =  RewardTarget::where('charge_event_id', $target->id)->get();
        foreach ($rewards as $reward) {
            if ($reward->type == "coins") {
                $amountBefore =  Common::getCurrentBalance($user->id);
                $logAmount = $reward->target;
                UserCoinLogHelper::log(
                    $user->id ,
                    'charge_event',
                    'charge_events',
                    $logAmount ?? 0,
                    $amountBefore ?? 0,
                    'event'
                );
                $user->di += $reward->target;
                $user->save();
            } elseif ($reward->type == "vip") {
                $vip = OVip::query()->find($reward->target);
                UserCommon::addVipToUser($user, $vip, $reward->expire);
            } elseif ($reward->type == "ware") {
                $ware = Ware::query()->find($reward->target);
                UserCommon::addWareToUser($user, $ware, $reward->expire);
            } elseif ($reward->type == "achievement") {
                $attributes = [
                    'user_id'       => $user->id,
                    'custom_image' => $reward->target,
                ];
                UserAchievementLevel::create($attributes);
            }
        }
        return Common::apiResponse(1, __('تم الاضافه بنجاح'));
    }
}
