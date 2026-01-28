<?php

namespace Utd\Room\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomMicrophoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'room_id' => $this->room_id,
            'position' => $this->position,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'uuid' => $this->user->uuid ?? '',
                'name' => $this->user->name ?? '',
                'avatar' => $this->user->profile?->avatar ?? '',
            ]),
        ];
    }
}
