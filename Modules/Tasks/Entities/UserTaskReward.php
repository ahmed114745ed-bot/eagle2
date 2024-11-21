<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTaskReward extends Model
{
    protected $fillable = [
        'user_id',
        'task_reward_id',
        'created_at'
    ];
    use HasFactory;
}
