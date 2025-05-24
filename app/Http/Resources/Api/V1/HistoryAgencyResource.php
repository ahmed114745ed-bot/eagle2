<?php

namespace App\Http\Resources\Api\V1;

use Carbon\Carbon;
use App\Models\Agency;
use App\Models\Target;
use App\Models\GiftLog;
use App\Models\UserSallary;
use App\Models\AgencySallary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\MyDataForAgancyResource;
use App\Models\UserTarget;

class HistoryAgencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */



    public function toArray($request)
    {



        $year = request('year') ?? Carbon::now()->year;
        $month = request('month') ?? Carbon::now()->month;


        $giftLog = GiftLog::where('agency_id', $this->id)->selectRaw("SUM(giftPrice) as exp, receiver_id")
            ->with('receiver')->groupBy('receiver_id')->whereHas('receiver')->whereYear('created_at', $year)->whereMonth('created_at', $month)->orderByDesc('exp')->take(3)->get();
        $heroGiftLog = GiftLog::where('agency_id', $this->id)->selectRaw("SUM(giftPrice) as exp, sender_id")
            ->with('sender')->groupBy('sender_id')->whereHas('sender')->whereYear('created_at', $year)->whereMonth('created_at', $month)->orderByDesc('exp')->take(3)->get();
        $salary = AgencySallary::where('agency_id', $this->id)->where('year', $year)->where('month', $month)->sum('sallary');
        $target = $this->AgencyUsersTargets()->where('add_year', $year)->where('add_month', $month)->sum('target_diamonds');

        return [
            'star' => ReceiverGiftLogResource::collection($giftLog),
            'heroes' => SenderGiftLogResource::collection($heroGiftLog),
            'salary' => $salary,
            'target' =>  $target,

        ];
    }
}
