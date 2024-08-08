<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'datetime',
    ];
    protected $fillable=['id','charger_id','charger_type','user_id','user_type','amount','amount_type','balance_before','agency_id','is_used_transferred'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sender()
    {
        return $this->hasOne(User::class, 'id', 'charger_id');
    }

    public function receiver()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
