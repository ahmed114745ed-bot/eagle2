<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BDSallary extends Model
{
    use HasFactory;
    protected $table = 'bd_sallaries';

    protected $fillable = [
        'bd_id',
        'agency_id',
        'sallary',
        'cut_amount',
        'month',
        'year',
        'is_paid',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }
}
