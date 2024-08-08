<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected static function boot()
{
    parent::boot();

    // static::saving(function ($model) {
    //     // Transform the 'content' column to JSON format
    //     $model->content = json_encode($model->content);
    // });
}


}


