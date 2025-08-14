<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class TopUsersRankResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => @$this->name ?? '', // both
            'notice' => @$this->notice ?? '',
            'owner_id' => @$this->app_owner_id??0,
            'owner_name' => @$this->owner->name  ?? '',
            'phone' => @$this->phone ?? 0,
            'img' => $this->owner->profile->avatar ?? '',
            'user_count' => $this->userCount ?? 0,
            'total_gift' => $this->total_gift ?? 0,
        ];
    }
}
