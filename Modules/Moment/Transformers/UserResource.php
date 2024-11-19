<?php

namespace Modules\Moment\Transformers;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request
     * @return array
     */
    public function toArray($request)
    {

        $level = Common::level_center(@$this->id);
        if (gettype($level) == 'array') {
            $receiver_level = $level['receiver_level'] ?? 0;
            $sender_level   = $level['sender_level'] ?? 0;
            $receiver_img   = $level['receiver_img'] ?? '';
            $sender_img     = $level['sender_img'] ?? '';
        } else {
            $receiver_level = 0;
            $sender_level   = 0;
            $receiver_img   = '';
            $sender_img     = '';
        }

        $vip       = @Common::ovip_center($this->id) ?? 0;
        $vip_level = (gettype($vip) == 'array') ? $vip['level'] : 0;


        return [
            'id'             => @$this->id, // both
            'uuid'           => @$this->uuid ?? '', // both
            'name'           => @$this->name ?: '', // both
            'image'          => @$this->profile->avatar ?: '', // both
            'receiver_level' => $receiver_level ?? 0, // both
            'sender_level'   => $sender_level, // both
            'receiver_img'   => $receiver_img, // both
            'sender_img'     => $sender_img, // both
            'charge_level' => Common::chargeLevel (@$this->id),
            'vip'            => $vip_level, // both
            'has_color_name' => Common::hasInPack($this->id, 18), // both

            
        ];

    }
}
