<?php

namespace Utd\Gifts\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GiftLogUtdResource extends JsonResource
{
    public function toArray($request)
    {
        $type = request()->type;
        $userId = request()->id;

        $isSender = $this->sender_id == $userId;
        $isReceiver = $this->receiver_id == $userId;

        $senderUser = $receiverUser = [];

        if (($type == 'sender' || $isSender) && !$isReceiver) {
            $receiverUser = [
                'id'    => $this->receiver->id ?? 0,
                'name'  => $this->receiver->name ?? '',
                'uuid'  => $this->receiver->uuid ?? 0,
                'image' => $this->receiver?->profile?->avatar ?? '',
            ];
        } elseif (($type == 'receiver' || $isReceiver) && !$isSender) {
            $senderUser = [
                'id'    => $this->sender->id ?? 0,
                'name'  => $this->sender->name ?? '',
                'uuid'  => $this->sender->uuid ?? 0,
                'image' => $this->sender?->profile?->avatar ?? '',
            ];
        }

        return [
            'gift' => [
                'img'       => $this->gift?->img ?? '',
                'show_img'  => $this->gift?->show_img ?? '',
                'show_img2' => $this->gift?->show_img2 ?? '',
            ],
            'total_price'   => $this->total,
            'sender_user'   => $senderUser,
            'receiver_user' => $receiverUser,
            'type'          => $isSender && $isReceiver ? 'yourself' : ($isSender ? 'sender' : 'receiver'),
        ];
    }
}
