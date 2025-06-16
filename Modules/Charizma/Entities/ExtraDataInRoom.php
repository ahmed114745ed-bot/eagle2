<?php

namespace Modules\Charizma\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraDataInRoom extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $table = 'extra_data_in_rooms';

    protected $fillable = ['user_id', 'total', 'room_id'];
}
