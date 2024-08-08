<?php

namespace Modules\Achievement\Enums;

enum TargetType : string
{

    case DEFAULT = 'default';
    case WEEKLY = 'week';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
}
