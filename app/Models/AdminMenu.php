<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminMenu extends Menu
{
    

    public function getTitleAttribute($value)
    {
        return \Str::contains(request()->fullUrl(), 'edit') ? $value : __($value);
    }
}
