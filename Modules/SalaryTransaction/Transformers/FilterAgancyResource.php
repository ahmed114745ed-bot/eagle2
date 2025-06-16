<?php

namespace Modules\SalaryTransaction\Transformers;

use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Common;
use App\Models\GiftLog;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\AdminsAgencyResource;
use App\Http\Resources\Api\V1\ReceiverGiftLogResource;

class FilterAgancyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $type = '';
        // if (($this->Shipping_agency == 1) && ($this->Host_agency == 1)) {
        //     $type = 'hosts and shipping';
        // } elseif (($this->Shipping_agency == 0) && ($this->Host_agency == 1)) {
        //     $type = 'hosts';
        // } elseif (($this->Shipping_agency == 1) && ($this->Host_agency == 0)) {
        //     $type = 'shipping';
        // }
        if ($this->type == 1 ) {
            $type = 'hosts ';
         
        } elseif ($this->type == 2) {
            $type = 'shipping';
        }
        $year = request('year') ?? Carbon::now()->year;
        $month = request('month') ?? Carbon::now()->month;

        $giftLog = GiftLog::where('agency_id', $this->id)->selectRaw("SUM(giftPrice) as exp, receiver_id")
            ->with('receiver')->groupBy('receiver_id')->whereHas('receiver')->whereYear('created_at', $year)->whereMonth('created_at', $month)->orderByDesc('exp')->take(5)->get();
        return [
            'id' => $this->id,
            'name' => @$this->name,
            'image' => @$this->img,
            'total_members' => $this->mempers->count(),
            'members' => AgencyMemberResource::collection($this->mempers),
            'agency_type' =>  $type,
            'owner' => [
                'id' => $this->owner->id ?? 0,
                'uuid' => $this->owner->uuid ?? '',
                'name' => @$this->owner->name ?? '',
                'image' => @$this->owner->profile?->avatar ?? '',
            ],
            'admins' => AdminsAgencyResource::collection($this->admins),
            'star' => ReceiverGiftLogResource::collection($giftLog),
            'bio'               => $this->contents,
        ];
    }
}
