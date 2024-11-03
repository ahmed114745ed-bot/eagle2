<?php

namespace App\Enums;

enum ConfigCategory : string
{
    case SYSTEM_SETTINGS = 'system_settings';
    case TARGET_SETTINGS = 'target_settings';
    case ZEGO_SETTINGS = 'zego_settings';
    case SMS_SETTINGS = 'sms_settings';
    case FAMILY_SETTINGS = 'family_settings';
    case PAYMENT_SETTINGS = 'payment_settings';
    case ROOM_SETTINGS = 'room_settings';
    case AGENCY_SETTINGS = 'agency_settings';
    case LEVEL_SETTINGS = 'level_settings';


    public static function getOptions(): array
    {
        return array_column(self::cases(), 'value');
    }
}
