<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FairLuckLossLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contribution_bank',
        'jackpot_pity',
        'loss_momentum',
        'loss_score',
        'rotation_count',
        'cooldown_until',
        'last_high_multiplier_at',
    ];

    protected $casts = [
        'cooldown_until' => 'datetime',
        'last_high_multiplier_at' => 'datetime',
    ];
}
