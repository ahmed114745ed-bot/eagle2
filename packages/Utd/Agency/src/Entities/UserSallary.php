<?php

namespace Utd\Agency\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Modules\FixedTarget\Enums\TargetType;

class UserSallary extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'user_sallaries';

    protected $guarded = [];

    protected $casts = [
        'agency_sallary' => 'double',
        'sallary' => 'double',
        'extras' => 'json',
        'type' => TargetType::class,
        'year' => 'integer',
        'month' => 'integer',
    ];

    public function user()
    {
        $userClass = config('agency-package.models.user', \App\Models\User::class);
        return $this->belongsTo($userClass, 'user_id', 'id');
    }

    public function agency()
    {
        $agencyClass = config('agency-package.models.agency', \Utd\Agency\Entities\Agency::class);
        return $this->belongsTo($agencyClass, 'user_agency_id');
    }

    public function target()
    {
        $targetClass = config('agency-package.models.target', \App\Models\Target::class);
        return $this->belongsTo($targetClass, 'target_id');
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            if ($model->agency_id) {
                clearAgencyCache($model->agency_id);
            }
        });

        static::deleted(function ($model) {
            if ($model->agency_id) {
                clearAgencyCache($model->agency_id);
            }
        });
    }
}
