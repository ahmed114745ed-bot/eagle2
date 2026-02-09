<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FairLuckTransaction extends Model
{
    use HasFactory;

    public $timestamps = false; // Only created_at in migration
    
    protected $fillable = [
        'user_id',
        'gift_id',
        'bet_amount',
        'is_winner',
        'multiplier',
        'profit_amount',
        'deviation_before',
        'calculated_probability',
        'is_beginner_protected',
        'protection_multiplier',
        'room_id',
        'created_at',
    ];

    protected $casts = [
        'is_winner' => 'boolean',
        'is_beginner_protected' => 'boolean',
        'created_at' => 'datetime',
        'bet_amount' => 'decimal:2',
        'profit_amount' => 'decimal:2',
        'deviation_before' => 'decimal:6',
        'calculated_probability' => 'decimal:4',
        'protection_multiplier' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }
}
