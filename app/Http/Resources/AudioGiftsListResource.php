<?php

namespace App\Http\Resources;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class AudioGiftsListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {

        return [

            'user' => [
                'name' => $this->sender->name ?? '',
                'uuid' => $this->sender->uuid ?? '',
                'avatar' => $this->sender->profile->avatar ?? '',
            ],
            'room' => [
                'name' => $this->room->room_name ?? '',
            ],
            'gift' => [
                'name' => $this->gift->name ?? '',
            ],
            'created_at' => $this->created_at ?? '',
            'diamond' => $this->total ?? 0,
        ];
    }
}
