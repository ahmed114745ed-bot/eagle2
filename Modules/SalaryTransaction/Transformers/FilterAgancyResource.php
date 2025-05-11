<?php

namespace Modules\SalaryTransaction\Transformers;

use App\Helpers\Common;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

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
        if (($this->Shipping_agency == 1) && ($this->Host_agency == 1)) {
            $type = 'hosts and shipping';
        } elseif (($this->Shipping_agency == 0) && ($this->Host_agency == 1)) {
            $type = 'hosts';
        } elseif (($this->Shipping_agency == 1) && ($this->Host_agency == 0)) {
            $type = 'shipping';
        }
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
            ]
        ];
    }
}
