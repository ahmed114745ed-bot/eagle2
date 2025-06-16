<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = ['id'];

    public function serverCountries()
    {
        return $this->hasMany(ServerCountry::class, 'server_id');
    }
}
