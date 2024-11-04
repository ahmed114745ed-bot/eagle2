<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class GameReportResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'coins' => @$this->coins ?? 0,
            'type' => @$this->type,
            'game'  => $this->game,
        ];
    }
}
