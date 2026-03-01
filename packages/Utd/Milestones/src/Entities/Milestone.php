<?php

namespace Utd\Milestones\Entities;

use App\Helpers\Common;
use App\Models\User;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Milestone extends Model
{
    protected $fillable = ['name','slug' , 'description', 'is_active'];



    public function rewards()
    {
        return $this->hasMany(MilestoneReward::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_milestones')
                    ->withPivot('achieved_at')
                    ->withTimestamps();
    }
}
