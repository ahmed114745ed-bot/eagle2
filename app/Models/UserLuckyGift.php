<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLuckyGift extends Model
{
    use HasFactory;

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
