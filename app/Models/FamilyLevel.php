<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyLevel extends Model
{
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
