<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FairLuckLossLedger extends Model
{
    use HasFactory;

    protected $table = 'fair_luck_loss_ledgers';

    protected $fillable = [
        'user_id',
        'loss_score',
        'contribution_bank',
        'jackpot_pity',
        'loss_momentum',
    ];

    protected $casts = [
        'loss_score' => 'float',
        'contribution_bank' => 'integer',
        'jackpot_pity' => 'integer',
        'loss_momentum' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
