<?php

namespace App\Http\Resources\Api\V1;
use Illuminate\Http\Resources\Json\JsonResource;

class OVipResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            "sort"=> $this->sort,
            "name"=> $this->name,
            "img"=> $this->img,
            "price"=> $this->price,
            "expire"=> $this->expire,
            "exp"=> $this->exp,
            'privilegs' => VipPrivilegeResource::collection(
                $this->privilegs->map(function ($priv) {
                    $priv->oVipPrivilegIds = $this->privilegs->pluck('id')->toArray();
                    $priv->wares = $this->wares;
                    $priv->level = $this->level;
                    return $priv;
                })->sortByDesc('active')
            ),
        ];
    }
}