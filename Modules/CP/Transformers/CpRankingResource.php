<?php

namespace Modules\CP\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CpRankingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->cp_id,
            'level' => $this->cp->level,
            'exp' => $this->cp->exp,
            "userOne" => [
                "id" => $this->cp?->userOne?->id,
                "uid" => $this->cp?->userOne?->uuid,
                "name" => $this->cp?->userOne?->name,
                "image" => $this->cp?->userOne?->profile?->avatar,
            ]
            ,"userTwo" => [
                "id" => $this->cp?->userTwo?->id,
                "uid" => $this->cp?->userTwo?->uuid,
                "name" => $this->cp?->userTwo?->name,
                "image" => $this->cp?->userTwo?->profile?->avatar,
            ]
        ];
    }
}
