<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Utd\Agency\Scopes\HostAgencyScope;
use Utd\Agency\Traits\ConfigurableModelsTrait;
use Utd\Agency\Traits\CreatedByTrait;
use Utd\Agency\Traits\DefaultBdAssignmentTrait;
use Utd\Agency\Traits\PaymentGetWayTrait;
use App\Traits\TimestampsWithTimezone;

class Agency extends Model
{
    use ConfigurableModelsTrait, CreatedByTrait, DefaultBdAssignmentTrait, PaymentGetWayTrait, SoftDeletes, TimestampsWithTimezone;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'salary',
    ];

    // Dynamically use SalaryTransferTrait if available
    public function __construct(array $attributes = [])
    {
        if (trait_exists('Modules\\SalaryTransaction\\Traits\\SalaryTransferTrait')) {
            $this->initializeSalaryTransferTrait();
        }
        parent::__construct($attributes);
    }

    /**
     * Additional Info relationship (from AgencyAdditionalInfoTrait)
     */
    public function additionalInfo()
    {
        if (class_exists('Utd\\Agency\\Entities\\AdditionalInfo')) {
            $additionalInfoClass = AdditionalInfo::class;

            return $this->hasOne($additionalInfoClass, 'agency_id');
        }

        return $this->hasOne(self::class, 'agency_id')->whereRaw('1 = 0');
    }

    public function chargeAgency()
    {
        if (! class_exists('Modules\\SalaryTransaction\\Entities\\ChargeAgency')) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        $chargeAgencyClass = 'Modules\\SalaryTransaction\\Entities\\ChargeAgency';

        return $this->hasMany($chargeAgencyClass, 'agency_id');
    }

    public function salaryRequests()
    {
        if (! class_exists('Modules\\SalaryTransaction\\Entities\\SalaryRequest')) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        $salaryRequestClass = 'Modules\\SalaryTransaction\\Entities\\SalaryRequest';

        return $this->hasMany($salaryRequestClass, 'agency_id');
    }

    public function charges()
    {
        $chargeClass = config('agency-package.models.charge', \App\Models\Charge::class);

        return $this->hasMany($chargeClass, 'user_id', 'id')->where('charger_type', 'host_agency');
    }

    public function senderCharges()
    {
        $chargeClass = config('agency-package.models.charge', \App\Models\Charge::class);

        return $this->hasMany($chargeClass, 'charger_id', 'id')->where('charger_type', 'host_agency');
    }

    public function Countries()
    {
        $countryClass = config('agency-package.models.country', \App\Models\Country::class);

        return $this->belongsToMany($countryClass, 'agency_countries', 'agency_id', 'country_id')->withTimestamps();
    }

    public function country(): BelongsTo
    {
        $countryClass = config('agency-package.models.country', \App\Models\Country::class);

        return $this->belongsTo($countryClass);
    }

    public function mempers()
    {
        $userClass = config('agency-package.models.user', \App\Models\User::class);

        return $this->hasMany($userClass, 'agency_id');
    }

    public function members()
    {
        $userClass = config('agency-package.models.user', \App\Models\User::class);

        return $this->hasMany($userClass, 'agency_id');
    }

    public function users()
    {
        $userClass = config('agency-package.models.user', \App\Models\User::class);

        return $this->hasMany($userClass, 'agency_id');
    }

    public function admins()
    {
        return $this->hasMany(AgencyUserJob::class, 'agency_id')->where('type', 'requestManger');
    }

    public function scopeOfOwner($query, $owner_id)
    {
        return $query->where('owner_id', $owner_id);
    }

    public function owner()
    {
        $userClass = config('agency-package.models.user', \App\Models\User::class);

        return $this->belongsTo($userClass, 'app_owner_id', 'id')
            ->withoutGlobalScopes();
    }

    public function agencyManger()
    {
        $userClass = config('agency-package.models.user', \App\Models\User::class);

        return $this->belongsTo($userClass, 'agency_manger_id', 'id');
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function dashOwner()
    {
        $adminClass = config('agency-package.models.admin', \App\Models\Admin::class);

        return $this->belongsTo($adminClass, 'owner_id', 'id');
    }

    public function getUrlAttribute($val)
    {
        if (! $val) {
            return '';
        }

        return $val;
    }

    public function getContentsAttribute($val)
    {
        // Map 'contents' attribute to 'notice' column for backward compatibility
        return $this->attributes['notice'] ?? '';
    }

    public function target($month = null, $year = null)
    {
        if (! $month) {
            $month = date('m');
        }
        if (! $year) {
            $year = date('Y');
        }

        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);

        return $this->hasMany($agencySalaryClass)->where('month', $month)->where('year', $year)->first();
    }

    public function getTargetAttribute($month = null, $year = null)
    {
        if (! $month) {
            $month = date('m');
        }
        if (! $year) {
            $year = date('Y');
        }

        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);

        return $this->hasMany($agencySalaryClass)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }

    public function getTargetsAttribute($month = null, $year = null)
    {
        if (! $month) {
            $month = date('m');
        }
        if (! $year) {
            $year = date('Y');
        }

        $userTargetClass = config('agency-package.models.user_target', \App\Models\UserTarget::class);

        return $this->hasMany($userTargetClass)
            ->where('add_month', $month)
            ->where('add_year', $year)
            ->first();
    }

    public function getSalaryAttribute()
    {
        return $this->agencySalaries->sum(fn ($row) => ($row->sallary - $row->cut_amount));

        //        $salary = AgencySallary::query()->where('agency_id', $this->id)
        //            ->sum(DB::raw('sallary - cut_amount'));
        //
        //        return $salary;
    }

    public function setSalaryAttribute()
    {
        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);
        $salary = $agencySalaryClass::query()->where('agency_id', $this->id)->where('is_paid', 0)->sum(DB::raw('sallary - cut_amount'));
        $this->attributes['salary'] = $salary;

        return $salary;
    }

    public function getSalaryAttributeAgencyManger()
    {
        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);
        $salaryAgency = $agencySalaryClass::query()->where('agency_id', $this->id)->where('is_paid', 0)->sum(DB::raw('sallary - cut_amount'));
        $attributes['salaryAgency'] = $salaryAgency;

        return $attributes;
    }

    public function AgencyUsersTargets()
    {
        $userTargetClass = config('agency-package.models.user_target', \App\Models\UserTarget::class);

        return $this->hasMany($userTargetClass, 'agency_id');
    }

    public function UserTarget()
    {
        $userTargetClass = config('agency-package.models.user_target', \App\Models\UserTarget::class);

        return $this->hasMany($userTargetClass, 'agency_id');
    }

    public function agencySalary()
    {
        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);

        return $this->hasOne($agencySalaryClass, 'agency_id')
            ->orderByDesc('id')
            ->where('month', now()->month)
            ->where('year', now()->year);
    }

    public function getLastMonthSalaryAttribute()
    {
        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);

        return $this->hasOne($agencySalaryClass, 'agency_id')
            ->orderByDesc('id')
            ->where('month', now()->subMonth()->month)
            ->where('year', now()->subMonth()->year)
            ->where('is_paid', 0)
            ->sum(DB::raw('sallary - cut_amount'));
    }

    public function salaries()
    {
        $userSalaryClass = config('agency-package.models.user_salary', \App\Models\UserSallary::class);

        return $this->hasMany($userSalaryClass, 'sallary');
    }

    public function userSalaries()
    {
        $userSalaryClass = config('agency-package.models.user_salary', \App\Models\UserSallary::class);

        return $this->hasMany($userSalaryClass, 'user_agency_id');
    }

    public function agencySalaries()
    {
        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);

        return $this->hasMany($agencySalaryClass, 'agency_id')->orderByDesc('id');
    }

    public function getTotalSallaryAgency($month = null, $year = null)
    {
        $month ??= now()->month;
        $year ??= now()->year;

        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);
        $agencySalary = $agencySalaryClass::query()
            ->where(DB::raw('concat(year,"-", month)'), '<=', $year.'-'.$month)
            ->where('is_paid', 0)
            ->where('agency_id', $this->id)
            ->orderByDesc('id')
            ->sum(DB::raw('sallary'));

        return floor($agencySalary ?? 0);
    }

    public function getTotalTargetAgency($month = null, $year = null)
    {
        $month ??= now()->month;
        $year ??= now()->year;
        $sumTargets =
            UserSallary::where('user_agency_id', $this->id)
                ->where('month', $month)
                ->where('year', $year)
                ->sum('target_diamonds');

        return floor($sumTargets ?? 0);
    }

    public function getTotalCutAmountAgency($month = null, $year = null)
    {
        $month ??= now()->month;
        $year ??= now()->year;

        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);
        $agencySalary = $agencySalaryClass::query()
            ->where(DB::raw('concat(year,"-", month)'), '<=', $year.'-'.$month)
            ->where('is_paid', 0)
            ->where('agency_id', $this->id)
            ->orderByDesc('id')
            ->sum(DB::raw('cut_amount'));

        return round($agencySalary, 2);
    }

    public function getTotalNetSallaryAgency($month = null, $year = null)
    {
        $month ??= now()->month;
        $year ??= now()->year;
        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);
        $result = $agencySalaryClass::query()
            ->where(DB::raw('concat(year,"-", month)'), '<=', $year.'-'.$month)
            ->where('is_paid', 0)
            ->where('agency_id', $this->id)
            ->selectRaw('SUM(sallary) - SUM(cut_amount) as total')
            ->value('total');

        return round($result, 2);
    }

    public function getOldAgency($month = null, $year = null)
    {
        $currentYear = date('Y');
        $currentMonth = date('m');

        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);

        return $agencySalaryClass::query()
            ->where('month', '<=', $month)
            ->where('year', '<=', $year)
            ->where('agency_id', $this->id)
            ->whereRaw("CONCAT(year, LPAD(month, 2, '0')) != CONCAT('$currentYear', LPAD('$currentMonth', 2, '0'))")
            ->where('is_paid', 0)
            ->sum(DB::raw('sallary - cut_amount'));
    }

    public function getSalaryAgency($month = null, $year = null)
    {
        $month ??= now()->month;
        $year ??= now()->year;

        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);
        $agencySalary = $agencySalaryClass::query()
            ->where(DB::raw('concat(year,"-", month)'), '<=', $year.'-'.$month)
            ->where('is_paid', 0)
            ->where('agency_id', $this->id)
            ->orderByDesc('id')
            ->sum(DB::raw('sallary - cut_amount'));

        return round($agencySalary, 2);
    }

    public function getSalaryWithOutCutAmountAgency($month = null, $year = null)
    {
        $month ??= now()->month;
        $year ??= now()->year;

        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);
        $agencySalary = $agencySalaryClass::query()
            ->where(DB::raw('concat(year,"-", month)'), '<=', $year.'-'.$month)
            ->where('is_paid', 0)
            ->where('agency_id', $this->id)
            ->orderByDesc('id')
            ->sum(DB::raw('sallary'));

        return round($agencySalary, 2);
    }

    public function getSalary($month = null, $year = null)
    {
        $month ??= now()->month;
        $year ??= now()->year;

        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);
        $agencySalary = $agencySalaryClass::query()
            ->where('year', $year)
            ->where('month', $month)
            ->where('agency_id', $this->id)
            ->sum(DB::raw('sallary - cut_amount'));

        return floor($agencySalary ?? 0);
    }

    public function sumNetSalary($month = null, $year = null)
    {
        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);
        $agencySalary = $agencySalaryClass::query()
            ->when(isset($month) && isset($year), function ($query) use ($year, $month) {
                $query->where(function ($query) use ($year, $month) {
                    $query->where(DB::raw('concat(year,"-", month)'), '=', $year.'-'.$month);
                });
            })
            ->where('agency_id', $this->id)
            ->sum(DB::raw('sallary - cut_amount'));

        return truncateAndTrim($agencySalary ?? 0);
    }

    public function sumCutAmount($month = null, $year = null)
    {
        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);
        $agencySalary = $agencySalaryClass::query()
            ->when(isset($month) && isset($year), function ($query) use ($year, $month) {
                $query->where(function ($query) use ($year, $month) {
                    $query->where(DB::raw('concat(year,"-", month)'), '=', $year.'-'.$month);
                });
            })
            ->where('agency_id', $this->id)
            ->sum(DB::raw('cut_amount'));

        return floor($agencySalary ?? 0);
    }

    public function sumSalary($month = null, $year = null)
    {
        $agencySalaryClass = config('agency-package.models.agency_salary', AgencySalary::class);
        $agencySalary = $agencySalaryClass::query()
            ->when(isset($month) && isset($year), function ($query) use ($year, $month) {
                $query->where(function ($query) use ($year, $month) {
                    $query->where(DB::raw('concat(year,"-", month)'), '=', $year.'-'.$month);
                });
            })
            ->where('agency_id', $this->id)
            ->sum(DB::raw('sallary'));

        return truncateAndTrim($agencySalary ?? 0);
    }

    public function joinRequests()
    {
        return $this->hasMany(AgencyJoinRequest::class, 'agency_id');
    }

    public function getIsFrozenAttribute($value)
    {
        return $value ?? 0;
    }

    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereDoesntHave('additionalInfo')
                ->orWhereHas('additionalInfo', fn ($q) => $q->where('status', 1));
        })->whereNull('deleted_at')->where('type', 1);
    }

    public function bd()
    {
        $bdClass = config('agency-package.models.bd', \Utd\Bd\Entities\Bd::class);

        return $this->belongsTo($bdClass, 'bd_id');
    }

    public function creator()
    {
        $adminUserClass = config('agency-package.models.admin_user', \App\Models\AdminUser::class);

        return $this->belongsTo($adminUserClass, 'created_by');
    }

    public function ownerUserId(): ?int
    {
        return $this->app_owner_id ?? null;
    }

    public function bdUserId(): ?int
    {
        return $this->bd_id ?? null;
    }

    protected static function boot()
    {
        parent::boot();

        self::addGlobalScope(new HostAgencyScope);

        self::saving(function ($model) {
            $model->type = 1;

            if (request()->has('phone_code')) {
                $model->phone_code = request('phone_code');
            }
            if (request()->has('charge_agency')) {
                if (request('charge_agency') === 1) {
                    ChargeAgency::firstOrCreate([
                        'agency_id' => $model->id,
                    ]);
                } else {
                    ChargeAgency::where('agency_id', $model->id)->delete();
                }
            }

            if (request()->has('appear_charger_agency')) {
                $userClass = config('agency-package.models.user', \App\Models\User::class);
                $user = $userClass::find($model->app_owner_id);

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

            clearAgencyCache($agency->id);

            if (isset($agency->is_frozen)) {
                $agency->is_frozen = (bool) $agency->is_frozen;
            }
        });

        self::deleting(function ($agency) {

            if ($agency->app_owner_id) {
                $userClass = config('agency-package.models.user', \App\Models\User::class);
                $user = $userClass::find($agency->app_owner_id);
                if ($user) {
                    $user->type_user = 0;
                    $user->save();
                    $users = $userClass::where('agency_id', $agency->id)->update([
                        'type_user' => 0,
                    ]);
                }
            }
            clearAgencyCache($agency->id);
        });
    }

    protected function initializeSalaryTransferTrait()
    {
        if (method_exists($this, 'bootSalaryTransferTrait')) {
            $this->bootSalaryTransferTrait();
        }
    }
}
