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
        return [
            'id' => $this->id,
            'name'=>@$this->name,
            'image'=>@$this->img,
            'total_members'=>$this->mempers->count(),
        ];
    }
}
