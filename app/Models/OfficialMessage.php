<?php

namespace App\Models;

use App\Facades\CustomNotification;
use Illuminate\Database\Eloquent\Model;

class OfficialMessage extends Model
{
    protected $table = 'official_messages';
    protected $guarded = ['id'];


    public function user(){
        return $this->belongsTo(User::class);
    }
    
}
