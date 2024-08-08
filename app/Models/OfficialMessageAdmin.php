<?php

namespace App\Models;

use App\Facades\CustomNotification;
use Illuminate\Database\Eloquent\Model;

class OfficialMessageAdmin extends Model
{
    protected $table = 'official_messages';
    protected $guarded = ['id'];


    public function user(){
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function(OfficialMessageAdmin $officialMessageAdmin){
            CustomNotification::officialMsg($officialMessageAdmin);
        });
    }

}
