<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class MusicResource extends JsonResource
{
    public function toArray($request)
    {
        $url = $this->url;
        $parts = explode('/', $url);
        $lastPart = end($parts);
        return [
            'id' => $this->id,
            'name' => $lastPart,
            'url' => $this->url,
            'user' => $this->user,
        ];
    }
}
