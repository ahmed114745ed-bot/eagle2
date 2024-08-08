<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanType extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function bans()
    {
        return $this->hasMany(Ban::class, 'ban_type_id');
    }
}
