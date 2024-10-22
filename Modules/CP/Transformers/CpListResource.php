<?php

namespace Modules\CP\Transformers;

use App\Helpers\Common;
use App\Models\User;
use App\Models\Vip;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CpListResource extends JsonResource
{
    public function toArray($request)
    {
        $loginUserId = Auth::id() ;
        if ($this->user_one_id == $loginUserId) {
            $user = $this->toUser;
        }else{
            $user = $this->fromUser;
        }

        $nextLevel = Vip::where("level",">",$this->level_id)->where("type",3)->first();
        if ($nextLevel) {
            $nextLevelPercentage =($this->di * 100 / $nextLevel->exp ) ;
        }else{
            $nextLevelPercentage = 0;
        }
        return [
            'id'        => $this->id,
            'level'     => $this->level_id,
            'next_level'     => $nextLevelPercentage,
            'di'        => $this->di,
            "user"      =>[
                "id"        => $user?->id,
                "uid"       => $user?->uuid,
                "name"      => $user?->name,
                "image"     => $user?->avatar,
                "gender"    => $user?->gender,
            ],
            "relation" => $this->relation
        ];
    }
}
