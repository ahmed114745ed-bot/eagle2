<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EarnedDiamond extends Model
{
    use HasFactory;
    protected $table = 'earned_diamonds';
    protected $guarded = ['id'];

}
