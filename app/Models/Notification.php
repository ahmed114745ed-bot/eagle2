<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $fillable = ['key'];

    public function translations()
    {
        return $this->hasMany(NotificationTranslation::class, 'notification_id');
    }
}
