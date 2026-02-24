<?php

namespace Utd\Vip\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipPrev extends Model
{
    use HasFactory;

    protected $table = 'vip_prev';

    public $timestamps = false;

    protected $fillable = [
        'o_vip_id',
        'o_vip_privilege_id',
    ];
}
