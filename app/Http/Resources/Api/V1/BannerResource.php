<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'title'=>$this->title,
            'button_text'=>$this->button_text,
            'image_url'=>$this->image_url,
            'redirect_url'=>$this->redirect_url,
            'publish_at'=>$this->publish_at,
            'is_active'=>(int)$this->is_active,
        ];
    }
}
