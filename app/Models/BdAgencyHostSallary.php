<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BdAgencyHostSallary extends Model
{
    use HasFactory;

    protected $fillable = [
        'bd_id',
        'user_id',
        'agency_id',
        'amount',
        'user_sallary',
        'agency_sallary',
        'month',
        'year',
        'bd_user_id'
    ];

    public function bd()
    {
        return $this->belongsTo(Bd::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
