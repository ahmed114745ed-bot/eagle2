<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminCoinRate extends Model
{
    protected $fillable = ['admin_id', 'rate'];

    public function admin()
    {
        return $this->belongsTo(AdminUser::class, 'admin_id');
    }
}
