<?php

namespace App\Http\Resources\Api\V1;

use App\Models\UserVip;
use Illuminate\Http\Resources\Json\JsonResource;

class OVipResource extends JsonResource
{

    public function toArray($request)
    {
        $userVip = UserVip::where("user_id",auth()->user()->id)
            ->where("vip_id",$this->id)
            ->where(fn($q) => $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp))
            ->first();

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
            "target_id"=> $userVip?->id ,
            "is_buyed"=> $userVip != null ? true : false,
            "is_used"=> ($userVip != null && $userVip->is_used == 1 ? true : false),
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
