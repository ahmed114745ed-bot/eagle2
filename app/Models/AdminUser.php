<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;

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


    public function managerAgenciesWithoutScope(): HasManyThrough
    {
        $related = (new Agency)->newQueryWithoutScopes()->getModel();
        $through = (new User)->newQuery()->getModel();
    
        return new HasManyThrough(
            $related,     // موديل Agency بدون سكوبات
            $through,     // موديل User
            'app_id',     // المفتاح الأجنبي على جدول users الذي يشير إلى admin_users (AdminUser->app_id = users.id)
            'agency_manger_id', // المفتاح الأجنبي على جدول agencies الذي يشير إلى users
            'app_id',     // المفتاح المحلي في admin_users
            'id'          // المفتاح المحلي في users
        );
    }
}
