<?php

namespace App\Enums;

enum PermissionType: string
{
    case BD = 'bd';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super_admin';


    public static function getOptions(): array
    {
        return array_column(self::cases(), 'value');
    }

    // Method to get translated options
    public static function getTranslatedOptions(): array
    {
        return translateCategory(self::getOptions());
    }
}
