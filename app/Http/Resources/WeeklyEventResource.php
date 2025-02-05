<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WeeklyEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->type == "ware") {
            $target = @$this->ware->name ?? '';
        } elseif ($this->type == "vip") {
            $target = @$this->vip->name ?? '';
        } elseif ($this->type == "coins") {
            $target = @$this->target;
        } elseif ($this->type == "achievement") {
            $value = getDriverUrl() . '/' . @$this->target;
            $target = "<img src='$value' width='80' height='80'>";
        }

        if ($this->type == 'ware') {
            $path  = $this->ware->img2 ?? $this->ware->show_img;
        } elseif ($this->type == 'vip') {
            $path = $this->vip->img;
        } elseif ($this->type == 'achievement') {
            $path = $this->target;
        } else {
            $path = 'cion.png';
        }
        return [
            'id' => $this->id,
            'type' => $this->type ?: '',
            'target' => $target ?? '',
            'id_target' => $this->target,
            'image' => $path ?? '',
            'expire' => $this->expire ?? 0,
            'weekly_star_id' => $this->weekly_star_id,
            'level' => $this->level,


        ];
    }
}