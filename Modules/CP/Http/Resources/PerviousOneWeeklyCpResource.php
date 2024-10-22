<?php

namespace Modules\CP\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PerviousOneWeeklyCpResource extends JsonResource
{
    
    
    public function toArray($request)
    {
        $winner = $this->WeeklyCpWinners->first();
        return [
            
            'id' => $winner->id,
            'user_one_id' => $winner->userOne->id ?? 0,
            'user_one_name' => $winner->userOne->name ?? '',
            'user_one_image' => $winner->userOne->profile->avatar ?? '',
            'user_two_id' => $winner->userTwo->id ?? 0,
            'user_two_name' => $winner->userTwo->name ?? '',
            'user_two_image' => $winner->userTwo->profile->avatar ?? '',
            'total_gift_price' =>numToString(intval( $winner->total_price)) ?? '0',
            'level' => $winner->level,


        ];
    }
}