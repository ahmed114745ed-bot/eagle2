<?php

namespace Utd\Agency\Http\Resources;

use Utd\Agency\Facades\AgencyHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class MyDataForAgencyResource extends JsonResource
{
    public function toArray($request)
    {
        // التأكد من وجود بيانات
        if (!$this->resource) {
            return [
                'id' => 0,
                'uuid' => '',
                'diamonds' => 0,
                'name' => '',
                'phone' => '',
                'country' => null,
                'vip' => null,
                'level' => 0,
                'profile' => [
                    'image' => ''
                ],
                'has_color_name' => false,
                'gender' => null,
                'colored_name' => '',
            ];
        }

        $hasColor = AgencyHelper::hasInPack(@$this->id, 18, true);

        $data = [
            'id' => @$this->id,
            'uuid' => @$this->uuid,
            'diamonds' => @$this->monthly_diamond_received ?: 0,
            'name' => @$this->name ?: '',
            'phone' => @$this->phone ?? '',
            'country' => $this->country ?? null,
            'vip' => @AgencyHelper::ovip_center($this->id),
            'level' => AgencyHelper::level_center_min(@$this->id),
            'profile' => new ProfileForAgencyResource(@$this->profile),
            'has_color_name' => false,
            'gender' => $this->gender,
            'colored_name' => $hasColor ? AgencyHelper::wareUserVip(@$this->id, 18, 'color') ?? '' : '',
        ];

        return $data;
    }
}
