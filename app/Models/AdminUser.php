<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class AdminUser extends Administrator
{
    protected $table = 'admin_users';


    public function user()
    {
        return $this->belongsTo(User::class ,'app_id');
    }


    public function managerAgencies(): HasManyThrough
    {
        return $this->hasManyThrough(Agency::class, User::class, 'id', 'agency_manger_id', 'app_id', 'id');
    }
}
