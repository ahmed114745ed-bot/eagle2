<?php

namespace Modules\Whatsapp\Entities;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
class WhatsappApp extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'uuid',
        'username',
        'password',
        'webhook_url',
        'phone',
        'phone_id',
    ];

    protected $hidden = [
        'password'
    ];

    /**
     * Find a model by its UUID.
     *
     * @param string $uuid
     * @param array $columns
     * @return Model|null
     */
    public static function find($uuid, $columns = ['*']): ?Model
    {
        return static::where('uuid', $uuid)->first($columns);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid =  uuid_create();
        });
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }


}
