<?php

namespace Modules\SwitchAccount\Entities;

use App\Models\User;
use App\Models\Ware;
use Encore\Admin\Form\Field\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class UserAccount extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function parent_user()
    {
        return $this->hasOne(User::class,'parent_user_id');
    }

    public function child_user()
    {
        return $this->hasOne(User::class,'child_user_id');
    }
}
