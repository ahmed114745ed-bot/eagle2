<?php

namespace Modules\FixedTarget\Services;

use App\Helpers\Common;
use App\Models\BDSallary;
use App\Models\GiftLog;
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

class FixedTargetV2Service
{

    private TargetInterface $targetInstance;
    private TargetType $userTargetType;

    private \DateTime $startDate;
    private \DateTime $joinDate;

    private \DateTime $endDate;

    public function __construct(private User $user, private? int $month = null, private? int $year = null)
    {
        $timezone = getTimezone();

        // جلب تاريخ انضمام المستخدم
        $joinDate = UsersJoinedAgency::where('user_id', $user->id)
            ->where('agency_id', $user->agency_id)
            ->latest()
            ->value('join_date');
    
        $this->joinDate = Carbon::parse($joinDate, $timezone)->timezone('UTC');
    
        // استخدام التاريخ الحالي كافتراضي إذا لم يُرسل شهر وسنة
        $now = Carbon::now($timezone);
        $inputMonth = $this->month ?? $now->month;
        $inputYear  = $this->year  ?? $now->year;
    
        // بناء startDate من الشهر والسنة المدخلة
        $inputStartDate = Carbon::createFromDate($inputYear, $inputMonth, 1, $timezone)->startOfMonth()->timezone('UTC');
    
        // تحقق: هل التاريخ المدخل قبل تاريخ الانضمام؟
        if ($inputStartDate->lt($this->joinDate)) {
            // إذا كان التاريخ أقدم من الانضمام، استخدم شهر الانضمام
            $this->month = $this->joinDate->month;
            $this->year  = $this->joinDate->year;
            $this->startDate = $this->joinDate->copy()->startOfMonth();
            $this->endDate   = $this->joinDate->copy()->endOfMonth();
        } else {
            // إذا كان التاريخ صالحًا، استخدمه
            $this->month = $inputMonth;
            $this->year  = $inputYear;
            $this->startDate = $inputStartDate->copy()->startOfMonth();
            $this->endDate   = $inputStartDate->copy()->endOfMonth();
        }
    
        $this->userTargetType = $this->getUserTargetType($user->id);
        $this->targetInstance = new RegularTarget();
  
    }

    private function getUserTargetType(int $userId): TargetType
    {

        return TargetType::REGULAR;
    }

