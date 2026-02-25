<?php

namespace Utd\Pk\Entities;

use App\Models\User;
use App\Traits\EventModel;
use App\Traits\TimestampsWithTimezone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PkEvent extends Model
{
    use EventModel, HasFactory, TimestampsWithTimezone;

    protected $guarded = [];

    protected $appends = ['start_date_local', 'end_date_local'];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function rewards()
    {
        return $this->hasMany(PkReward::class, 'pk_event_id');
    }

    public function WinnersPK()
    {
        return $this->hasMany(PkWinner::class, 'pk_event_id');
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $dataLang = self::checkDateLanguage($model->attributes['start_date']);
            if ($dataLang === 'arabic') {

                $model->start_date = self::convertArabicNumbers($model->attributes['start_date']);
            } else {
                $model->start_date = $model->attributes['start_date'];
            }
            $model->end_date = Carbon::createFromFormat('Y-m-d', $model->attributes['start_date'])->addWeek();
            $model->admin_id = Auth::id();
        });

        self::saving(function ($model) {
            if ($model->isDirty('start_date')) {
                $dataLang = self::checkDateLanguage($model->attributes['start_date']);
                if ($dataLang === 'arabic') {

                    $model->start_date = self::convertArabicNumbers($model->attributes['start_date']);
                } else {
                    $model->start_date = $model->attributes['start_date'];
                }
                $model->end_date = Carbon::createFromFormat('Y-m-d', $model->attributes['start_date'])->addWeek();
                $model->editor_id = Auth::id();
            }
        });
    }
}
