<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class NewProfileResource extends JsonResource
{
    public function toArray($request)
    {
        $userId = $request->user()->id;
        $is_licked = $this->likes->where("likeable_id",$userId)->exist();
        $is_ignored = $this->ignores->where("ignore_user_id",$userId)->exist();
        return [
            'id' => $this->id,
            'uuid'=>@$this->uuid,
            'name' => $this->name,
            'image'=>@$this->profile?->avatar,
            'bio'=>@$this->bio,
            'distance'=>$this->distance,
            'licked'=>$is_licked ? true : false,
            'ignored'=>$is_ignored ? true : false,
        ];
    }
}
