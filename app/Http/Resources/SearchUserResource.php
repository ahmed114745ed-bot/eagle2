<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name'=>@$this->name?:'',
            'profile_image'=>@$this->profile->avatar ?? '',
            'level'      => [
                'sender_level'  =>@$this->total_sender_level ?? 0,
                'reciver_level' =>@$this->total_received_level ?? 0
            ],
            'frame'=> $frame, // both
            'frame_id'=>$frame != ''? @$this->dress_1 : 0, // both
            'has_color_name'=>@$this->packs?->where('type', 18)->first() != null, // both
        ];
    }
}
