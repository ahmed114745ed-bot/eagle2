<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class ServerResource extends JsonResource
{

    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'name'=>@$this->server_name ?? "", // both
            'short_name' => $this->short_name ?? "",
            'bucket_name' => $this->bucket_name ?? "",
            'domain' => $this->domain ?? "",
            'img'=>$this->img,
            'default'=>$this->default,
            'login_background'=>$this->login_background,
            'background'=>$this->splash_background,
            'description'=> app()->getLocale() == 'ar' ? $this->description_ar : $this->description_en,
        ];
    }
}
