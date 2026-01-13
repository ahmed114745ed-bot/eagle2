<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AgencyJoinRequest extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'agency_join_requests';

    protected $guarded = [];

    public function admin()
    {
        return $this->hasOne(Agent::class, 'id', 'change_status_admin_id');
    }

    public function changeStatusAdmin(): MorphTo
    {
        // If `change_status_admin_id` can be either Admin or User
        return $this->morphTo(null, 'App\Models\Admin', 'App\Models\User', 'change_status_admin_id');
    }

    // Or if you only want Admin first, fallback to User, you can define 2 relationships:
    public function changeStatusAdminUser()
    {
        return $this->belongsTo(User::class, 'change_status_admin_id');
    }

    public function changeStatusAdminAdmin()
    {
        return $this->belongsTo(Admin::class, 'change_status_admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function requsers()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function update(array $attributes = [], array $options = [])
    {
        if ($this->agency_id === 0) {
            $attributes['type_user'] = 0;
        }

        return parent::update($attributes, $options);
    }

    protected static function booted()
    {
        self::saved(function ($model) {
            if ($model->agency_id) {
                clearAgencyCache($model->agency_id);
            }
        });

        self::deleted(function ($model) {
            if ($model->agency_id) {
                clearAgencyCache($model->agency_id);
            }
        });
    }
}
