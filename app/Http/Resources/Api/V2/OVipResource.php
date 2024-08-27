<?php

namespace App\Http\Resources\Api\V2;
use Illuminate\Http\Resources\Json\JsonResource;

class OVipResource extends JsonResource
{
    public function toArray($request)
    {
        $activePrivilegeIds = $this->privilegs->pluck('id')->toArray();
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
                $request->vipPrivileges->map(function ($p) use ($activePrivilegeIds) {
                    $priv = clone  $p;
                    $priv->item = $this->wares->where('type', $priv->type)->first();
                    $priv->level = $this->level;
                    $priv->active = in_array($priv->id, $activePrivilegeIds);
                    return $priv;
                })->sortByDesc('active')
            ),
        ];
    }
}