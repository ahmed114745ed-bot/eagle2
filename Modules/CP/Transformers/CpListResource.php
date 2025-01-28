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
            $user = $this->fromUser;
        } else {
            $user = $this->toUser;
        }

        $dress_1_data = $this->getUserDress($user, 4, $user->dress_1, 'img2');
        $dress_1_fallback = $this->getUserDress($user, 4, $user->dress_1, 'img1');
        $frame = $dress_1_data ?: $dress_1_fallback;

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
                'frame' => $frame,
            ],
            "relation" => $this->relation,
            'frame' => $frame,

        ];
    }
    public function getUserDress($user, $type, $dress, $item = 'img1')
    {
        $pack = $user->packs
            ->where('type', $type)
            ->where('target_id', $dress)
            ->first();

        return $pack && $pack->ware ? $pack->ware->{$item} : '';
    }
}
