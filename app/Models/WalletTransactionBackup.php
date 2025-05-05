<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransactionBackup extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'value',
        'description',
        'description_data',
        'original_created_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}