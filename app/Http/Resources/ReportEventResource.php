<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $target = '';
        if ($this->reward->type == 'coins') {
            $target = $this->reward->target;
        } elseif ($this->reward->type == 'vip') {
            $target = $this->reward->vip->name;
        } elseif ($this->reward->type == 'ware') {
            $target = $this->reward->ware->name;
        }

        $path = "";
        if ($this->reward->type == 'ware') {
            $path  = $this->reward->ware->img2 ?? $this->reward->ware->show_img;
        } elseif ($this->reward->type == 'vip') {
            $path = $this->reward->vip->img;
        } elseif ($this->reward->type == 'achievement') {
            $path = $this->reward->target;
        } else {
            $path = 'cion.png';
        }
        return [
            'id' => $this->id,
            'winner' => [
                'name' => $this->winner->name ?? '',
                'uuid' => $this->winner->uuid ?? 0,
                'image' => @$this->winner->profile->avatar ?? '',
            ],
            'reward' => [
                'level' => $this->reward->level,
                'type' => $this->reward->type,
                'gift' => $target,
                'image' => $path,
            ],

        ];
    }
}
