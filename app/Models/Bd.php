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
        return $this->hasMany(Agency::class, 'bd_id', 'id');
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


    public function getTotalSalaryAttribute()
    {
        return $this->bdSalaries()->sum('salary');
    }
    public function getTotalCutAttribute()
    {
        return $this->bdSalaries()->sum('cut_amount');
    }

    public function getNetSallaryAttribute()
    {
        $userSallary = $this->bdSalaries()
        ->sum(DB::raw('salary - cut_amount'));

       return floor($userSallary);
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
                Agency::where('bd_id', $bd->id)
                    ->update(['bd_id' => $defaultBd->id]);
            } else {
                throw new Exception('لا يوجد BD افتراضي لنقل الوكالات إليه.');
            }
            $userApp = User::find($bd->app_id);
            if ($userApp) {
                $userApp->is_bd = 0;
                $userApp->type_user = 0;
                $userApp->agency_id = 0;
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
                })->update(['bd_id' => $model->id]);
            }
        });

        self::updating(function ($model) {
            if ($model->default) {
                static::where('id', '!=', $model->id)->update(['default' => 0]);
                Agency::where(function ($query) {
                    $query->whereNull('bd_id')
                        ->orWhere('bd_id', 0);
                })->update(['bd_id' => $model->id]);
            }
        });
    }


    public function incrementCutAmountInBdSallary(int $amount)
    {
        $lastBdSalary = $this->bdSalaries()->latest()->first();

        if ($lastBdSalary) {
            $newAmount = max(0, $lastBdSalary->cut_amount + $amount);
            $lastBdSalary->update(['cut_amount' => $newAmount]);

            return true;
        }

        return false;
    }

    public function bdSalaries()
    {
        return $this->hasMany(BdSalary::class,  'bd_id', 'id');
    }
    public function getBdSalaryAttribute()
    {
        $userSallary = $this->bdSalaries()
            ->sum(DB::raw('salary - cut_amount'));

        return floor($userSallary);
    }
}
