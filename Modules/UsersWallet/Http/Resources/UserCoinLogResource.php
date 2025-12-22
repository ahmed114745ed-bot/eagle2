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
                __('wallet.payment_title'),
                __('wallet.payment_description', [
                    'amount' => abs($this->amount),
                ]),
            ];

        case 'admin_charge':
        case 'bd_charge':
            return [
                __('wallet.admin_adjustment_title'),
                __('wallet.admin_adjustment_description', [
                    'amount' => abs($this->amount),
                ]),
            ];


        case 'cp':
            return [
                __('wallet.cp_title'),
                __('wallet.cp_description', [
                    'amount' => abs($this->amount),
                ]),
            ];


        case 'gifts':
        case 'gift_logs':
        case 'gift':
            return [
                __('wallet.gift_title'),
                __('wallet.gift_description'),
            ];

        case 'daily_gift':
            return [
                __('wallet.daily_gift_title'),
                __('wallet.daily_gift_description'),
            ];

        case 'lucky_gift':
            return [
                __('wallet.lucky_gift_title'),
                __('wallet.lucky_gift_description'),
            ];

        case 'lucky_box':
        case 'box_gift':
            return [
                __('wallet.lucky_box_title'),
                __('wallet.lucky_box_description'),
            ];


        case 'exchange':
        case 'exchanges_diamonds':
            return [
                __('wallet.exchange_title'),
                __('wallet.exchange_description', [
                    'diamonds' => abs($this->amount),
                    'coins'    => abs($this->coin),
                ]),
            ];

        case 'cashback':
            return [
                __('wallet.cashback_title'),
                __('wallet.cashback_description', [
                    'amount' => abs($this->amount),
                ]),
            ];


        case 'packs':
            return [
                __('packs.purchase_title'),
                __('packs.purchase_description', [
                    'amount' => abs($this->amount),
                    'item'   => $this->item_name ?? __('packs.default_item'),
                ]),
            ];


        case 'coin_game':
            return $this->amount < 0
                ? [
                    __('coin_game.lose_title'),
                    __('coin_game.lose_description', [
                        'amount' => abs($this->amount),
                    ]),
                ]
                : [
                    __('coin_game.win_title'),
                    __('coin_game.win_description', [
                        'amount' => abs($this->amount),
                    ]),
                ];

  
        case 'weekly_star':
        case 'room_cup':
        case 'room_boom':
        case 'host_level':
            return [
                __('wallet.achievement_title'),
                __('wallet.achievement_description'),
            ];

   
        case 'comment':
            return [
                __('wallet.comment_title'),
                __('wallet.comment_description'),
            ];

        case 'family':
            return [
                __('wallet.family_title'),
                __('wallet.family_description'),
            ];


        case 'background':
        case 'background_images':
            return [
                __('wallet.background_title'),
                __('wallet.background_description'),
            ];


        case 'invitation_charge_earnings':
            return [
                __('wallet.invitation_title'),
                __('wallet.invitation_description', [
                    'amount' => abs($this->amount),
                ]),
            ];

        default:
            return [
                __('wallet.general_title'),
                __('wallet.general_description'),
            ];
    }
}

}
