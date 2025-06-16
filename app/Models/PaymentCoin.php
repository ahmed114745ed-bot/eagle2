<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentCoin extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = ['id'];

    protected $casts = [
        'fields' => 'array',
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class, 'item_id', 'id');
    }

    public function coins()
    {
        return $this->hasMany(Coin::class, 'payment_gateway_id');
    }
}
