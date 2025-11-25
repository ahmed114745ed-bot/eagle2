<?php

namespace App\Http\Resources;

use App\Models\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class AllGameInRoomResource extends JsonResource
{
    public function toArray($request)
    {
        $type = \request()->type ?? 0;

        return [
            'id'      =>  $this->id,
            'name'      => (auth()->user()->lan == "ar" ? $this->name : $this->name_en),
            'image'     => @$this->image ?? '',
            'url' => $this->in_room == 1
                ? ($this->url ?? '')
                : ($this->in_room == 0
                    ? ($this->mini_url ?? '')
                    : ($this->hd_url ?? '')
                ),
            'webView_config' => $this->type == 2 ? true : false,
            'high_safety' => (intval(@$this->hight_image) ?? 0),
            'high' => intval($this->in_room == 1 ? (floatval($this->hight ?? 0)) : null),
            'in_room' => @$this->in_room,
            'is_hot' => rand(0, 1),
            'type' => $this->type ?? 0,
        ];
    }
}
