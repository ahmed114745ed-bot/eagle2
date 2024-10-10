<?php

namespace App\Http\Resources\Api\V1;
use App\Http\Resources\WareResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivilegeResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'name' => app()->getLocale() == 'en' ? ($this->en_name ?? $this->name) : $this->name,
            'active' => $this->active,
            'type' => $this->type,
            "title"=> $this->title,
            "img1"=> $this->imag1,
            "img2"=> $this->img2,
        ];
    }
}
