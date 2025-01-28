<?php

namespace Modules\CP\Transformers;

use App\Helpers\Common;
use App\Models\User;
use App\Models\Vip;
use App\Models\Ware;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\CP\Entities\Cp;
use Modules\CP\Entities\CpLevel;

class CpListResource extends JsonResource
{
    public function toArray($request)
    {
        $loginUserId = request('user_id') ?? Auth::id();
        if ($this->user_one_id == $loginUserId) {
            $user = $this->toUser;
        } else {
            $user = $this->fromUser;
        }

        $nextLevel = CpLevel::where("level", ">", $this->level_id)->first();
        $ratio = 0;
        if ($nextLevel) {
            $nextLevelPercentage = $nextLevel->level;
            $ratio = $this->di / $nextLevel->exp;
        } else {
            $nextLevelPercentage = 0;
        }


        return [
            'id'        => $this->id,
            'level'     => $this->level_id,
            'next_level'     => $nextLevelPercentage,
            'di'        => $this->di,
            'ratio' => $ratio,
            "user"      => [
                "id"        => $user?->id,
                "uid"       => $user?->uuid,
                "name"      => $user?->name,
                "image"     => $user?->avatar,
                "gender"    => (string)($user?->gender == 'male' ? 1 : 0),
                'frame'=>Common::getUserDress($user?->id,$user?->dress_1,4,'img2', true)?:Common::getUserDress($user?->id,$user?->dress_1,4,'img1', true),
            ],
            "relation" => $this->relation,
            'frame'=>Common::getUserDress($user?->id,$user?->dress_1,4,'img2', true)?:Common::getUserDress($user?->id,$user?->dress_1,4,'img1', true),

        ];
    }
}
