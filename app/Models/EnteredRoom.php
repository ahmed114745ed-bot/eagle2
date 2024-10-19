<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnteredRoom extends Model
{
    protected $table = 'entered_rooms';
    protected $guarded = ['id'];

    public function room()
    {
        return $this->belongsTo(Room::class, 'rid', 'id');
    }

}
