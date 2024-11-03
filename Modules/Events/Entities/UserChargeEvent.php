<?php

namespace Modules\Events\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChargeEvent extends Model
{
    protected $guarded=['id'];

    public function winner()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(ChargeTargetEvent::class);
    }

}
