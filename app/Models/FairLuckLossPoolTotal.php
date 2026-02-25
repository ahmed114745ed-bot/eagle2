<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FairLuckLossPoolTotal extends Model
{
    use HasFactory;

    protected $table = 'fair_luck_loss_pool_totals';

    protected $fillable = [
        'balance',
        'lifetime_contributed',
        'lifetime_withdrawn',
    ];

    protected $casts = [
        'balance' => 'integer',
        'lifetime_contributed' => 'integer',
        'lifetime_withdrawn' => 'integer',
    ];

    public static function getOrCreate()
    {
        return self::firstOrCreate(
            [],
            [
                'balance' => 0,
                'lifetime_contributed' => 0,
                'lifetime_withdrawn' => 0,
            ]
        );
    }
}
