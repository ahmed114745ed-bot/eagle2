<?php

namespace Utd\Agency\Entities;

use App\Traits\AgencyAdditionalInfoTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Utd\Agency\Scopes\ShippingAgencyScope;
use Utd\Agency\Traits\ConfigurableModelsTrait;
use Utd\Agency\Traits\CreatedByTrait;
use Utd\Agency\Traits\PaymentGetWayTrait;
use Utd\Agency\Traits\TimestampsWithTimezone;

class ShippingAgency extends Model
{
    use AgencyAdditionalInfoTrait, ConfigurableModelsTrait, CreatedByTrait, PaymentGetWayTrait, SoftDeletes, TimestampsWithTimezone;

    protected $table = 'agencies';

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    public function chargeAgency()
    {
        $class = $this->getModuleClass('salary_transaction', 'charge_agency');

        return $class ? $this->hasOne($class, 'agency_id') : null;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo($this->getModelClass('country'));
    }

    public function charges()
    {
        return $this->hasMany($this->getModelClass('charge'), 'user_id', 'id')
            ->where('charger_type', 'agency');
    }

    public function senderCharges()
    {
        return $this->hasMany($this->getModelClass('charge'), 'charger_id', 'id')
            ->where('charger_type', 'agency');
    }

    public function receiveShippingAgencyCharges()
    {
        return $this->hasMany($this->getModelClass('charge'), 'user_id', 'id')
            ->where('user_type', 'agency');
    }

    public function Countries()
    {
        return $this->belongsToMany(
            $this->getModelClass('country'),
            'agency_countries',
            'agency_id',
            'country_id'
        )->withTimestamps();
    }

    public function salaryRequests()
    {
        $class = $this->getModuleClass('salary_transaction', 'salary_request');

        return $class ? $this->hasMany($class, 'agency_id') : null;
    }

    // Logic from SalaryTransferTrait
    public function getTransferSalaryAttribute()
    {
        $class = $this->getModuleClass('salary_transaction', 'agency_transfer_salary');
        if (! $class) {
            return 0;
        }

        return $class::query()->where('agency_id', $this->id)->sum(DB::raw('salary - cut_amount - pending_usd'));
    }

    public function getPendingSalaryAttribute()
    {
        $class = $this->getModuleClass('salary_transaction', 'agency_transfer_salary');
        if (! $class) {
            return 0;
        }

        return $class::query()->where('agency_id', $this->id)->sum('pending_usd');
    }

    public function mempers()
    {
        return $this->hasMany($this->getModelClass('user'), 'agency_id');
    }

    public function users()
    {
        return $this->hasMany($this->getModelClass('user'));
    }

    public function admins()
    {
        return $this->hasMany(AgencyUserJob::class, 'agency_id')
            ->where('type', 'requestManger');
    }

    public function owner()
    {
        return $this->belongsTo($this->getModelClass('user'), 'app_owner_id', 'id');
    }

    public function agencyManger()
    {
        return $this->belongsTo($this->getModelClass('user'), 'agency_manger_id', 'id');
    }

    public function dashOwner()
    {
        return $this->belongsTo($this->getModelClass('admin'), 'owner_id', 'id');
    }

    public function getSalaryAttribute()
    {
        return AgencySalary::query()
            ->where('agency_id', $this->id)
            ->where('is_paid', 0)
            ->sum(DB::raw('sallary - cut_amount'));
    }

    public function getTargetsAttribute()
    {
        return $this->getModelClass('user_target')::query()
            ->where('add_month', date('m'))
            ->where('add_year', date('Y'))
            ->first();
    }

    public function setSalaryAttribute()
    {
        $salary = AgencySalary::query()->where('agency_id', $this->id)->where('is_paid', 0)->sum(DB::raw('sallary - cut_amount'));
        $this->attributes['salary'] = $salary;

        return $salary;
    }

    public function getSalaryAttributeAgencyManger()
    {
        $salaryAgency = AgencySalary::query()->where('agency_id', $this->id)->where('is_paid', 0)->sum(DB::raw('sallary - cut_amount'));
        $attributes['salaryAgency'] = $salaryAgency;

        return $attributes;
    }

    public function AgencyUsersTargets()
    {
        return $this->hasMany($this->getModelClass('user_target'), 'agency_id');
    }

    public function UserTarget()
    {
        return $this->hasMany($this->getModelClass('user_target'), 'agency_id');
    }

    public function agencySalary()
    {
        return $this->hasOne(AgencySalary::class, 'agency_id')->orderByDesc('id')
            ->where('month', now()->month)->where('year', now()->year);
    }

    public function getLastMonthSalaryAttribute()
    {
        return $this->hasOne(AgencySalary::class, 'agency_id')->orderByDesc('id')
            ->where('month', now()->subMonth()->month)->where('year', now()->subMonth()->year)->where('is_paid', 0)->sum(DB::raw('sallary - cut_amount'));
    }

    public function salaries()
    {
        return $this->hasMany($this->getModelClass('user_salary'), 'sallary');
    }

    public function agencySalaries()
    {
        return $this->hasMany(AgencySalary::class, 'agency_id')->orderByDesc('id');
    }

