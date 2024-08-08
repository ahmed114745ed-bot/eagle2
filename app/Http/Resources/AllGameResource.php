<?php

namespace App\Http\Resources;

use App\Models\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class AllGameResource extends JsonResource
{
    public function toArray($request)
    {
        $type = \request()->type ?? 0;
        return [
            'id'      =>  $this->id,
            'name'      => (auth()->user()->lan == "ar" ? $this->name : $this->name_en),
            'image'     => @$this->image ?? '',
            'url'       => $type == 1 ? (@$this->mini_url ?? '') : (@$this->url ?? ''),
            'webView_config' => $this->type == 2 ? true : false,
        ];
    }
}
