<?php

namespace Modules\CP\Transformers;

use App\Helpers\Common;
use App\Models\User;
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
        
        return [
            'id'        => $this->id,
            'level'     => $this->level_id,
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
