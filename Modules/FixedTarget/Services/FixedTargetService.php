<?php

namespace Modules\FixedTarget\Services;

use App\Helpers\Common;
use App\Models\BDSallary;
use App\Models\UsersJoinedAgency;
use App\Services\WalletService;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Agency;
use App\Models\Target;
use App\Models\LiveTime;
use App\Models\UserTarget;
use App\Helpers\UserCommon;
use App\Models\UserSallary;
use Illuminate\Support\Facades\Log;
use Modules\Reals\Entities\Real;
use Modules\Moment\Entities\Moment;
use Illuminate\Database\Eloquent\Model;
use Modules\Moment\Entities\MomentLikes;
use Modules\Reals\Entities\RealUserLike;
use Modules\FixedTarget\Enums\TargetType;
use Modules\Moment\Entities\MomentCommint;
use Modules\Reals\Entities\RealUserComment;
use Modules\FixedTarget\Entities\SpecialUser;
use Modules\FixedTarget\Classes\RegularTarget;
use Modules\FixedTarget\Classes\FixedTargetClass;
use Modules\FixedTarget\Interfaces\TargetInterface;
class FixedTargetService
{
    private TargetInterface $targetInstance;
    private TargetType $userTargetType;

    private \DateTime $startDate;
    private \DateTime $joinDate;
    private \DateTime $endDate;

    public function __construct(private User $user, private ?int $month = null, private ?int $year = null)
    {
        $timezone = getTimezone();
        if (is_null($this->month) || is_null($this->year)) {
            $dt = new \DateTime('now', new \DateTimeZone($timezone));
            $this->month = $dt->format('m');
            $this->year  = $dt->format('Y');
        }

        $joinDate = UsersJoinedAgency::where('user_id', $user->id)
            ->where('agency_id', $user->agency_id)
            ->latest()
            ->value('join_date');

        $this->joinDate = Carbon::parse($joinDate, $timezone)->timezone('UTC');
        $this->startDate = Carbon::createFromDate($this->year, $this->month, 1, $timezone)->startOfMonth()->timezone('UTC');
        $this->endDate   = Carbon::createFromDate($this->year, $this->month, 1, $timezone)->endOfMonth()->timezone('UTC');

        $this->userTargetType = $this->getUserTargetType($user->id);
        $this->targetInstance = new RegularTarget();
    }

    private function getUserTargetType(int $userId): TargetType
    {
        return TargetType::REGULAR;
    }

    public function calculateTarget($month = null, $year = null): void
    {
        $month_received       = $this->user->getMonthlyDiamondReceived($month, $year);
        $this->user           = $this->calculateRegularTarget($month_received, $this->user);
        $this->user->salary_is_updated = false;
        $this->user->save();
    }

    public function calculateFixedTarget($month_received, User $user): User
    {
        if ($user->agency_id == 0) {
            return $user;
        }

        $fixedTarget = new FixedTargetClass();
        $times       = $this->getUserLiveTime($user);

        $hours = $times?->hnum ?? 0;
        $days  = $times ? $user->monthly_days : 0;

        $countMoments = Moment::where('user_id', $user->id)
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->count();

        $countReels = Real::where('user_id', $user->id)
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->count();

        $userTarget  = UserTarget::where('user_id', $user->id)->first();
        $target      = Target::find($userTarget->target_id);
        $nextTarget  = Target::where('level', $target->level + 1)->first();

        $output        = $fixedTarget->findClosestElement([
            "diamonds"     => $month_received,
            "hours"        => $hours,
            "days"         => $days,
            "count_moment" => $countMoments,
            "count_real"   => $countReels
        ]);

        $currentTarget = $output['currentElement'];
        $closeTarget   = $output['closestElement'];

        $t                = @$currentTarget->usd ?? 0;
        $ap               = (@$currentTarget->agency_share ?? 0) / 100;
        $user->target_usd = $t;

        $this->updateSalaries($user, $t, $ap, $hours, $currentTarget, $days, $month_received, $this->userTargetType, [
            "count_moment" => $countMoments . '/' . @$closeTarget->count_moment ?? 0,
            "count_real"   => $countReels . '/' . @$closeTarget->count_real ?? 0
        ]);

        return $user;
    }

