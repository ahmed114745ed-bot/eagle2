<?php

namespace App\Models;

use App\Traits\PreventDeleteIfCreatedByDeveloper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
    use PreventDeleteIfCreatedByDeveloper;
    protected $guarded = ['id'];

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'uid');
    }

    public function banType()
    {
        return $this->belongsTo(BanType::class);
    }

    public function staff()
    {
        return $this->belongsTo(Admin::class, 'staff_id');
    }
    protected static function boot()
    {
        parent::boot();
        static::preventDeleteByDeveloper();
        static::preventCreateByDeveloper();
        
    
    }

}
