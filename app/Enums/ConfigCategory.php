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

    // Method to get translated options
    public static function getTranslatedOptions(): array
    {
        return translateCategory(self::getOptions());
    }

    public static function getLinkedStringsByValue(string $value): ?array
    {
        return match ($value) {
            self::SYSTEM_SETTINGS->value => ['setting1', 'setting2', 'setting3'],
            self::TARGET_SETTINGS->value => ['target1', 'target2', 'target3'],
            self::ZEGO_SETTINGS->value => ['zego1', 'zego2', 'zego3'],
            self::SMS_SETTINGS->value => ['sms1', 'sms2', 'sms3'],
            self::FAMILY_SETTINGS->value => ['family1', 'family2', 'family3'],
            self::PAYMENT_SETTINGS->value => ['payment1', 'payment2', 'payment3'],
            self::ROOM_SETTINGS->value => ['room1', 'room2', 'room3'],
            self::AGENCY_SETTINGS->value => ['agency1', 'agency2', 'agency3'],
            self::LEVEL_SETTINGS->value => ['level1', 'level2', 'level3'],
            default => null,
        };
    }
}
