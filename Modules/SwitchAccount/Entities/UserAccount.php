<?php

namespace Modules\SwitchAccount\Entities;

use App\Models\User;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = ['id'];

    public function parent_user()
    {
        return $this->hasOne(User::class, 'parent_user_id');
    }

    public function child_user()
    {
        return $this->hasOne(User::class, 'child_user_id');
    }
}
