<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyUserJob extends Model
{
    protected $guarded=['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function agency()
    {
        return $this->belongsTo(\App\Models\Agency::class);
    }
}
