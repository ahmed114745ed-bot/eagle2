<?php

namespace Utd\Agency\Http\Resources;

use Carbon\Carbon;
use Utd\Agency\Facades\AgencyHelper;
use Utd\Agency\Contracts\ExternalModuleInterface;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AgencyDetailsResource extends JsonResource
{
    public function toArray($request)
    {
        $year = request('year') ?? Carbon::now()->year;
        $month = request('month') ?? Carbon::now()->month;
        $user = $request->user();

        $externalModule = app(ExternalModuleInterface::class);
        
        $giftLogModel = $externalModule->get('models.gift_log');
        
        $giftLog = $giftLogModel::where('agency_id', $this->id)
            ->selectRaw("SUM(giftPrice) as exp, receiver_id")
            ->with('receiver')
            ->groupBy('receiver_id')
            ->whereHas('receiver')
            ->orderByDesc('exp')
            ->take(3)
            ->get();
            
        $heroGiftLog = $giftLogModel::where('agency_id', $this->id)
            ->selectRaw("SUM(giftPrice) as exp, sender_id")
            ->with('sender')
            ->groupBy('sender_id')
            ->whereHas('sender')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderByDesc('exp')
            ->get();
            
        $admin = $this->admins()->take(3)->get();

        $adminUser = $user?->agencyUserJob;

        return [
            'id' => $this->id ?: 0,
            'name' => $this->name ?: '',
            'img' => $this->img ?: '',
            'bio'               => $this->contents,
            'owner' => $this->owner ? new MyDataForAgencyResource($this->owner) : [
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
            'star' => ReceiverGiftLogResource::collection($giftLog),
            'heroes' => SenderGiftLogResource::collection($heroGiftLog),
        ];
    }
}
