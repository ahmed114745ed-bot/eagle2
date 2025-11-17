<?php

namespace Modules\MixStream\Entities;

use Illuminate\Database\Eloquent\Model;

class MixStreamInvitation extends Model
{
    protected $fillable = ['mix_stream_id', 'inviter_user_id', 'invitee_user_id', 'status'];
}
