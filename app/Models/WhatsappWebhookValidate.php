<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappWebhookValidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public $timestamps = false;
//whatsapp_webhook_validates
    public function getCreatedAtColumn()
    {
        return 'id';
    }

    protected $fillable = [
        'uuid',
        'verification_code',
        'phone_number',
        'profile_name',
        'status',
        'app_id',
        'requested_at',
        'expires_at',
    ];


    public function scopeValidated(Builder $query)
    {
        return $query->where('status', 'validated')->where('expires_at', '>=', now());
    }

    public function getUserData()
    {
        return [
            'phone' => $this->phone_number ?? '',
            'name' => $this->profile_name
        ];
    }


}
