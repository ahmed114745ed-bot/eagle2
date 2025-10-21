<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class OfficialMessageAdmin extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'official_messages';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userOfficialMessages()
    {
        return $this->hasMany(UserOfficialMessage::class);
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            // Determine which request field exists (priority order)
            $featureIdsText = request('feature_ids')
                ?? request('agency_ids')
                ?? request('shipping_agency_ids');

            if (is_array($featureIdsText)) {
                $featureIds = array_filter($featureIdsText); // remove nulls
                $featureIdsText = implode(',', $featureIds);
            }

            // Assign to feature_ids column
            $model->feature_ids = $featureIdsText;

            // ✅ Remove the raw arrays from the request before save
            unset($model->agency_ids);
            unset($model->shipping_agency_ids);
           
        });
    }
}
