<?php

namespace Utd\Gifts\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GiftLogResource extends JsonResource
{
    protected static $giftTotal;

    public static function setGiftTotal($giftTotal): void
    {
        self::$giftTotal = $giftTotal;
    }

    public function toArray($request)
    {
        return [
            'num'=>numToString(@$this->t),
            'gift'=>(new GiftResource(@$this->gift))->additional([
                'gift_total' => self::$giftTotal ?? collect()
            ]),
        ];
    }
}
