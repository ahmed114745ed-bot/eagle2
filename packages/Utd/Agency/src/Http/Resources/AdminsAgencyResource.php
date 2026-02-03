<?php

namespace Utd\Agency\Http\Resources;

use Utd\Agency\Facades\AgencyHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminsAgencyResource extends JsonResource
{
    public function toArray($request)
    {
        $hasColor = AgencyHelper::hasInPack(@$this->user->id, 18, true);

        return [
            'id' => $this->user->id ?? 0,
            'name' => @$this->user->name ?? '',
            'uuid' => $this->user->uuid ?? '',
            'image' => @$this->user->profile->avatar ?? '',
            'exp'   => '0',
            'image_color'          => @$this->user->color_image,
            'id_image'             => @$this->user->specialId?->ware?->show_img ?? '',
            'colored_name' => $hasColor ? AgencyHelper::wareUserVip(@$this->user->id, 18, 'color') ?? '' : '',
        ];
    }
}
