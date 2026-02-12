<?php

namespace Utd\Achievements\Enums;

enum AchievementType: string
{
    case GIFT_TARGET = 'gift_target';
    case RECHARGE_TARGET = 'recharge_target';
    case ROOM_TARGET = 'room_target';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getName(): string
    {
        $ucfirst = ucfirst(mb_strtolower($this->name));

        return str_replace('_', ' ', $ucfirst);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
