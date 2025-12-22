<?php

namespace Modules\UsersWallet\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCoinLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        [$title, $description] = $this->getTitleAndDescription();

        return [
            'feature_type'  => $this->feature_type,
            'type'          => $this->type,
            'amount'        => $this->amount,
            'title'         => $title,
            'description'   => $description,
            'negative_sign' => $this->amount < 0,
            'created_at'    => Carbon::parse($this->created_at)
                ->locale(app()->getLocale())
                ->translatedFormat('d F Y - h:i A'),
        ];
    }

    /**
     * Build professional title & description
     */
  private function getTitleAndDescription(): array
{
    switch ($this->type) {

 
        case 'payment':
            return [
                __('payment_title'),
                __('payment_description', [
                    'amount' => abs($this->amount),
                ]),
            ];

        case 'admin_charge':
        case 'bd_charge':
            return [
                __('admin_adjustment_title'),
                __('admin_adjustment_description', [
                    'amount' => abs($this->amount),
                ]),
            ];


        case 'cp':
            return [
                __('cp_title'),
                __('cp_description', [
                    'amount' => abs($this->amount),
                ]),
            ];


        case 'gifts':
        case 'gift_logs':
        case 'gift':
            return [
                __('gift_title'),
                __('gift_description'),
            ];

        case 'daily_gift':
            return [
                __('daily_gift_title'),
                __('daily_gift_description'),
            ];

        case 'lucky_gift':
            return [
                __('lucky_gift_title'),
                __('lucky_gift_description'),
            ];

        case 'lucky_box':
        case 'box_gift':
            return [
                __('lucky_box_title'),
                __('lucky_box_description'),
            ];


        case 'exchange':
        case 'exchanges_diamonds':
            return [
                __('exchange_title'),
                __('exchange_description', [
                    'diamonds' => abs($this->amount),
                    'coins'    => abs($this->coin),
                ]),
            ];

        case 'cashback':
            return [
                __('cashback_title'),
                __('cashback_description', [
                    'amount' => abs($this->amount),
                ]),
            ];


        case 'packs':
            return [
                __('purchase_title'),
                __('purchase_description', [
                    'amount' => abs($this->amount),
                    'item'   => $this->item_name ?? __('packs.default_item'),
                ]),
            ];


        case 'coin_game':
            return $this->amount < 0
                ? [
                    __('lose_title'),
                    __('lose_description', [
                        'amount' => abs($this->amount),
                    ]),
                ]
                : [
                    __('win_title'),
                    __('win_description', [
                        'amount' => abs($this->amount),
                    ]),
                ];

  
        case 'weekly_star':
        case 'room_cup':
        case 'room_boom':
        case 'host_level':
            return [
                __('achievement_title'),
                __('achievement_description'),
            ];

   
        case 'comment':
            return [
                __('comment_title'),
                __('comment_description'),
            ];

        case 'family':
            return [
                __('family_title'),
                __('family_description'),
            ];


        case 'background':
        case 'background_images':
            return [
                __('background_title'),
                __('background_description'),
            ];


        case 'invitation_charge_earnings':
            return [
                __('invitation_title'),
                __('invitation_description', [
                    'amount' => abs($this->amount),
                ]),
            ];

        default:
            return [
                __('general_title'),
                __('general_description'),
            ];
    }
}

}
