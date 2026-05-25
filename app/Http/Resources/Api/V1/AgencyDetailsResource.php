<?php

namespace App\Http\Resources\Api\V1;

use Carbon\Carbon;
use App\Models\Agency;
use App\Models\Target;
use App\Models\GiftLog;
use App\Models\UserSallary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\MyDataForAgancyResource;

class AgencyDetailsResource extends JsonResource
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
        $user = $request->user();

        $giftLog = GiftLog::where('agency_id', $this->id)->selectRaw("SUM(giftPrice) as exp, receiver_id")
            ->with('receiver')->groupBy('receiver_id')->whereHas('receiver')->orderByDesc('exp')->take(3)->get();
        $heroGiftLog = GiftLog::where('agency_id', $this->id)->selectRaw("SUM(giftPrice) as exp, sender_id")
            ->with('sender')->groupBy('sender_id')->whereHas('sender')
            ->whereBetween('created_at', [
                Carbon::create($year, $month, 1)->startOfMonth(),
                Carbon::create($year, $month, 1)->endOfMonth(),
            ])
            ->orderByDesc('exp')->get();
        $admin = $this->admins()->take(3)->get();


        $adminUser = $user?->agencyUserJob;

        return [
            'id' => $this->id ?: 0,
            'name' => $this->name ?: '',
            'img' => $this->img ?: '',
            'bio'               => $this->contents,
            'owner' => new MyDataForAgancyResource($this->owner) ?: [
                "id" => 0,
                "uuid" => '',
                "target_usd" => 0,
                'name' => '',
                "profile" => [
                    "image" => ''
                ]
            ],
            'user_agency_status' =>  $this->app_owner_id == $user->id ? 2 : ($adminUser ? 1 : 3),
            'admins' => AdminsAgencyResource::collection($admin),
            //'members' => MyDataForAgancyNewResource::collection($this->mempers),
            'star' => ReceiverGiftLogResource::collection($giftLog),
            'heroes' => SenderGiftLogResource::collection($heroGiftLog),

        ];
    }
}
