<?php

namespace Utd\LuckyBox\Entities;

use App\Models\Gift; // App\Models\Gift safely aliases Utd\Gifts\Entities\Gift when package is installed
use App\Models\User;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
