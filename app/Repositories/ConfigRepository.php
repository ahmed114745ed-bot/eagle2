<?php

namespace App\Repositories;

use App\Models\Config;
use Illuminate\Database\Eloquent\Model;

class ConfigRepository
{
    public function getAll()
    {
        return Config::all();
    }
}