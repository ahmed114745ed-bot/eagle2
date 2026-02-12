<?php

namespace Utd\Agency\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Utd\Agency\Facades\AgencyHelper;

class SenderGiftLogResource extends JsonResource
{
    public function toArray($request)
    {
        $hasColor = AgencyHelper::hasInPack(@$this->sender->id, 18, true);
        $data = [
            'id' => @$this->sender->id ?? 0,
            'uuid' => @$this->sender->uuid ?? '',
            'name' => @$this->sender->name ?: '',
            'image' => $this->sender->profile->avatar ?? '',
            'image_color' => @$this->sender->color_image,
            'id_image' => @$this->sender->specialId?->ware?->show_img ?? '',
            'exp' => $this->exp ?? '',
            'level' => AgencyHelper::level_center_min(@$this->sender->id),
            'colored_name' => $hasColor ? AgencyHelper::wareUserVip(@$this->sender->id, 18, 'color') ?? '' : '',
        ];

        return $data;
    }
}
