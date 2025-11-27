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
            'feature_type' => $this->feature_type,
            'type' => $this->type,
            'amount' =>  $this->amount,
            'item_name' => __($this->type),
            'get_by'    => @$this->user->name ?? '',
            'coin' => $this->when(!is_null($this->coin), $this->coin),
            'created_at' => $this->created_at,
        ];
    }
}
