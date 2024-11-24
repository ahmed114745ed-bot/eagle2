<?php

namespace App\Enums;

enum PaymentType: string
{
    case FAWRY = 'fawry';
    case GOOGLE_PAY = 'google_pay';
    case SKY_PAY = 'sky_pay';
    case STRIP = 'strip';
    case OPAY = 'opay';
   
    


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
