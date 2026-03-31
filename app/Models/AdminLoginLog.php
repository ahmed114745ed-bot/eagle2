<?php

namespace App\Models;

use Encore\Admin\Auth\Database\OperationLog;

class AdminLoginLog extends OperationLog
{
    protected $fillable = [
        'user_id',
        'path',
        'method',
        'ip',
        'input',
        'country',
        'city',
        'region',
        'latitude',
        'longitude',
        'device',
        'platform',
        'platform_version',
        'browser',
        'browser_version',
        'user_agent',
        'login_at',
        'logout_at',
        'session_duration_minutes',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    /**
     * Scope to only get login entries.
     */
    public function scopeLogins($query)
    {
        return $query->where('path', 'admin/login')->where('method', 'POST');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }
}
