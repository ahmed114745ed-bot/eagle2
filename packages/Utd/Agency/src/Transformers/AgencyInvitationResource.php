<?php

namespace Utd\Agency\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AgencyInvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'invited_by' => $this->invited_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relations
            'agency' => $this->whenLoaded('agency'),
            'user' => $this->whenLoaded('user'),
            'inviter' => $this->whenLoaded('inviter'),
        ];
    }
}
