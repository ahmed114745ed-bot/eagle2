<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserHistoryReward extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'receive_type',
        'rewardable_id',
        'rewardable_type',
        'extra',
        'sub_type',
        'is_deleted',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'extra' => 'array',
    ];

    public function rewardable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getReceiveNameAttribute()
    {
    }
}
