<?php
namespace Utd\UsersWallet\Entities;

use Illuminate\Database\Eloquent\Model;

class UserWithdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
