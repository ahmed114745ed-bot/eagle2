<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MomentGiftResource extends JsonResource
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
                'name' => $this->user->name ?? '',
                'uuid' => $this->user->uuid ?? '',
                'avatar' => $this->user->profile->avatar ?? '',
            ],
            
            'gift' => [
                'name' => $this->gift->name ?? '',
            ],
            'created_at' => $this->created_at ?? '',
            'diamond' => $this->total ?? 0,
        ];
    }
}
