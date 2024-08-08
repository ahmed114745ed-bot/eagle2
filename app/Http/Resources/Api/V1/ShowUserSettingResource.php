<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowUserSettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'    => $this->id,
            'user_id' => $this->user_id,
            'show_git' => ($this->show_git == 1 ? true : false ),
            'show_intro' => ($this->show_intro == 1 ? true : false ),
            'show_banner' => ($this->show_banner == 1 ? true : false ),
        ];
    }
}
