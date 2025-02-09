<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class MusicResource extends JsonResource
{
    public function toArray($request)
    {
        $url = $this->url;
        $lastPart = basename($url);
        return [
            'id' => $this->id,
            'name' => $lastPart,
            'url' => $this->url,
            'user' => $this->user,
        ];
    }
}
