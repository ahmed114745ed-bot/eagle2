<?php

namespace App\Http\Resources\Api\V1;


use Illuminate\Http\Resources\Json\JsonResource;

class UserVipResource extends JsonResource
{

    public function toArray($request)
    {

        return [
            "target_id" => $this?->id,
            "is_buyed" => $this != null ? true : false,
            "is_used" => ($this != null && $this->is_used == 1 ? true : false),
            "using" => ($this != null && $this->using == 1 ? true : false),
            'expire' => $this->expire != 0 ? date("Y-m-d H:i:s", $this->expire) : 0,

        ];
    }
}
