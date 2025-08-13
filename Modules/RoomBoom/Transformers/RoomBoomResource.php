<?php

namespace Modules\RoomBoom\Transformers;

use App\Models\Gift;
use App\Models\Ware;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomBoomResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'total_gifts_value' => $this->total_gifts_value,
        ];
    }
}