    public function getTotalSallaryAgency($month = null, $year = null)
    {
        if ($month === null) {
            $month = now()->month;
        }

        if ($year === null) {
            $year = now()->year;
        }
        $agencySallary = AgencySalary::query()->where(function ($query) use ($year, $month) {
            $query->where(DB::raw('concat(year,"-", month)'), '<=', $year.'-'.$month);
        })->where('is_paid', 0)
            ->where('agency_id', $this->id)
            ->orderByDesc('id')
            ->sum(DB::raw('sallary'));

        return floor($agencySallary ?? 0);
    }

    public function getTotalCutAmountAgency($month = null, $year = null)
    {
        if ($month === null) {
            $month = now()->month;
        }

        if ($year === null) {
            $year = now()->year;
        }
        $agencySallary = AgencySalary::query()->where(function ($query) use ($year, $month) {
            $query->where(DB::raw('concat(year,"-", month)'), '<=', $year.'-'.$month);
        })->where('is_paid', 0)
            ->where('agency_id', $this->id)
            ->orderByDesc('id')
            ->sum(DB::raw('cut_amount'));

        return floor($agencySallary ?? 0);
    }

    public function getOldAgency($month = null, $year = null)
    {
        $currentYear = date('Y');
        $currentMonth = date('m');
        $old =
            AgencySalary::query()->when(isset($month), function ($query) use ($month) {
                $query->where('month', '<=', $month);
            })->when(isset($year), function ($query) use ($year) {
                $query->where('year', '<=', $year);
            })->where('agency_id', $this->id)->whereRaw("CONCAT(year, LPAD(month, 2, '0')) != CONCAT('$currentYear', LPAD('$currentMonth', 2, '0'))")->where('is_paid', 0)->sum(DB::raw('sallary - cut_amount'));

        return $old;
    }

    public function getSalaryAgency($month = null, $year = null)
    {
        if ($month === null) {
            $month = now()->month;
        }

        if ($year === null) {
            $year = now()->year;
        }
        $agencySallary = AgencySalary::query()->where(function ($query) use ($year, $month) {
            $query->where(DB::raw('concat(year,"-", month)'), '<=', $year.'-'.$month);
        })->where('is_paid', 0)
            ->where('agency_id', $this->id)
            ->orderByDesc('id')
            ->sum(DB::raw('sallary - cut_amount'));

        return round($agencySallary, 2);
    }

    public function getSalary($month = null, $year = null)
    {
        if ($month === null) {
            $month = now()->month;
        }

        if ($year === null) {
            $year = now()->year;
        }
        $agencySallary = AgencySalary::query()
            ->where(function ($query) use ($year) {
                $query->where('year', $year);
            })->where(function ($query) use ($month) {
                $query->where('month', $month);
            })->where('agency_id', $this->id)->sum(DB::raw('sallary - cut_amount'));

        return floor($agencySallary ?? 0);
    }

    public function joinRequests()
    {
        return $this->hasMany(AgencyJoinRequest::class, 'agency_id');
    }

    public function getIsFrozenAttribute($value)
    {
        return $value ?? 0;
    }

    public function coinLogs()
    {
        return $this->morphMany($this->getModelClass('coin_log'), 'owner', 'user_type', 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo($this->getModelClass('admin_user'), 'created_by');
    }

    protected static function boot()
    {
        parent::boot();
        self::addGlobalScope(new ShippingAgencyScope);

        self::saving(function ($model) {

            if (request()->has('charge_agency')) {
                $chargeAgencyClass = $model->getModuleClass('salary_transaction', 'charge_agency');
                if ($chargeAgencyClass) {
                    if (request('charge_agency') === 1) {
                        $chargeAgencyClass::firstOrCreate([
                            'agency_id' => $model->id,
                        ]);
                    } else {
                        $chargeAgencyClass::where('agency_id', $model->id)->delete();
                    }
                }
            }

            if (request()->has('appear_charger_agency')) {
                $user = $model->getModelClass('user')::find($model->app_owner_id);

                if ($user) {
                    if (request('appear_charger_agency') === 1) {
                        $user->update(['appear_charger_agency' => 1]);
                    } else {
                        $user->update(['appear_charger_agency' => 0]);
                    }
                }
            }
        });

        self::updating(function ($agency) {
            if (isset($agency->is_frozen)) {
                $agency->is_frozen = (bool) $agency->is_frozen;
            }
        });

        self::deleting(function ($agency) {
            // Update the related user model (change type to 0)
            if ($agency->app_owner_id) {
                $userModel = new static; // access instance to get trait method
                $userClass = $userModel->getModelClass('user');
                $user = $userClass::find($agency->app_owner_id);

                if ($user) {
                    $otherAgenciesCount = Agency::where('app_owner_id', $user->id)
                        ->count();
                    if ($otherAgenciesCount > 0) {
                        $user->type_user = 2;
                    } elseif ($user->agency_id) {

                        $user->type_user = 1;
                    } else {
                        $user->type_user = 0;
                    }
                    $user->save();

                    $users = $userClass::where('agency_id', $agency->id)->update([
                        'type_user' => 0,
                    ]);
                }
            }
        });
    }

    protected function userModel()
    {
        return config('agency-package.models.user');
    }

    protected function adminModel()
    {
        return config('agency-package.models.admin');
    }

    protected function countryModel()
    {
        return config('agency-package.models.country');
    }

    protected function chargeModel()
    {
        return config('agency-package.models.charge');
    }

    protected function salaryModel()
    {
        return config('agency-package.models.user_salary');
    }

    protected function userTargetModel()
    {
        return config('agency-package.models.user_target');
    }

    protected function agencyUserJobModel()
    {
        return AgencyUserJob::class;
    }
}
