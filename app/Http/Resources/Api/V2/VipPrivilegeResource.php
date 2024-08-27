<?php

namespace App\Http\Resources\Api\V2;

use App\Http\Resources\WareResource;
use Illuminate\Http\Resources\Json\JsonResource;

class VipPrivilegeResource extends JsonResource
{
    public function toArray($request)
    {
        $ware = $this->item;
        return [
            'id' => $this->id,
            'name' => app()->getLocale() == 'en' ? ($this->en_name ?? $this->name) : $this->name,
            'active' => $this->active,
            'type' => $this->type,
            "title" => $this->title,
            "img1" => $this->imag1,
            "img2" => $this->img2,
            'item' => $ware ? new WareResource($ware) : new \stdClass(),

        ];
    }
}
