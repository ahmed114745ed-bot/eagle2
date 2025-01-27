<?php

namespace App\Models;

use App\Helpers\Common;
use App\Traits\PaymentGetWayTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AgencyApp\Entities\AdditionalInfo;
use Modules\AgencyApp\Traits\AgencyAdditionalInfoTraits;
use Modules\SalaryTransaction\Entities\ChargeAgency;
use Modules\SalaryTransaction\Entities\SalaryRequest;
use Modules\SalaryTransaction\Http\Controllers\ChargeAgencyController;
use Modules\SalaryTransaction\Traits\SalaryTransferTrait;

class Agency extends Model
{
    use SoftDeletes,AgencyAdditionalInfoTraits, PaymentGetWayTrait,SalaryTransferTrait;
    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    public function getCreatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }
    Public function chargeAgency()
    {
        return $this->hasMany(ChargeAgency::class ,'agency_id');
    }

    public function Countries()
    {
        return $this->belongsToMany(Country::class, 'agency_countries', 'agency_id', 'country_id')->withTimestamps();
    }

    public function salaryRequests()
    {
        return $this->hasMany(SalaryRequest::class, 'agency_id');
    }

    public function mempers()
    {
        return $this->hasMany(User::class, 'agency_id');
    }
    public function users(){
        return $this->hasMany (User::class);
    }

    public function scopeOfOwner($query, $owner_id)
    {
        return $query->where('owner_id', $owner_id);
    }

    public function owner(){
        return $this->belongsTo (User::class,'app_owner_id','id');
    }

    public function agencyManger()
    {
        return $this->belongsTo (User::class,'agency_manger_id','id');
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }


    public function dashOwner(){
        return $this->belongsTo (Admin::class,'owner_id','id');
    }

    public function getUrlAttribute($val){
        if (!$val) {
            return "";
        }
        return $val;
    }
    public function getContentsAttribute($val){
        if (!$val) {
            return "";
        }
        return $val;
    }

    public function target($month = null,$year = null){
        if (!$month){
            $month = date ('m');
        }
        if (!$year){
            $year = date ('Y');
        }
        return $this->hasMany (AgencySallary::class)->where ('month',$month)->where ('year',$year)->first ();
    }
    public function getTargetAttribute($month = null, $year = null)
    {
        if (!$month) {
            $month = date('m');
        }
        if (!$year) {
            $year = date('Y');
        }

        return $this->hasMany(AgencySallary::class)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }

    public function getSalaryAttribute(){
        $salary = AgencySallary::query ()->where ('agency_id',$this->id)->where ('is_paid',0)->sum (\DB::raw('sallary - cut_amount'));
        return $salary;
    }

    public function setSalaryAttribute(){
        $salary = AgencySallary::query ()->where ('agency_id',$this->id)->where ('is_paid',0)->sum (\DB::raw('sallary - cut_amount'));
        $this->attributes['salary'] = $salary;
        return $salary;
    }

    public function getSalaryAttributeAgencyManger(){
        $salaryAgency = AgencySallary::query ()->where ('agency_id',$this->id)->where ('is_paid',0)->sum (\DB::raw('sallary - cut_amount'));
        $attributes['salaryAgency'] = $salaryAgency;
        return $attributes;
    }

    protected static function boot()
    {
        parent::boot();

        // Listen for the 'deleting' event of the Agency model
        static::deleting(function ($agency) {
            // Update the related user model (change type to 0)
            if ($agency->app_owner_id) {
                $user = User::find($agency->app_owner_id);
                if ($user) {
                    $user->type_user = 0;
                    $user->save();
                    $users = User::where('agency_id', $agency->id)->update([
                        'type_user'=> 0
                    ]);

                }



            }
        });
    }




    public function AgencyUsersTargets()
    {
        return $this->hasMany(UserTarget::class, 'agency_id');
    }


    public function UserTarget()
    {
        return $this->hasMany(UserTarget::class,'agency_id');
    }

    public function agencySalary()
    {
        return $this->hasOne(AgencySallary::class, 'agency_id')->orderByDesc('id')
            ->where('month', now()->month)->where('year', now()->year);
    }

    public function getLastMonthSalaryAttribute()
    {
        return $this->hasOne(AgencySallary::class, 'agency_id')->orderByDesc('id')
            ->where('month', now()->subMonth()->month)->where('year', now()->subMonth()->year)->where ('is_paid',0)->sum (\DB::raw('sallary - cut_amount'));
    }

    public function salaries()
    {
        return $this->hasMany(UserSallary::class, 'sallary');
    }

    public function agencySalaries()
    {
        return $this->hasMany(AgencySallary::class, 'agency_id')->orderByDesc('id');
    }

    public function getTotalSallaryAgency($period)
    {
            $agencySallary = AgencySallary::query()->when(isset($period), function ($query) use ($period) {
                //$query->where(DB::raw('concat(year,"-", month)'), '<=', $year . '-' . $month);
                $query->where('period_id', $period);
            })->where('is_paid', 0)
                ->where('agency_id', $this->id)
                ->orderByDesc('id')
                ->sum(DB::raw('sallary'));


            return floor($agencySallary ?? 0);
    }

    public function getTotalCutAmountAgency($period)
    {
            $agencySallary = AgencySallary::query()->when(isset($period), function ($query) use ($period) {
                $query->where('period_id', $period);
            }) ->where('is_paid', 0)
                ->where('agency_id', $this->id)
                ->orderByDesc('id')
                ->sum(DB::raw('cut_amount'));
            return floor($agencySallary ?? 0);

    }

    public function getOldAgency($month = null, $year = null)
    {
        $currentYear  = date('Y');
        $currentMonth = date('m');
        $old          =
            AgencySallary::query()->when(isset($month), function ($query) use ($month) {
                $query->where('month', '<=', $month);
            })->when(isset($year), function ($query) use ($year) {
                $query->where('year', '<=', $year);
            })->where('agency_id', $this->id)->whereRaw("CONCAT(year, LPAD(month, 2, '0')) != CONCAT('$currentYear', LPAD('$currentMonth', 2, '0'))")->where('is_paid', 0)->sum(DB::raw('sallary - cut_amount'));
        return $old;
    }

    public function getSalaryAgency($period)
    {
        
            $agencySallary = AgencySallary::query()->when(isset($period), function ($query) use ($period) {
                $query->where('period_id', $period);
            })->where('is_paid', 0)
                ->where('agency_id', $this->id)
                ->orderByDesc('id')
                ->sum(DB::raw('sallary - cut_amount'));


            return floor($agencySallary ?? 0);
    }

    public function getSalary($month = null, $year = null, )
    {
        if ($month == null) {
            $month = now()->month;
        }

        if ($year == null) {
            $year = now()->year;
        }
            $agencySallary = AgencySallary::query()
            ->where( function ($query) use ($year) {
                $query->where('year', $year);
            })->where( function ($query) use ( $month) {
                $query->where('month',$month);
            })->where('agency_id', $this->id)->sum(DB::raw('sallary - cut_amount'));
            return floor( $agencySallary ?? 0);
    }

    public function joinRequests()
    {
        return $this->hasMany(AgencyJoinRequest::class, 'agency_id');
    }




}
