<?php

namespace Utd\LuckyBox\Entities;

use App\Models\Gift;
use App\Models\User;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLuckyGift extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = [];

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
