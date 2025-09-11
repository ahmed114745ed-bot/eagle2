<?php
namespace App\Enums;

enum UserCoinLogType: string
{
    case ADMIN_CHARGES = 'admin_charge';
    case BD_CHARGES = 'bd_charge';
    case APP_CHARGE = 'app_charge';
    case ROOM_COMMENT = 'comment';
    case HUAWEI_PAY = 'huawei_pay';
    case GOOGLE_PAY = 'google_pay';
    case PAYMENT = 'payment';
    case ROOM_TARGET = 'room_target';
    case CP = 'cp';
    case CPS = 'cps';
    case EXCHANGE = 'exchange';
    case FAMILY = 'family';
    case BACKGROUND_IMAGES = 'background_images';
    case DAILY_GIFT = 'daily_gift';
    case PK = 'pk_event';
    case WEEKLY_STAR = 'weekly_star';
    case CHARGE_EVENT = 'charge_event';
    case LUCK_BOX = 'lucky_box';
    case COIN_GAME = 'coin_game';
    case LUCKY_GIFT = 'lucky_gift';
    case CASHBACK = 'cashback';
    case VIP = 'vip';
    case PACK = 'packs';
    case GIFT = 'gifts';
    case RETURN_CHAGE = 'return_charge';
    case CREATE_ROOM = 'create_room';
    case INVITATION_CODE = 'invitation_code';
    case INVITATION_CHARGE_EARNINGS = 'invitation_charge_earnings';
    
    public function meta(): array
    {
        return match ($this) {
            self::ADMIN_CHARGES => [
                'sub_type' => 'charges',
                'item_name' => 'admin',
                'queue_job' => null,
            ],
            self::BD_CHARGES => [
                'sub_type' => 'charges',
                'item_name' => 'BD',
                'queue_job' => null,
            ],
            self::APP_CHARGE => [
                'sub_type' => 'charges',
                'item_name' => 'app_charges',
                'queue_job' => null,
            ],
            
            self::ROOM_COMMENT => [
                'sub_type' => 'rooms',
                'item_name' => 'Special Bar',
                'queue_job' => null,
            ],

            self::HUAWEI_PAY => [
                'sub_type' => 'charges',
                'item_name' => 'huawei_pay',
                'queue_job' => null,
            ],
            self::GOOGLE_PAY => [
                'sub_type' => 'charges',
                'item_name' => 'google_pay',
                'queue_job' => null,
            ],
            self::PAYMENT => [
                'sub_type' => 'charges',
                'item_name' => 'payment',
                'queue_job' => null,
            ],
            self::ROOM_TARGET => [
                'sub_type' => 'rooms',
                'item_name' => 'room_target',
                'queue_job' => null,
            ],
            self::CP => [
                'sub_type' => 'cps',
                'item_name' => 'room_target',
                'queue_job' => null,
            ],
            self::CPS => [
                'sub_type' => 'cps',
                'item_name' => 'cps',
                'queue_job' => null,
            ],
            self::EXCHANGE => [
                'sub_type' => 'users',
                'item_name' => 'exchanges_diamonds',
                'queue_job' => null,
            ],
            self::FAMILY => [
                'sub_type' => 'families',
                'item_name' => 'create_family',
                'queue_job' => null,
            ],
            self::BACKGROUND_IMAGES => [
                'sub_type' => 'request_background_images',
                'item_name' => 'background',
                'queue_job' => null,
            ],
            self::DAILY_GIFT => [
                'sub_type' => 'daily_gifts',
                'item_name' => 'daily_gift',
                'queue_job' => null,
            ],
            self::PK => [
                'sub_type' => 'pk_events',
                'item_name' => 'rewards',
                'queue_job' => null,
            ],
            self::WEEKLY_STAR => [
                'sub_type' => 'weekly_stars',
                'item_name' => 'rewards',
                'queue_job' => null,
            ],
            self::CHARGE_EVENT => [
                'sub_type' => 'charge_events',
                'item_name' => 'rewards',
                'queue_job' => null,
            ],
            self::LUCK_BOX => [
                'sub_type' => 'lucky_boxs',
                'item_name' => 'lucky_box',
                'queue_job' => null,
            ],
            
            self::COIN_GAME => [
                'sub_type' => 'coin_game_users',
                'item_name' => 'coin_game',
                'queue_job' => \App\Jobs\LogUserCumulativeCoinProfit::class,
            ],
            self::LUCKY_GIFT => [
                'sub_type' => 'gifts',
                'item_name' => 'lucky_gift',
                'queue_job' => \App\Jobs\LogUserCumulativeCoinProfit::class,
            ],
            self::CASHBACK => [
                'sub_type' => 'lucky_gifts', 
                'item_name' => 'cashback',
                'queue_job' => \App\Jobs\LogUserCoinProfit::class,
            ],
            self::VIP => [
                'sub_type' => 'o_vips',
                'item_name' => 'vip',
                'queue_job' => null,
            ],
            self::PACK => [
                'sub_type' => 'packs',
                'item_name' => 'pack',
                'queue_job' => null,
            ],
            self::GIFT => [
                'sub_type' => 'gift_logs',
                'item_name' => 'gift',
                'queue_job' => \App\Jobs\LogUserCoinProfit::class,
            ],
            self::RETURN_CHAGE => [
                'sub_type' => 'return_charges',
                'item_name' => 'return_charges',
                'queue_job' => null,
            ],
            self::CREATE_ROOM => [
                'sub_type' => 'rooms',
                'item_name' => 'rooms',
                'queue_job' => null,
            ],
            self::INVITATION_CODE => [
                'sub_type' => 'invitation_code',
                'item_name' => 'invitation_code',
                'queue_job' => null,
            ],
            self::INVITATION_CHARGE_EARNINGS => [
                'sub_type' => 'invitation_charge_earnings',
                'item_name' => 'invitation_charge_earnings',
                'queue_job' => null,
            ],

            
            
            
        };
    }
}

