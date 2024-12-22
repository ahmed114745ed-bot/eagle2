<?php

namespace Modules\CP\Transformers;

use App\Helpers\Common;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\CP\Entities\CpLevel;

class RankingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->cp?->id,
            'level'         => [
                'id' => $this->cp?->level_id,
                'img' => $this->cp?->level?->img
            ],
            'exp'           => $this->cp?->di,
            "userOne"       => [
                "id"        => $this->cp?->fromUser?->id,
                "uid"       => $this->cp?->fromUser?->uuid,
                "name"      => $this->cp?->fromUser?->name,
                "image"     => $this->cp?->fromUser?->profile?->avatar,
                "gender"    => $this->cp?->fromUser?->profile?->gender,
                'achievements' => $this->cp?->fromUser?->medals->map(function ($medal) {
                    return ['custom_image' => $medal->custom_image];
                })
            ],
            "userTwo"      => [
                "id"        => $this->cp?->toUser?->id,
                "uid"       => $this->cp?->toUser?->uuid,
                "name"      => $this->cp?->toUser?->name,
                "image"     => $this->cp?->toUser?->profile?->avatar,
                "gender"    => $this->cp?->toUser?->profile?->gender,
                'achievements' => $this->cp?->toUser?->medals->map(function ($medal) {
                    return ['custom_image' => $medal->custom_image];
                })

            ]
        ];
    }
}
