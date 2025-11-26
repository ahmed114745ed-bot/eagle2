<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCoinLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this?->id,
            'type' => $this->type,
            'amount' => $this->type == 'exchange' ? (int)$this->feature_type : $this->amount,
            'item_name' => $this->item_name,
            'feature_type' => $this->type == 'exchange' ? $this->amount : ($this->whenHas('feature_type') ?: ''),
            'created_at' => $this->created_at,
        ];
    }
}
