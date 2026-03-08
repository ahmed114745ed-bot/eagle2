<?php

namespace Utd\UsersWallet\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoreWallet extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $fillable = [
        'coins',
    ];
}
