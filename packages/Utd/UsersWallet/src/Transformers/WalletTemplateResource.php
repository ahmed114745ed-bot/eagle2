<?php

namespace Utd\UsersWallet\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletTemplateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'minimum' => $this->minimum,
            'transfer_fee' => $this->transfer_fee,
            'fields' => WalletFieldResource::collection($this->whenLoaded('fields')),
        ];
    }
}
