<?php

namespace App\Models;

use App\Traits\PreventDeleteIfCreatedByDeveloper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BanRoom extends Model
{
    use PreventDeleteIfCreatedByDeveloper;

    protected $table='bans_rooms';
    protected $guarded = ['id'];

    public function room()
    {
        return $this->hasOne(Room::class, 'id', 'room_id');
    }

    // public function banType()
    // {
    //     return $this->belongsTo(BanType::class,);
    // }

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
