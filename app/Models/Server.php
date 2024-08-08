<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function serverCountries()
    {
        return $this->hasMany(ServerCountry::class, 'server_id');
    }
}
