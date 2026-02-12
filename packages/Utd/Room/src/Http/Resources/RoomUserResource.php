<?php

namespace Utd\Room\Http\Resources;

use App\Helpers\Common;
use App\Http\Resources\Api\V1\MangerTypeResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class RoomUserResource extends JsonResource
{
    public static $vipsReceivedImages = null;

    public static $vipsSenderImages = null;

    public static function initializeData($vipsReceivedImages, $vipsSenderImages)
    {
        self::$vipsSenderImages = $vipsSenderImages;
        self::$vipsReceivedImages = $vipsReceivedImages;

    }

    public static function clear()
    {
        self::$vipsSenderImages = null;
        self::$vipsReceivedImages = null;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {

        if (! self::$vipsReceivedImages && ! self::$vipsSenderImages) {
            $imageReceiver = $this->getImageReceiverOrSender('receiver_id', 1);
            $imageSender = $this->getImageReceiverOrSender('sender_id', 2);
        } else {
            $imageReceiver =
                count(self::$vipsReceivedImages) > 0 ? self::$vipsReceivedImages->where('level', $this->total_received_level)->first() : null;
            $imageSender =
                count(self::$vipsSenderImages) > 0 ? self::$vipsSenderImages->where('level', $this->total_sender_level)->first() : null;
        }

        $bubbleDress = $this->dress2;

        $bubble =
            ($this->packs->where('type', 5)->first() !== null) ? (($bubbleDress !== null) ? $bubbleDress->show_img : '') : '';

        return [
            'id' => @$this->id, // both //
            'uuid' => @$this->uuid, // both //
            'name' => @$this->name ?: '', // both //

            'profile_image' => @$this->profile->avatar ?? '', // both //
            'uuid' => @$this->uuid, // both
            'id_image' => @$this->specialId?->ware?->show_img ?? '',
            'special_id' => @$this->specialId?->ware?->id ?? 0,
            'level' => [
                'receiver_img' => $imageReceiver ? @$imageReceiver->img : '',
                'sender_img' => $imageSender ? @$imageSender->img : '',
                'sender_level' => @$this->total_sender_level ?? 0,
                'reciver_level' => @$this->total_received_level ?? 0,
            ],

            'vip' => [
                'level' => @$this->UserVip->level,
            ],
            'bubble' => @$bubble, // both
            'bubble_id' => @$bubble !== '' ? $this->dress_2 : 0, // both
            // 'has_color_name' => @$this->packs->where('type', 18)->first() != null, // both
            'has_color_name' => Common::hasInPack($this->id, 18, true), // both
            'manger_type' => new MangerTypeResource(@$this->mangerType),
            'type_user' => @$this->type_user ?? 0,

        ];

    }
}
