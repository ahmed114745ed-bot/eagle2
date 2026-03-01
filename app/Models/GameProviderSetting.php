<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class GameProviderSetting extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $casts = [
    //     'app_key' => 'encrypted',
    //     'app_secret' => 'encrypted',
    // ];


    public function setAppKeyAttribute($value)
    {
        if ($value) {
            $this->attributes['app_key'] = Crypt::encryptString($value);
        }
    }

    public function getAppKeyAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
    public function setAppSecretAttribute($value)
    {
        if ($value) {
            $this->attributes['app_secret'] = Crypt::encryptString($value);
        }
    }

    public function getAppSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
}
