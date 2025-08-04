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

        $dress_1_data = $this->getUserDress($user, 4, $user->dress_1, 'img2');
        $dress_1_fallback = $this->getUserDress($user, 4, $user->dress_1, 'img1');
        $frame = $dress_1_data ?: $dress_1_fallback;
 
        $currentLevel = CpLevel::find($this->level_id);
        $nextLevel = CpLevel::where("cp_relation_id",  $currentLevel?->cp_relation_id)->where("id", ">", $this->level_id)->orderBy('id')->first();

        dd([
            'di' => $this->di,
            'currentLevelExp' => $currentLevel ?? null,
            'nextLevelExp' => $nextLevel ?? null,
        ]);
        $ratio = 0;
    
        
        if ($currentLevel && $nextLevel && $nextLevel->exp > $currentLevel->exp) {

            $currentExp = $this->di;
            $nextLevelExp = $nextLevel->exp ?? $currentExp;
            $ratio = round(min(($currentExp / $nextLevelExp) * 100, 100), 2);            
        } else {
            $ratio = 100;
        }


        return [
            'id'        => $this->id,
            'level'     => $this->level_id,
            'next_level'     => $nextLevel,
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
