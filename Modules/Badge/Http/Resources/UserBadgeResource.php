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
        $user = request()->user();
        $userLang = $user->lan ?? 'en';

        // Get image for user's language with fallback
        $badgeImage = $this->badge?->images?->firstWhere('language', $userLang)?->image
            ?? $this->badge?->images?->firstWhere('language', 'default')?->image
            ?? $this->badge?->images?->first()?->image
            ?? $this->badge?->image;

        info($this->badge?->images?->firstWhere('language', $userLang)?->image);
        info($this->badge?->images?->firstWhere('language', 'default')?->image();
        info($this->badge?->images?->first()?->image);
        info($this->badge?->image);

        return [
            'image' => $badgeImage,
            'image_type' => $badgeImage?->image_type ?? $this->badge?->image_type ?? '',
        ];
    }
}
