<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDayProgress extends Model
{
    protected $table = 'user_day_progress';
    public function day()
    {
        return $this->belongsTo(Day::class, 'day_id', 'id');
    }
    use HasFactory;
}
