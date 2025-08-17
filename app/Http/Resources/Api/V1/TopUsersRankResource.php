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
            'name' => @$this->name ?? '',
            'uuid' => @$this->uuid,
            'img' => $this->profile->avatar ?? '',
            'total_gift' => $this->total_gift ?? 0,
        ];
    }
}
