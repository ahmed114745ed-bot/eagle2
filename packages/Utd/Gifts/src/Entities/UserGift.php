<?php

namespace Utd\Gifts\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGift extends Model
{
    use HasFactory;

    protected $table = 'user_gifts';

    protected $fillable = ['gift_id', 'user_id', 'quantity', 'expire'];

    public function gift()
    {
        return $this->belongsTo(Gift::class, 'gift_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