    public function calculateTarget()
    {
        $user           = $this->user;
        // $month_received = $user->monthly_diamond_received;
        /*     $agency=Agency::find($user->agency_id);
        if ($this->userTargetType == TargetType::FIXED) {
            if ($agency->users->where("type_user",1)->sum("monthly_diamond_received") >= $agency->monthly_target) {
                $user = $this->calculateFixedTarget($month_received, $user);
            }
        } else {*/
       
            $month_received = GiftLog::where('receiver_id', $user->id)->whereBetween('created_at', [$this->startDate, $this->endDate])->sum('giftPrice');

            $user = $this->calculateRegularTarget($month_received, $user);

        //  }
       
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
                // Carbon::now()->startOfMonth(),
                // Carbon::now()->endOfMonth()
                $this->startDate,
                $this->endDate
            ])->count();

            $countReels         = Real::query()->where('user_id', $user->id)->whereBetween('created_at', [
                // Carbon::now()->startOfMonth(),
                // Carbon::now()->endOfMonth()
                $this->startDate,
                $this->endDate
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
        return LiveTime::query()->where('uid', $user->id)->whereBetween('created_at', [$this->startDate, $this->endDate])->selectRaw('uid, sum(hours) as hnum, count(days) as dnum')->groupBy('uid')->first();
    }

    private function updateSalaries(User &$user, $t, $ap, $hours, $target, $days, $month_received, TargetType $targetType, array $extra = null, $appProfit, $db, $percentageAchieved = 0): void
    {
        $agency_usd              = Common::getTargetUsd($target->diamonds, $target->agency_share);
        $app_profit_usd               = Common::getTargetUsd($target->diamonds, $target->app_profit_percentage);
        $db_usd         = Common::getTargetUsd($target->diamonds, $target->db_percentage);

        $next_target = Target::where('diamonds', '>', $month_received)->orderBy('diamonds')->first();
        // WalletService::storeTransaction(
        //     $user->id,
        //     'add',
        //     $t,
        //     'user_transaction',
        //     'target_achieved',
        //     ['target_id' => $target->id],
        //     'get_target'
        // );
        \Log::info('updateSalaries',['test'=>$user->id ,'$this->month' => $this->month,'$t'=>$t]);

        // logger('agency_usd Achieved:', [$agency_usd]);
        // logger('percentageAchieved Achieved:', [$percentageAchieved]);
        // logger(' Achieved:', [$agency_usd * $percentageAchieved]);
        // logger(' Achieved: user', [ $t]);

        try {
            $values = [
                'user_id'             => $user->id,
                'add_month'           => $this->month,
                'add_year'            => $this->year,
                'agency_id'           => $user->agency_id,
                'target_id' => @$target->id,
                'target_diamonds'     => @$target->diamonds ?? 0,
                'target_usd' => @$target->usd ?? 0,
                'target_hours'        => @$target->hours ?? 0,
                'target_days'         => @$target->days ?? 0,
                'target_agency_share' => @$target->agency_share ?? 0,
                'user_diamonds'       => $month_received,
                'user_hours'          => $hours,
                'user_days'           => $days,
                'extras'              => $extra !== null ? json_encode($extra) : 0,
                'next_diamond'        => $next_target->diamonds - $month_received,


            ];
            if (0.0 <= $t) {
                $values['user_obtain']   = $t;
                $values['agency_obtain'] = $agency_usd * $percentageAchieved;
            }
            UserTarget::query()->updateOrCreate([
                'user_id' => $user->id,
                'add_month' => $this->month,
                'add_year' => $this->year,
                'type'    => $targetType,
            ], $values)->lock('user-' . $user->id);
        } catch (\Exception $e) {
        }

        $values = [
            'agency_sallary' => $agency_usd * $percentageAchieved,
            'hours' => $hours . ' / ' . (@$target->hours ?? 0),
            'days' => $days . ' / ' . ($target->days ?? 0),
            'diamond' => $month_received . ' / ' . @$target->diamonds ?? 0,
            'target_id' =>  @$target->id,
            'target_diamonds'     => @$target->diamonds ?? 0,
            'extras'               => $extra !==  null ? json_encode($extra) : 0,
            'app_profit' => $app_profit_usd * $percentageAchieved,
            'dB' =>  $db_usd * $percentageAchieved,
            'achieved_hours' =>   $hours ?? 0,
            'achieved_days' =>  $days ?? 0,
            'achieved_diamond' =>  $month_received ?? 0,
        ];
        if (0 < $t) $values['sallary'] = $t;

        $userSalary = UserSallary::query()->where([
            'user_id' => $user->id,
            'month' => $this->month,
            'year' => $this->year,
            'user_agency_id' => $user->agency_id,
        ])->lock()->first();
        if ($userSalary) {
            $values['remaining_diamond'] =  ($month_received - (@$target->diamonds ?? 0));
            $userSalary->update($values);
        } else {
            $userSalary = UserSallary::query()->create([
                'user_id' => $user->id,
                'month' => $this->month,
                'year' => $this->year,
                'user_agency_id' => $user->agency_id,
                'remaining_diamond'   => ($month_received - (@$target->diamonds ?? 0)),
                'target_id' =>  @$target->id,
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
        // \Log::info('$$user->agency_id ',['$$user->agency_id '=>$user->agency_id ]);
      
        if ($user->agency_id != 0 && @$user->type_user != 3) {
            $target = $this->targetInstance->getTarget($month_received);
            
            // \Log::info('$this->joinDate',['$this->joinDate'=>$this->joinDate]);
            if ($target) {
                $hours = 0;
                $days  = 0;
                $startDate = $this->startDate > $this->joinDate ? $this->startDate : $this->joinDate;

                $times = $this->getUserLiveTime($user);
                if ($times) {
                    
                    $hours = $times->hnum;
                    // $days  = $user->monthly_days;
                    $days  = $user->getTotalDaysJoinedAgencyByMonth($startDate,$this->endDate,$this->year);
                }


                $targetReel  = explode(',', $target->reel);
                $targetMoment = explode(',', $target->moment);
             

                $extra = UserCommon::UserStatistic($user->id, type: 1, startDate: $startDate, endDate: $this->endDate);


                $t                = $this->targetInstance->calculateUsdFromTarget($target, $hours ?? 0, $days, $extra);
                $percentageAchieved  = $this->targetInstance->calculatePercentageAchieved($target, $hours ?? 0, $days, $extra);;
                $ap               = $target->agency_share / 100;
                $appProfit        = $target->app_profit_percentage / 100;
                $db               = $target->db_percentage / 100;
                $user->target_usd = $t;
                // logger('t:', [$t]);
                // logger('Percentage Achieved:', [$percentageAchieved]);
                // logger('target_usd Achieved:', [$user->target_usd]);

                $extras = [
                    "moment" => [
                        "upload" => (isset($extra['moment']['upload']) ? $extra['moment']['upload'] : 0) . '/' . (@$targetMoment[0] ?? 0),
                        "likes" => (isset($extra['moment']['likes']) ? $extra['moment']['likes'] : 0) . '/' . (@$targetMoment[1] ?? 0),
                        "comments" => (isset($extra['moment']['comments']) ? $extra['moment']['comments'] : 0) . '/' . (@$targetMoment[2] ?? 0),
                    ],
                    "reel" => [
                        "upload" => (isset($extra['reel']['upload']) ? $extra['reel']['upload'] : 0) . '/' . (@$targetReel[0] ?? 0),
                        "likes" => (isset($extra['reel']['likes']) ? $extra['reel']['likes'] : 0) . '/' . (@$targetReel[1] ?? 0),
                        "comments" => (isset($extra['reel']['comments']) ? $extra['reel']['comments'] : 0) . '/' . (@$targetReel[2] ?? 0),
                    ],
                ];



                $this->updateSalaries($user, $t, $ap, $hours, $target, $days, $month_received, $this->userTargetType, $extras, $appProfit, $db, $percentageAchieved);
            }else{
                 $hours = 0;
                $days  = 0;
                $times = $this->getUserLiveTime($user);
                if ($times) {
                    $hours = $times->hnum;
                    $days  = $user->monthly_days;
                }
                 UserSallary::updateOrCreate(
                    [
                        'user_id' => $user->id ,
                         'month' => $this->month,
                        'year' => $this->year,
                        'user_agency_id' => $user->agency_id,
                    ],
                    [
                        'agency_sallary' => 0,
                        'sallary' => 0,
                        'achieved_hours' =>   $hours,
                        'achieved_days' =>  $days,
                        'achieved_diamond' =>  $month_received,
                        'app_profit' => 0,
                        'dB' =>  0,
                        'diamond' => $month_received . ' / ' . 0,
                        'target_diamonds'     => 0,
                        'remaining_diamond'   => ($month_received -  0),
                    ]
                );
            }
        }
        return $user;
    }
}
