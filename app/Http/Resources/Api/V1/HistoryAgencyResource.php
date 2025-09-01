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
            ->with('receiver')->groupBy('receiver_id')->whereHas('receiver')->whereYear('created_at', $year)->whereMonth('created_at', $month)->having('exp', '>', 0)->orderByDesc('exp')->take(3)->get();
        // $heroGiftLog = GiftLog::where('agency_id', $this->id)->selectRaw("SUM(giftPrice) as exp, sender_id ,is_finished")
        //     ->with('sender')->groupBy('sender_id')->whereHas('sender')->whereYear('created_at', $year)->whereMonth('created_at', $month)->where('is_finished', 0)->orderByDesc('exp')->take(3)->get();
        $heroGiftLog = GiftLog::where('agency_id', $this->id)
        ->selectRaw("SUM(giftPrice) as exp, sender_id, is_finished")
        ->with('sender')
        ->whereHas('sender')
        ->whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->where('is_finished', 0) 
        ->groupBy('sender_id', 'is_finished')
        ->orderByDesc('exp')
        ->take(3)
        ->get();
        
        $salary = 0;
        $target = 0;

        $isOwner = Auth::user()->id == $this->app_owner_id;
        if ($isOwner) {
            $salary = AgencySallary::where('agency_id', $this->id)
                ->where('year', $year)
                ->where('month', $month)
                ->sum('sallary');
            $target = $heroGiftLog->where('is_finished', 0)->sum('exp');
        }


        return [
            'star' => ReceiverGiftLogResource::collection($giftLog),
            'heroes' => SenderGiftLogResource::collection($heroGiftLog),
            'salary' => $isOwner ? (string)$salary : '0',
            'target' => $isOwner ? $target : 0,
           

        ];
    }
}
