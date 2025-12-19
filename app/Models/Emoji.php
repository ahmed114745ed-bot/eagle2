<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class Emoji extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'emojis';

    protected $guarded = [];

     public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];
}
