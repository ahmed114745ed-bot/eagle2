<?php

namespace Utd\RoomBoom\Transformers;

use Utd\Gifts\Entities\Gift;
use App\Models\Ware;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomBoomRewardResource extends JsonResource
{
    protected $ware = null;

    protected $gift = null;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'priority' => $this->priority,
            'type' => $this->target_type,
            'image' => $this->getImageUrl(),
            'price' => $this->getPrice(),
            'gift_image_type' => $this->getGiftImageType() ?? '',
            'title' => $this->getTitle().($this->expire_days ? ' - '.$this->expire_days.' days' : ''),
            'count' => $this->quantity,
        ];
    }

    public function getGiftImageType()
    {
        if ($this->target_type === 'gift') {
            $gift = $this->getGift();

            return $gift?->image_type;
        }
    }

    public function getImageUrl()
    {
        if ($this->target_type === 'ware') {
            $ware = $this->getWare();
            $path = $ware->show_img ?? $ware?->img2;
        } elseif ($this->target_type === 'gift') {
            $gift = $this->getGift();
            $path = $gift->img ?? '';
        } elseif ($this->target_type === 'achievement') {
            $path = $this->target;
        } elseif ($this->target_type === 'coin') {
            $path = 'coin.png';
        } else {
            $path = '';
        }

        return getImagePath($path);
    }

    protected function getWare()
    {
        if ($this->ware !== null) {
            return $this->ware;
        }

        return $this->ware = Ware::find($this->target);
    }

    protected function getGift()
    {
        if ($this->gift !== null) {
            return $this->gift;
        }

        return $this->gift = Gift::find($this->target);
    }

    protected function getTitle()
    {
        if ($this->target_type === 'ware') {
            $ware = $this->getWare();

            return $ware?->name ?? '';
        }
        if ($this->target_type === 'gift') {
            $gift = $this->getGift();

            return $gift?->name ?? '';
        }
        if ($this->target_type === 'achievement') {
            return 'Achievement';
        }
        if ($this->target_type === 'Coin') {
            return 'Coin';
        }

        return '';

    }

    protected function getPrice()
    {
        if ($this->target_type === 'ware') {
            $ware = $this->getWare();

            return $ware?->price ?? 0;
        }
        if ($this->target_type === 'gift') {
            $gift = $this->getGift();

            return $gift?->price ?? 0;
        }
        if ($this->target_type === 'achievement') {
            return 0;
        }
        if ($this->target_type === 'coin') {
            return $this->target;
        }

        return 0;

    }
}
