<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bd extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'admin_users';

    protected $guarded = [];

    protected $attributes = [
        'type' => 'bd',
    ];

    public function appUser()
    {
        return $this->belongsTo(User::class, 'app_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class, 'parent_id');
    }

    public function agencies()
    {
        return $this->hasMany(Agency::class, 'bd_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Charge::class, 'charger_id', 'id')
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
        // scope يجيب بس ال BD
        self::addGlobalScope('bdOnly', function (Builder $builder) {
            $builder->where('type', 'bd');
        });
    
        // عند الحذف
        self::deleting(function (Bd $bd) {
            // نجيب الافتراضي الآخر لنفس السوبر ادمن
            $defaultBd = self::where('default', 1)
                ->where('parent_id', $bd->parent_id)
                ->where('id', '!=', $bd->id)
                ->first();
    
            if ($defaultBd) {
                // ننقل الوكالات لل BD الافتراضي
                Agency::where('bd_id', $bd->id)
                    ->update(['bd_id' => $defaultBd->id]);
            } else {
                throw new Exception('لا يوجد BD افتراضي آخر لنقل الوكالات إليه.');
            }
    
            // نفصل علاقة المستخدم لو مرتبطة
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
    
        // عند الإنشاء
        self::creating(function (Bd $model) {
            $model->type = 'bd';
    
            if ($model->default) {
                // نخلي باقي BDs لنفس السوبر = 0
                static::where('parent_id', $model->parent_id)
                    ->update(['default' => 0]);
    
                // نربط الوكالات اللي مالهاش BD بالافتراضي الجديد
                Agency::where(function ($query) {
                    $query->whereNull('bd_id')
                        ->orWhere('bd_id', 0);
                })->update(['bd_id' => $model->id]);
            }
        });
    
        // عند التحديث
        self::updating(function (Bd $model) {
            if ($model->default) {
                // نخلي الافتراضي واحد بس لنفس السوبر
                static::where('parent_id', $model->parent_id)
                    ->where('id', '!=', $model->id)
                    ->update(['default' => 0]);
    
                Agency::where(function ($query) {
                    $query->whereNull('bd_id')
                        ->orWhere('bd_id', 0);
                })->update(['bd_id' => $model->id]);
            }
    
            // لو غيرنا app_id → نفضي القديم
            if ($model->isDirty('app_id')) {
                $oldAppId = $model->getOriginal('app_id');
                if ($oldAppId) {
                    $userApp = User::find($oldAppId);
                    if ($userApp) {
                        $userApp->is_bd = 0;
                        $userApp->type_user = 0;
                        $userApp->agency_id = 0;
                        $userApp->save();
                    }
                }
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

    public function salaries()
    {
        return $this->hasMany(BdSalary::class, 'bd_id', 'id');
    
    }
}
