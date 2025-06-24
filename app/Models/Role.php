<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDeleteIfCreatedByDeveloper;

class Role extends Model
{
    use HasFactory ,PreventDeleteIfCreatedByDeveloper;
    protected $table = 'admin_roles';

    protected $guarded = ['permissions'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'admin_role_permissions', 'role_id', 'permission_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::preventDeleteByDeveloper();
        static::preventCreateByDeveloper();
    
    }
}
