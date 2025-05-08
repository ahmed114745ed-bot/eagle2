<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\MyDataForAgancyResource;
use App\Models\Agency;
use App\Models\GiftLog;
use App\Models\Target;
use App\Models\UserSallary;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
        $giftLog = GiftLog::where('agency_id', $this->id)->selectRaw("SUM(giftPrice) as exp, receiver_id")
            ->with('receiver')->groupBy('receiver_id')->whereHas('receiver')->orderByDesc('exp')->get();

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
            'admins' => AdminsAgencyResource::collection($this->admins),
            //'members' => MyDataForAgancyNewResource::collection($this->mempers),
            'star' => ReceiverGiftLogResource::collection($giftLog),

        ];
    }
}
