<?php

namespace Modules\Charizma\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExtraDataInRoom extends Model
{
    use HasFactory;

    protected $table = 'extra_data_in_rooms';

    protected $fillable = ['user_id','total','room_id'];


}
