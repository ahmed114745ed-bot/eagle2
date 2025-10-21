<?php

namespace App\Models;


use App\Helpers\Common;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
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
            $featureIdsText = request('feature_ids');
            if (is_array(request('feature_ids'))) {
                $featureIds = array_filter(request('feature_ids')); // remove nulls
                $featureIdsText = implode(',', $featureIds);

                unset(request()['feature_ids']);
            }
            $model->feature_ids  = $featureIdsText;

            // $file = request('img', $model->img);

            //     if ($file instanceof UploadedFile) {
            //         $url = Common::upload(DIRECTORY_SEPARATOR . 'official_message', $file);
            //     }
            //     $model->img = $url ?? '';

            
        });
    }
}
