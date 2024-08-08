<?php

namespace Modules\Whatsapp\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'uuid',
        'code',
        'app_id',
        'requested_at',
        'expires_at',
    ];

    public function isExpire(): bool
    {
        $expireAt = Carbon::parse($this->expires_at);
        $now      = Carbon::now();
        return $expireAt->lt($now);
    }

    public function getCreatedAtColumn()
    {
        return 'requested_at';
    }
}
