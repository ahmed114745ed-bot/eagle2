<?php

namespace Modules\Events\Transformers;


use App\Models\Ware;
use Illuminate\Http\Resources\Json\JsonResource;

class WeeklyStarGift extends JsonResource
{
    public function toArray($request)
    {
        switch ($this->type) {
            case "ware":
                $expire = $this->expire . ' days';
                $type = match ($this->ware->type) {
                    6 => trans('Intro Frame'),
                    5 => trans('Bubble Frame'),
                    default => trans('Avatar Frame'),
                };
                $image = $this->ware->show_img;
                break;

            case "vip":
                $expire = $this->expire . ' days';
                $type = $this->vip->name;
                $vipIcon = Ware::where('level',$this->vip->level)->where('type' ,10)->where('get_type',1)->first();
                $image = $this->vip->img;
                break;

            case "achievement":
                $expire = $this->expire . ' days';
                $type = "achievement";
                $image = @$this->target[0] == '/' ? substr(@$this->target, 1) : @$this->target??'';
                break;
            default:
                $expire = $this->target;
                $type = "coins";
                $image = "custom_image/gold_coin_icon.png";
                break;
        }

        return [
            'name'  => "{$expire} /{$type}",
            'image' => $image,
        ];
    }
}
