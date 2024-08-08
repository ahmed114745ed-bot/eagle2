<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner_pid_target extends Model
{
    use HasFactory;

    protected $table = 'OwnerAgancyBide';
    protected $fillable = ['user_id', 'total','agency_id','usd_pid','month' ,'year','totalowner','total']; // Add 'total' to the array



   
}
