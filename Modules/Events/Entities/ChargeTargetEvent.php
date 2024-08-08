<?php

namespace Modules\Events\Entities;

use App\Models\Ware;
use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Events\Database\factories\TargetEventFactory;

class ChargeTargetEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $table   = 'charge_events';
    public function rewards()
    {
        return $this->hasMany(RewardTarget::class, 'charge_event_id')->with("ware",'vip');
    }

    public function ware()
    {
        return $this->rewards->ware();
    }

    public function getWareAttribute()
    {
        $wares = $this->rewards->map(function ($reward) {
            return $reward->ware;
        })->filter();

        return $wares;
    }

}
