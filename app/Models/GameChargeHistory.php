<?php

namespace App\Models;
use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameChargeHistory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {

            $model->admin_id = Auth::id();

        });
    }
}
