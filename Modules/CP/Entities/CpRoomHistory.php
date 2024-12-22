<?php

namespace Modules\CP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CP\Database\factories\CpRoomHistoryFactory;

class CpRoomHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

}
