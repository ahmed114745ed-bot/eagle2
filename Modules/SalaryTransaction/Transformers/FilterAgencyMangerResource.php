<?php

namespace Modules\SalaryTransaction\Transformers;

use App\Helpers\Common;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class FilterAgencyMangerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'agency_id' => (string)$this->agency_id ?? '0',
            'uuid' => $this->uuid,
            'name'=>@$this->name,
            'image'=>@$this->profile?->avatar,
            'total_member'=>$this->ownAgency?->mempers->count(),
        ];
    }
}
