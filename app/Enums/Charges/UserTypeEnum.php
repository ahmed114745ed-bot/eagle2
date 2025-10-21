<?php

namespace App\Enums\Charges;

enum UserTypeEnum: string
{
    const AGENCY = 'agency';
    const USER = 'user';
    const SUPER_ADMIN = 'super_admin';
    const AREA_MANAGER = 'area_manager';
}
