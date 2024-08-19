<?php

namespace App\Http\Resources\Api\V2;
use App\Http\Resources\WareResource;
use Illuminate\Http\Resources\Json\JsonResource;

class VipPrivilegeResource extends JsonResource
{
    public function toArray($request)
    {
        $mp = $this->oVipPrivilegIds;
        $ware = $this->wares->where('level', $this->level)
                            ->where('type', $this->type)
                            ->first() ??
                $this->wares->where('level', 8)
                            ->where('type', $this->type)
                            ->first();

        return [
            'id' => $this->id,
            'name' => app()->getLocale() == 'en' ? ($this->en_name ?? $this->name) : $this->name,
            'active' => in_array($this->id, $mp),
            'item' => $ware ? new WareResource($ware) : new \stdClass(),
            'type' => $this->type,
        ];
    }
}