<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Bd extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'admin_users';

    protected $attributes = [
        'type' => 'bd',
    ];

    public function appUser()
    {
        return $this->belongsTo(User::class, 'app_id');
    }

    public function agencies()
    {
        return $this->hasMany(Agency::class, 'bd_id', 'app_id');
    }

    public function transactions()
    {
        return $this->hasMany(Charge::class, 'charger_id', 'app_id')
            ->where('user_charger_type', 'bd');
    }

    public function getAgenciesCountAttribute()
    {
        return $this->agencies()->count();
    }

    public function salaries()
    {
        return $this->hasMany(BDSallary::class, 'bd_id', 'app_id');
    }

    public function getTotalSalaryAttribute()
    {
        return $this->salaries()->sum('sallary');
    }

    public function getNetSallaryAttribute()
    {
        return $this->salaries()->sum(DB::raw('sallary - cat_amount'));
    }

    protected static function booted(): void
    {

        self::addGlobalScope('bdOnly', function (Builder $builder) {
            $builder->where('type', 'bd');
        });

        self::deleting(function (Bd $bd) {
            $defaultBd = self::where('default', 1)
                ->where('id', '!=', $bd->id)
                ->first();

            if ($defaultBd) {
                Agency::where('bd_id', $bd->app_id)
                    ->update(['bd_id' => $defaultBd->app_id]);
            } else {
                throw new Exception('لا يوجد BD افتراضي لنقل الوكالات إليه.');
            }
            $userApp = User::find($bd->app_id);
            if ($userApp) {
                $userApp->is_bd = 0;
                $userApp->save();
            }
        });
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->type = 'bd';

            if ($model->default) {
                static::query()->update(['default' => 0]);
                Agency::where(function ($query) {
                    $query->whereNull('bd_id')
                        ->orWhere('bd_id', 0);
                })->update(['bd_id' => $model->app_id]);
            }
        });

        self::updating(function ($model) {
            if ($model->default) {
                static::where('id', '!=', $model->id)->update(['default' => 0]);
                Agency::where(function ($query) {
                    $query->whereNull('bd_id')
                        ->orWhere('bd_id', 0);
                })->update(['bd_id' => $model->app_id]);
            }
        });
    }
}
