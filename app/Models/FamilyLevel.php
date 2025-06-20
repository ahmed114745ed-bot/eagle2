<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class FamilyLevel extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'family_levels';

    protected $fillable = [
        'name',
        'img',
        'exp',
        'type',
        'members',
        'admins',
    ];
}
