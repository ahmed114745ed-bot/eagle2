<?php

namespace Utd\Agency\Http\Resources;

use Utd\Agency\Facades\AgencyHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiverGiftLogResource extends JsonResource
{
    public function toArray($request)
    {
        $hasColor = AgencyHelper::hasInPack(@$this->receiver->id, 18, true);

        $data = [
            'id' => @$this->receiver->id ?? 0,
            'uuid' => @$this->receiver->uuid ?? '',
            'name' => @$this->receiver->name ?: '',
            'image' => $this->receiver->profile->avatar ?: '',
            'exp'   => number_format(floatval($this->exp ?? 0.0)) ?? '',
            'image_color'          => @$this->receiver->color_image,
            'id_image'             => @$this->receiver->specialId?->ware?->show_img ?? '',
            'level' => AgencyHelper::level_center_min(@$this->receiver->id),
            'colored_name' => $hasColor ? AgencyHelper::wareUserVip(@$this->receiver->id, 18, 'color') ?? '' : '',
        ];

        return $data;
    }
}