    public function getUserLiveTime(User $user): null|Model
    {
        return LiveTime::where('uid', $user->id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('uid, sum(hours) as hnum, count(days) as dnum')
            ->groupBy('uid')
            ->first();
    }

    private function calculateUsdValues($target): array
    {
        return [
            'agency_usd'    => Common::getTargetUsd($target->diamonds, $target->agency_share),
            'app_profit_usd'=> Common::getTargetUsd($target->diamonds, $target->app_profit_percentage),
            'db_usd'        => Common::getTargetUsd($target->diamonds, $target->db_percentage),
        ];
    }

    private function prepareUserTargetValues(User $user, $t, $hours, $days, $month_received, $target, ?array $extra, array $usdValues, float $percentageAchieved, $nextTarget): array
    {
        $values = [
            'user_id'             => $user->id,
            'add_month'           => $this->month,
            'add_year'            => $this->year,
            'agency_id'           => $user->agency_id,
            'target_id'           => $target->id ?? null,
            'target_diamonds'     => $target->diamonds ?? 0,
            'target_usd'          => $target->usd ?? 0,
            'target_hours'        => $target->hours ?? 0,
            'target_days'         => $target->days ?? 0,
            'target_agency_share' => $target->agency_share ?? 0,
            'user_diamonds'       => $month_received,
            'user_hours'          => $hours,
            'user_days'           => $days,
            'extras'              => $extra ? json_encode($extra) : 0,
            'next_diamond'        => ($nextTarget->diamonds ?? 0) - $month_received,
        ];

        if ($t >= 0) {
            $values['user_obtain']   = $t;
            $values['agency_obtain'] = $usdValues['agency_usd'] * $percentageAchieved;
        }

        return $values;
    }

    private function prepareUserSalaryValues($t, $hours, $days, $month_received, $target, ?array $extra, array $usdValues, float $percentageAchieved): array
    {
        $values = [
            'agency_sallary'   => $usdValues['agency_usd'] * $percentageAchieved,
            'hours'            => $hours . ' / ' . ($target->hours ?? 0),
            'days'             => $days . ' / ' . ($target->days ?? 0),
            'diamond'          => $month_received . ' / ' . ($target->diamonds ?? 0),
            'target_id'        => $target->id ?? null,
            'target_diamonds'  => $target->diamonds ?? 0,
            'extras'           => $extra ? json_encode($extra) : 0,
            'app_profit'       => $usdValues['app_profit_usd'] * $percentageAchieved,
            'dB'               => $usdValues['db_usd'] * $percentageAchieved,
            'achieved_hours'   => $hours,
            'achieved_days'    => $days,
            'achieved_diamond' => $month_received,
        ];

        if ($t > 0) {
            $values['sallary'] = $t;
        }

        return $values;
    }

    private function updateSalaries(User &$user, $t, $ap, $hours, $target, $days, $month_received, TargetType $targetType, array $extra = null, $appProfit = 0, $db = 0, $percentageAchieved = 0): void
    {
        $usdValues   = $this->calculateUsdValues($target);
        $nextTarget  = Target::where('diamonds', '>', $month_received)->orderBy('diamonds')->first();

        try {
            $targetValues = $this->prepareUserTargetValues($user, $t, $hours, $days, $month_received, $target, $extra, $usdValues, $percentageAchieved, $nextTarget);
            UserTarget::updateOrCreate([
                'user_id'   => $user->id,
                'add_month' => $this->month,
                'add_year'  => $this->year,
                'type'      => $targetType,
            ], $targetValues)->lock('user-' . $user->id);
        } catch (\Exception $e) {
            Log::error("UserTarget update failed", ['error' => $e->getMessage()]);
        }

        $salaryValues = $this->prepareUserSalaryValues($t, $hours, $days, $month_received, $target, $extra, $usdValues, $percentageAchieved);

        $userSalary = UserSallary::where([
            'user_id'        => $user->id,
            'month'          => $this->month,
            'year'           => $this->year,
            'user_agency_id' => $user->agency_id,
            'is_finished'    => 0,
        ])->lock()->first();

        if ($userSalary) {
            $salaryValues['remaining_diamond'] = $month_received - ($target->diamonds ?? 0);
            $userSalary->update($salaryValues);
        } else {
            UserSallary::create([
                'user_id'          => $user->id,
                'month'            => $this->month,
                'year'             => $this->year,
                'user_agency_id'   => $user->agency_id,
                'remaining_diamond'=> $month_received - ($target->diamonds ?? 0),
                'target_id'        => $target->id ?? null,
                ...$salaryValues
            ])->lock();
        }
    }

    public function calculateRegularTarget($month_received, User $user): User
    {
        if ($user->agency_id == 0 || @$user->type_user == 3) {
            return $user;
        }

        $target = $this->targetInstance->getTarget($month_received);

        if ($target) {
            $times = $this->getUserLiveTime($user);
            $hours = $times?->hnum ?? 0;
            $days  = $times ? $user->monthly_days : 0;

            $targetReel   = explode(',', $target->reel);
            $targetMoment = explode(',', $target->moment);

            $startDate = $this->startDate > $this->joinDate ? $this->startDate : $this->joinDate;
            $extra     = UserCommon::UserStatistic($user->id, type: 1, startDate: $startDate, endDate: $this->endDate);

            $t   = $this->targetInstance->calculateUsdFromTarget($target, $hours, $days, $extra);
            $pct = $this->targetInstance->calculatePercentageAchieved($target, $hours, $days, $extra);

            $ap        = $target->agency_share / 100;
            $appProfit = $target->app_profit_percentage / 100;
            $db        = $target->db_percentage / 100;
            $user->target_usd = $t;

            $extras = [
                "moment" => [
                    "upload"   => ($extra['moment']['upload'] ?? 0)   . '/' . ($targetMoment[0] ?? 0),
                    "likes"    => ($extra['moment']['likes'] ?? 0)    . '/' . ($targetMoment[1] ?? 0),
                    "comments" => ($extra['moment']['comments'] ?? 0) . '/' . ($targetMoment[2] ?? 0),
                ],
                "reel" => [
                    "upload"   => ($extra['reel']['upload'] ?? 0)   . '/' . ($targetReel[0] ?? 0),
                    "likes"    => ($extra['reel']['likes'] ?? 0)    . '/' . ($targetReel[1] ?? 0),
                    "comments" => ($extra['reel']['comments'] ?? 0) . '/' . ($targetReel[2] ?? 0),
                ],
            ];

            $this->updateSalaries($user, $t, $ap, $hours, $target, $days, $month_received, $this->userTargetType, $extras, $appProfit, $db, $pct);
        } else {
            $times = $this->getUserLiveTime($user);
            $hours = $times?->hnum ?? 0;
            $days  = $times ? $user->monthly_days : 0;

            UserSallary::updateOrCreate(
                [
                    'user_id'        => $user->id,
                    'month'          => $this->month,
                    'year'           => $this->year,
                    'user_agency_id' => $user->agency_id,
                    'is_finished'    => 0
                ],
                [
                    'agency_sallary'   => 0,
                    'sallary'          => 0,
                    'achieved_hours'   => $hours,
                    'achieved_days'    => $days,
                    'achieved_diamond' => $month_received,
                    'app_profit'       => 0,
                    'dB'               => 0,
                    'diamond'          => $month_received . ' / 0',
                    'target_diamonds'  => 0,
                    'remaining_diamond'=> $month_received
                ]
            );
        }

        return $user;
    }
}
