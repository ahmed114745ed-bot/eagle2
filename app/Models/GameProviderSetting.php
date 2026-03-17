<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class GameProviderSetting extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $casts = [
    //     'app_key' => 'encrypted',
    //     'app_secret' => 'encrypted',
    // ];

    protected $casts = [
        'webhook_routes' => 'array',
    ];


    public function setAppKeyAttribute($value)
    {
        if ($value) {
            $this->attributes['app_key'] = Crypt::encryptString($value);
        }
    }

    public function getAppKeyAttribute($value)
    {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            Log::warning("GameProviderSetting: Failed to decrypt app_key for provider [{$this->provider_code}]. The APP_KEY may have changed since this value was encrypted. Error: {$e->getMessage()}");
            return null; // Return null instead of crashing
        }
    }
}
