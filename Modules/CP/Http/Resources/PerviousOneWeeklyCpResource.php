<?php

namespace Modules\CP\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PerviousOneWeeklyCpResource extends JsonResource
{


    public function toArray($request)
    {

        $cp = $this->cp;
        
        return [

            'id' => $cp?->id ?? 0,
            'user_one_id' => $cp?->userOne->id ?? 0,
            'user_one_name' => $cp?->userOne->name ?? '',
            'user_one_image' => $cp?->userOne->profile->avatar ?? '',
            'user_two_id' => $cp?->userTwo->id ?? 0,
            'user_two_name' => $cp?->userTwo->name ?? '',
            'user_two_image' => $cp?->userTwo->profile->avatar ?? '',
            'total_gift_price' =>numToString(intval( $this?->total_price ?? 0)) ,
            'level' => @$cp?->level ?? 0,
        ];
    }
}
