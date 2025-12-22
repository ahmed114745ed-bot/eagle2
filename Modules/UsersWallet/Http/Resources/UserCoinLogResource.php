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
                        'amount' => abs($this->amount)
                    ]),
                ];

            case 'gifts':
                return [
                    __('wallet.gift_title'),
                    __('wallet.gift_description'),
                ];

            case 'exchanges_diamonds':
                return [
                    __('wallet.exchange_title'),
                    __('wallet.exchange_description', [
                        'diamonds' => abs($this->amount),
                        'coins'    => abs($this->coin),
                    ]),
                ];

            case 'gift_room_audio':
            case 'gift_room_live':
                return [
                    __('wallet.gift_room_title'),
                    __('wallet.gift_room_description', [
                        'user' => $this->user->name ?? __('wallet.unknown_user')
                    ]),
                ];

            case 'moment':
                return [
                    __('wallet.moment_title'),
                    __('wallet.moment_description'),
                ];

            case 'coin_game':
                $amount = abs($this->amount);

                if ($this->amount < 0) {
                    return [
                        __('coin_game.lose_title'),
                        __('coin_game.lose_description', ['amount' => $amount]),
                    ];
                }

                return [
                    __('coin_game.win_title'),
                    __('coin_game.win_description', ['amount' => $amount]),
                ];

            default:
                return [
                    __('wallet.general_title'),
                    __('wallet.general_description'),
                ];
        }
    }
}
