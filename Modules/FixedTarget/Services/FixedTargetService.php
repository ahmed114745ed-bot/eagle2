<?php

namespace Modules\FixedTarget\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Agency;
use App\Models\Target;
use App\Models\LiveTime;
use App\Models\UserTarget;
use App\Helpers\UserCommon;
use App\Models\PeriodTarget;
use App\Models\UserSallary;
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

    public function __construct(private User $user)
    {
        $targetType           = $this->getUserTargetType($user->id);
        $this->userTargetType = $targetType;
        $this->targetInstance = new RegularTarget();
    }

    private function getUserTargetType(int $userId): TargetType
    {
        $isSpecial = SpecialUser::query()->where('user_id', $userId)->where('status', true)->exists();
        return $isSpecial ? TargetType::FIXED : TargetType::REGULAR;
    }

    public function calculateTarget()
    {
        $user           = $this->user;
        $month_received = $user->monthly_diamond_received;
        /*     $agency=Agency::find($user->agency_id);
        if ($this->userTargetType == TargetType::FIXED) {
            if ($agency->users->where("type_user",1)->sum("monthly_diamond_received") >= $agency->monthly_target) {
                $user = $this->calculateFixedTarget($month_received, $user);
            }
        } else {*/
        $user = $this->calculateRegularTarget($month_received, $user);
        //        }
        $user->salary_is_updated = false;
        $user->save();
    }

    /**
     * @param $month_received
     * @param User $user
     * @return User
     */
    public function calculateFixedTarget($month_received, User $user): User
    {

        if ($user->agency_id != 0) {
            $fixedTarget = new FixedTargetClass();
            //            $target = $this->targetInstance->getTarget($month_received);

            //            if ($target) {
            $hours = 0;
            $days  = 0;
            $times = $this->getUserLiveTime($user);
            if ($times) {
                $hours = $times->hnum;
                $days  = $user->monthly_days;
            }

            $countMoments  = Moment::query()->where('user_id', $user->id)->whereBetween('created_at', [
                Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()
            ])->count();

            $countReels         = Real::query()->where('user_id', $user->id)->whereBetween('created_at', [
                Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()
            ])->count();


            $userTarget = UserTarget::where('user_id', $user->id)->first();
            $target = Target::find($userTarget->target_id);
            $nextTarget = Target::where('level', $target->level + 1)->first();

            $input         = [
                "diamonds"     => $month_received,
                "hours"        => $hours,
                "days"         => $days,
                "count_moment" => $countMoments,
                "count_real"   => $countReels
            ];
            $output        = $fixedTarget->findClosestElement($input);
            $currentTarget = $output['currentElement'];
            $closeTarget = $output['closestElement'];

            //            if ($currentTarget) {
            $t                = @$currentTarget->usd ?? 0;
            $ap               = (@$currentTarget->agency_share ?? 0) / 100;
            $user->target_usd = $t;
            $this->updateSalaries($user, $t, $ap, $hours, $currentTarget, $days, $month_received, $this->userTargetType, [
                "count_moment" => $countMoments . '/' . @$closeTarget->count_moment ?? 0,
                "count_real"   => $countReels . '/' . @$closeTarget->count_real ?? 0
            ]);
            //            }
            //            }
        }
        return $user;
    }

    /**
     * @param User $user
     * @returns ['uid', 'hnum', 'dnum'] or null
     * @return Model | null
     */
    public function getUserLiveTime(User $user): null|Model
    {
        $period = UserCommon::getPeriodTarget();
        return LiveTime::query()->where('uid', $user->id)->whereDate('created_at', '>=', $period['start_at'])->whereDate('created_at', '<=', $period['end_at'])->selectRaw('uid, sum(hours) as hnum, count(days) as dnum')->groupBy('uid')->first();
    }

    private function updateSalaries(User &$user, $t, $ap, $hours, $target, $days, $month_received, TargetType $targetType, array $extra = null): void
    {
        try {
            $values = [
                'user_id'             => $user->id, 'add_month' => Carbon::now()->month,
                'add_year'            => Carbon::now()->year,
                'agency_id'           => $user->agency_id, 'target_id' => @$target->id,
                'target_diamonds'     => @$target->diamonds ?? 0, 'target_usd' => @$target->usd ?? 0,
                'target_hours'        => @$target->hours ?? 0,
                'target_days'         => @$target->days ?? 0,
                'target_agency_share' => @$target->agency_share ?? 0,
                'user_diamonds'       => $month_received,
                'user_hours'          => $hours,
                'user_days'           => $days,
                'extras'              => $extra !== null ? json_encode($extra) : 0,

            ];
            if (0.0 <= $t) {
                $values['user_obtain']   = $t;
                $values['agency_obtain'] = $t * $ap;
            }
            UserTarget::query()->updateOrCreate([
                                                    'user_id' => $user->id, 'add_month' => Carbon::now()->month,
                                                                                                                                                                                                                   'add_year' => Carbon::now()->year,
                                                    'type'    => $targetType,
                                                ], $values)->lock('user-'.$user->id);
        } catch (\Exception $e) {
        }

        $values = [
            'agency_sallary' => $t * $ap,  'hours' => $hours . ' / ' . (@$target->hours ?? 0),
            'days' => $days . ' / ' . ($target->days ?? 0),
            'diamond' => $month_received . ' / ' . @$target->diamonds ?? 0,
            'extras'               => $extra !==  null ? json_encode($extra) : 0,
        ];
        if (0 < $t) $values['sallary'] = $t;
        $period = UserCommon::getPeriodTarget();
        $userSalary = UserSallary::query()->whereHas('period_target',function($q) use ($period){
            $q->whereDate("created_at",">=",$period['start_at'])->whereDate("created_at","<=",$period['end_at']);
        })->where([
                                                      'user_id' => $user->id,
                                                      'user_agency_id' => $user->agency_id,
                                                  ])->lock()->first();
        if ($userSalary){
            $userSalary->update($values);
        }else{
            $period = PeriodTarget::latest()->first();
            $userSalary = UserSallary::query()->create([
                                                           'user_id' => $user->id,
                                                           'month' => $period->id,
                                                           'year' => 0,
                                                           'user_agency_id' => $user->agency_id,
                                                           ...$values
                                                       ])->lock();
           /* UserSallary::query()->where([
                                            'user_id' => $user->id,
                                            'month' => Carbon::now()->month,
                                            'year' => Carbon::now()->year,
                                            'user_agency_id' => $user->agency_id,

                                        ])->where('id','!=', $userSalary->id)->delete();*/

        }
    }

    /**
     * @param $month_received
     * @param User $user
     * @return User
     */
    public function calculateRegularTarget($month_received, User $user): User
    {
        if ($user->agency_id != 0 && @$user->type_user != 3) {
            $target = $this->targetInstance->getTarget($month_received);

            if ($target) {
                $hours = 0;
                $days  = 0;
                $times = $this->getUserLiveTime($user);
                if ($times) {
                    $hours = $times->hnum;
                    $days  = $user->monthly_days;
                }

                $targetReel  = explode(' ', $target->reel);
                $targetMoment = explode(' ', $target->moment);


                $extra = UserCommon::UserStatistic($user->id, 1);


                $t                = $this->targetInstance->calculateUsdFromTarget($target, $hours ?? 0, $days, $extra);
                $ap               = $target->agency_share / 100;
                $user->target_usd = $t;
                $extras = [

                    "moment" => [
                        "upload" => $extra['moment']['upload'] . '/' . @$targetMoment[0] ?? 0,
                        "likes" => $extra['moment']['likes'] . '/' . @$targetMoment[1] ?? 0,
                        "comments" => $extra['moment']['comments'] . '/' . @$targetMoment[2] ?? 0,
                    ],
                    "reel" => [
                        "upload" => $extra['reel']['upload'] . '/' . @$targetReel[0] ?? 0,
                        "likes" =>  $extra['reel']['likes'] . '/' . @$targetReel[1] ?? 0,
                        "comments" => $extra['reel']['comments'] . '/' . @$targetReel[2] ?? 0,

                    ],

                ];



                $this->updateSalaries($user, $t, $ap, $hours, $target, $days, $month_received, $this->userTargetType, $extras);
            }
        }
        return $user;
    }
}
