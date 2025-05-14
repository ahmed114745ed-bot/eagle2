<?php

namespace App\Http\Resources\Api\V1;

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


        $target = $this->targets->first();
        // $userTarget = UserTarget::where('user_id', $this->id)->where('agency_id', $this->agency_id)->where('add_year', $year)->where('add_month', '<', $month)->orderByDesc('add_month')
        //     ->select('id', 'user_diamonds')->get();
        $months = collect(range($month - 3, $month - 1))->filter(fn($m) => $m > 0)->values();

        $userTargets = UserTarget::where('user_id', $this->id)
            ->where('agency_id', $this->agency_id)
            ->where('add_year', $year)
            ->whereIn('add_month', $months)
            ->select('add_month', 'user_diamonds')
            ->pluck('user_diamonds', 'add_month');

        $giftLog = GiftLog::where('agency_id', $this->agency_id)->where('receiver_id', $this->id)->whereHas('sender')->with('sender')->whereYear('created_at', $year)->whereMonth('created_at', $month)
            ->selectRaw("sum(giftPrice) as exp, sender_id")
            ->groupBy('sender_id')->orderByRaw("exp desc")->limit(3)
            ->get()->reject(function ($q) {
                return $q->exp == 0;
            });


        $salary = UserSallary::query()->where('user_id', $this->id)
            ->where(function ($query) use ($year, $month) {
                $query->where(DB::raw('concat(year,"-", month)'), '==', $year . '-' . $month);
            })->sum(DB::raw('sallary - cut_amount'));

       
        return [
            'id' => $this->id ?? 0,

            'id' => $this->id ?? 0,
            'name' => $this->name ?? '',
            'uuid' => $this->uuid ?? '',
            'image' => $this->profile->avatar ?? '',
            'is_host' => $this->is_host,
            'salary'  => $salary ?? 0,
           
            'target' => [
                'id' => @$target->target_id ?? 0,
                'user_diamonds' => @$target->user_diamonds ?? 0,
                'user_hours' => @$target->user_hours ?? 0,
                'user_days' => @$target->user_days ?? 0,
                'diamonds_next_target'   => @$target?->next_diamond ?? 0,
                'old_targets'  => $userTargets,
            ],
            'sender_gifts' => SenderGiftLogResource::collection($giftLog),
        ];
    }
}
