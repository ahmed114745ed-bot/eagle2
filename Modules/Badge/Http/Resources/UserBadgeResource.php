<?php

namespace Modules\Badge\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBadgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $badge = $this->badge->images->firstWhere('language', app()->getLocale());
        return [
            'image' => $badge->image ?? '',
            'image_type' => $badge->image_type ?? '',

        ];
    }
}
