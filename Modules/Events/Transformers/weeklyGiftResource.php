<?php

namespace Modules\Events\Transformers;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class weeklyGiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'name'=>app ()->getLocale () == 'ar' ? $this->name : $this->e_name,
            'type'=>$this->type=1?'normal':'hot',
            'price'=>$this->price?:0,
            'image'=>$this->img?:'',
            'show_img'=>$this->show_img?:'',
            'show_img2'=>$this->show_img2?:'',
            'vip_level'=>$this->vip_level ?:0,
            'is_on'=>($this->vip_level <= Common::getLevel (request ()->user ()->id,3)) ? 1 : 0
        ];
    }
}