<?php

namespace App\Http\Resources\Api\V1;

use App\Models\UsersJoinedAgency;
use Carbon\Carbon;
use App\Models\GiftLog;
use App\Models\LiveTime;
use App\Models\UserTarget;
use App\Models\UserSallary;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class AgencyUsersTargetResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $year = request('year') ?? Carbon::now()->year;
        $month = request('month') ?? Carbon::now()->month;

        $endOfMonth = Carbon::create($year, $month)->endOfMonth();

        $joinRecord = UsersJoinedAgency::where('user_id', $this->id)
        ->where('agency_id', $this->agency_id)
        ->whereMonth('join_date', $month)
        ->whereYear('join_date', $year)
        ->latest('join_date')
        ->first();
    
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = Carbon::create($year, $month)->endOfMonth()->endOfDay();
        
        $joinedDate = $joinRecord ? Carbon::parse($joinRecord->join_date)->startOfDay() : $startOfMonth;
        $leaveDate = $joinRecord && $joinRecord->leave_date
            ? Carbon::parse($joinRecord->leave_date)->endOfDay()
            : $endOfMonth;
        
        $from = $joinedDate->greaterThan($startOfMonth) ? $joinedDate : $startOfMonth;
        $to = $leaveDate->lessThan($endOfMonth) ? $leaveDate : $endOfMonth;
    
        $totalSeconds = $this->liveTime()
        ->whereBetween('created_at', [$from, $to])
        ->get()
            ->reduce(function ($carry, $session) {
                $start = is_numeric($session->start_time)
                    ? Carbon::createFromTimestamp($session->start_time)
                    : Carbon::parse($session->start_time);
        
                $end = is_numeric($session->end_time)
                    ? Carbon::createFromTimestamp($session->end_time)
                    : Carbon::parse($session->end_time);
        
                return $carry + $end->diffInSeconds($start);
            }, 0);
        
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);

        $target = $this->targets()
        ->where('agency_id', $this->agency_id)
        ->latest()
        ->first();

        // $userTarget = UserTarget::where('user_id', $this->id)->where('agency_id', $this->agency_id)->where('add_year', $year)->where('add_month', '<', $month)->orderByDesc('add_month')
        //     ->select('id', 'user_diamonds')->get();
        // $months = collect(range($month - 2, $month - 1))
        // ->filter(fn($m) => $m > 0)
        // ->values();

        // $userTargets = UserTarget::where('user_id', $this->id)
        //     ->where('agency_id', $this->agency_id)
        //     ->where('add_year', $year)
        //     ->whereIn('add_month', $months)
        //     ->select('add_month', 'user_diamonds')
        //     ->pluck('user_diamonds', 'add_month');

        // $result = [];

        // foreach ($months as $m) {
        //     $result[] = [
        //         'month_number' => $m,
        //         'diamonds'     => $userTargets->get($m, 0),
        //     ];
        // }
        $currentDate = Carbon::create($year, $month, 1);

            $monthsWithYears = collect();

            for ($i = 2; $i >= 0; $i--) {
                $date = $currentDate->copy()->subMonths($i);
                $monthsWithYears->push([
                    'year' => $date->year,
                    'month' => $date->month,
                ]);
            }

            $userTargets = UserTarget::where('user_id', $this->id)
                ->where('agency_id', $this->agency_id)
                ->where(function($query) use ($monthsWithYears) {
                    foreach ($monthsWithYears as $item) {
                        $query->orWhere(function($q) use ($item) {
                            $q->where('add_year', $item['year'])
                            ->where('add_month', $item['month']);
                        });
                    }
                })
                ->select('add_year', 'add_month', 'user_diamonds')
                ->get()
                ->keyBy(function($item) {
                    return $item->add_year . '-' . $item->add_month;
                });

            $result = [];

            foreach ($monthsWithYears as $item) {
                $key = $item['year'] . '-' . $item['month'];
                $result[] = [
                    // 'year' => $item['year'],
                    'month_number' => $item['month'],
                    'diamonds' => $userTargets->has($key) ? $userTargets->get($key)->user_diamonds : 0,
                ];
            }
        $giftLog = GiftLog::where('agency_id', $this->agency_id)->where('receiver_id', $this->id)->whereHas('sender')->with('sender')->whereYear('created_at', $year)->whereMonth('created_at', $month)
            ->selectRaw("sum(giftPrice) as exp, sender_id")
            ->groupBy('sender_id')->orderByRaw("exp desc")->limit(3)
            ->get()
            ->filter(function ($q) {
                return $q->exp > 0;
            });
     

        // $salary = UserSallary::query()

        //     ->where('user_agency_id', $this->agency_id)
        //     ->where('user_id', $this->id)
        //     ->where(function ($query) use ($year, $month) {
        //         $query->where(DB::raw('concat(year,"-", month)'), '=', $year . '-' . $month);
        //     })->sum(DB::raw('sallary - cut_amount'));
        $agencySallary = UserSallary::query()
        ->where('user_id', $this->id)
        ->where('user_agency_id', $this->agency_id)
        ->where(function ($query) use ($year, $month) {
            $query->where(DB::raw('concat(year,"-", month)'), '=', $year . '-' . $month);
        })
        ->value('agency_sallary');

        return [
            'id' => $this->id ?? 0,
            'name' => $this->name ?? '',
            'uuid' => $this->uuid ?? '',
            'image' => $this->profile->avatar ?? '',
            'is_host' => $this->is_host,
            'salary' => (float) $agencySallary ?? 0,
            'target' => [
                'id' => @$target->target_id ?? 0,
                'user_diamonds' => @$this->monthly_diamond_received ?? 0,
                'user_hours' => @$hours ?? 0,
                'user_days' => @$this->getTotalDays() ?? 0,
                // 'diamonds_next_target'   => @$target?->next_diamond ?? 0,
                'old_targets'  => $result,
            ],
            // 'top_users' => SenderGiftLogResource::collection($giftLog),
           'top_users' => $giftLog->map(function ($log) {
                return $log->sender?->profile?->avatar ?? '';
            })->filter()->values()->toArray(),
        ];
    }
}
