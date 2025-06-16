<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Menu;
use Str;

class AdminMenu extends Menu
{
    public function getTitleAttribute($value)
    {
        return Str::contains(request()->fullUrl(), 'edit') ? $value : __($value);
    }
}
