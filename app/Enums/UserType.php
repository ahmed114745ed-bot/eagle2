<?php

namespace App\Enums;

enum UserType: int
{
    case User = 0;
    case Host = 1;
    case HostAgent = 2;
    case ShippingAgent = 3;
    case HostAndShippingAgent = 4;
    case Admin = 5;


    public function label(): string
    {
        return trans("api.{$this->name}");
    }

    public static function list(): array
    {
        return array_reduce(
            self::cases(),
            fn($carry, $case) => $carry + [$case->value => $case->label()],
            []
        );
    }
}
