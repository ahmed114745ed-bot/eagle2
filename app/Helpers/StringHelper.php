<?php

namespace App\Helpers;

use App\Classes\Enums\SubTypeMessagesType;

class StringHelper
{
    public $key = 'api.sys_messages.';

    public function translate(int $type, string $data)
    {
        $data = explode('#', $data);
        switch ($type){
            case SubTypeMessagesType::WARE:
                if (count($data) > 1){
                    return __($data[0], ['name' => $data[1]]);
                }
                break;
            case SubTypeMessagesType::SILVER:
                if (count($data) > 1){
                    return __($data[0], ['quantity' => $data[1]]);
                }
                break;
            case SubTypeMessagesType::VIP_UPGRADE:
                if (count($data) > 1){
                    return __($data[0], ['level' => $data[1]]);
                }
                break;
            case SubTypeMessagesType::SEND_WARE:
                if (count($data) > 2){
                    return __($data[0], ['wareName' => $data[1], 'toUserName' => $data[2]]);
                }
                break;
            case SubTypeMessagesType::GOT_GIFT:
                if (count($data) > 1){
                    return __($data[0], ['giftName' => $data[1]]);
                }
                break;
        }

            return null;
    }
    public function levelUp(int $fromLevel, int $toLevel, float $expired = null): string
    {
        return $this->key . 'level_up#'. $fromLevel ?? 1 . '#' . $toLevel;
    }

    public function buyWare(string $wareName) : string
    {
        return 'api.sys_messages.ware#'.$wareName;
    }

    public function buySilvers(int $quantity) : string
    {
        return 'api.sys_messages.silver#'.$quantity;
    }
    public function vipUpgrade(int $level) : string
    {
        return 'api.sys_messages.vip_upgrade#'.$level;
    }

    public function sendWare(string $wareName, string $toUserName) : string
    {
        return 'api.sys_messages.send_ware#'.$wareName.'#'.$toUserName;
    }

    public function gotGift(string $wareName) : string
    {
        return 'api.sys_messages.got_gift#'.$wareName;
    }

}
