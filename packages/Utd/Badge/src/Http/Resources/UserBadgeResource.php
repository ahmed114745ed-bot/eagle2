<?php

namespace Utd\Badge\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBadgeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'image' => $this->badge->image,
            'image_type' => @$this->badge->image_type ?? '',
        ];
    }
}
