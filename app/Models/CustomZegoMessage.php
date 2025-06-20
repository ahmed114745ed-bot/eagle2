<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomZegoMessage extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class, 'gift_id');
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $user = User::find($model->user_id);
            $model->room_id = $user->ownerRoom->id;
        });

        self::saving(function ($model) {
            if ($model->isDirty('user_id')) {
                $user = User::find($model->user_id);
                $model->room_id = $user->ownerRoom->id;
            }
        });
    }
}
