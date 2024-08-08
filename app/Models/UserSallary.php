<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\FixedTarget\Enums\TargetType;

class UserSallary extends Model
{
    protected $table = 'user_sallaries';

public bool $allowSaving = true;

    protected $guarded = ['id'];

    protected $casts = [
        'agency_sallary' => 'double',
        'sallary' => 'double',
        'extras' => 'json',
        'type' => TargetType::class,
        'year' => 'integer',
        'month' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
