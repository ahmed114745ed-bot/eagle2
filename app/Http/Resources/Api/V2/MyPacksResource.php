<?php

namespace App\Http\Resources\Api\V2;

use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class MyPacksResource extends JsonResource
{
    public function toArray($request)
    {
        $user_id = $request->user_id ?:  $request->user()->id;
        $type = $request->type;
        if (in_array($type, [4, 5, 6, 7])) {
            $user_dress_after_i_changed = [
                4 => 1,
                5 => 2,
                6 => 3,
                7 => 4
            ];
            $dress_id  =
                User::where(['id' => $user_id])->value("dress_" . $user_dress_after_i_changed[$type]);
        }
        if (in_array($type, [4, 5, 6, 7])) {
            $title    = empty($this->expire) ? "permanent" : date('Y-m-d H:i:s', $this->expire) . " expire";
            $is_dress = $dress_id == $this->target_id ? 1 : 0;
            $color   = $this->color ?: '';
        } elseif ($type == 2) {
            $title = "have" . $this->num . "value" . $this->num * $this->price . "diamond";
            $color = '';
        } else {
            $title = "have" . $this->num . "indivual " . $this->title;
            $color = $this->color ?: '';
        }
        $types       = [
            '1' => 'gem',
            '2' => 'gifts',
            '3' => 'coupons',
            '4' => 'avatar frames',
            '5' => 'bubble boxes',
            '6' => 'entry effects',
            '7' => 'mic on the aperture',
            '8' => 'badges',
            '25' => 'special id'
        ];
        $get_types   = [
            '1' => 'vip level automatic acquisition',
            '2' => 'activities',
            '3' => 'treasure box',
            '4' => 'purchase',
            '5' => 'background addition',
            '6' => 'limited time purchase'
        ];
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'get_type' => __($get_types[$this->get_type]),
            'type' => __($types[$this->type]),
            'target_id' => $this->target_id,
            'num' => $this->num,
            'expire' => $this->expire != 0 ? date("Y-m-d H:i:s", $this->expire) : 0,
            'is_read' => $this->is_read,
            'created_at' => Carbon::parse($this->created_at)->setTimezone($request->hasHeader('tz') ? $request->header()['tz'][0] : 'UTC')->format('Y-m-d H:i:s') ?? '',
            'updated_at' => $this->updated_at,
            'sender_id' => $this->sender_id,
            'is_used' => $this->is_used == 1 ? true : false,
            'use_num' => $this->use_num,
            'name' => $this->name,
            'show_img' => $this->show_img,
            'svg'       =>  $this->img2 ?? '',
            'price' => @$this->price ?? '',
            'is_dress' => $is_dress ?? 0,
            'title' => $title,
            'color' => $color,
        ];
    }
}
