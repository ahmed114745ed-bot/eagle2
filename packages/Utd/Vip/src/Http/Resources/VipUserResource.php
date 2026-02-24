<?php

namespace Utd\Vip\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VipUserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'level' => $this->level ?? 0,
            'qty' => $this->qty,
            'expire' => $this->expire,
        ];
    }
}
