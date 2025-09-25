<?php

namespace App\Enums\Charges;

enum ChargerTypeEnum: string
{
    case DASH = 'dash';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
