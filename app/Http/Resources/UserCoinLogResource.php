<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCoinLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $title = '';
        $description = '';
        switch ($this->type) {
            case 'payment':
                $title = __('buy') . '' . $this->amount . ' ' . _('coin');
                $description = _('through') . ' ' . $this->feature;
                break;

            case 'gift':
                $title = __('send gift');
                break;

            case 'exchanges_diamonds':
                $title = __($this->type);
                $description = abs($this->amount) . ' ' . __('diamonds') . '-> ' . $this->coin . ' ' . __('coin');
                break;

            case 'gift_room_audio':
                $title = __($this->type);
                $description = __('from user') . ' ' . $this->user->name;
                break;
            case 'gift_room_live':
                $title = __($this->type);
                $description = __('from user') . ' ' . $this->user->name;
            case 'moment':
                $title = __($this->type);
                $description = __('your moment');
                break;

            default:
                $title = __($this->type);
                $description = __($this->type);
                break;
        }
        return [
            'feature_type' => $this->feature_type,
            'type' => $this->type,
            'amount' =>  $this->amount,
            'title' => $title,
            'description' => $description,
            'negative_sign' => $this->amount < 0 ? true : false,
            'created_at' => Carbon::parse($this->created_at)
                ->locale(app()->getLocale()) // Arabic or English
                ->translatedFormat('d F Y - h:i A'),
        ];
    }
}
