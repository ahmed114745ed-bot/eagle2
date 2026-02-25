<?php

namespace Utd\Events\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TopWeeklyStarUsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'totalGiftNum' => (string) ((int) (@$this->totalGiftNum)) ?? '0',
            'user_id' => $this->sender_id,
            'uuid' => $this->sender->uuid ?? 0,
            'name' => @$this->sender->name ?? '',
            'avatar' => @$this->sender->profile->avatar ?? '',
        ];
    }
}
