<?php

namespace App\Models;

use App\Helpers\AgencyPackageHelper;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Utd\AreaManager\Entities\Region;

class Admin extends Administrator
{
    protected $table = 'admin_users';

    protected $fillable = ['username', 'password', 'name','app_id', 'avatar', 'is_preview','type'];

    protected $guarded = [];

    /**
     * The accessors to append to the model's array form.
     * Only add agency_id if agency package is installed
     */
    protected function getArrayableAppends()
    {
        $appends = parent::getArrayableAppends();
        
        if (AgencyPackageHelper::isAgencyInstalled() && Schema::hasTable('agencies')) {
            $appends[] = 'agency_id';
        }
        
        return $appends;
    }

    public function agency()
    {
        if (!AgencyPackageHelper::isAgencyInstalled() || !Schema::hasTable('agencies')) {
            // Return empty relationship instead of null
            return $this->hasOne(Agency::class, 'owner_id')->whereRaw('1 = 0');
        }
        return $this->hasOne(Agency::class, 'owner_id');
    }
    public function user()
    {
         return $this->belongsTo(User::class, 'app_id');
    }

    public function getAgencyIdAttribute()
    {
        if (!AgencyPackageHelper::isAgencyInstalled() || !Schema::hasTable('agencies')) {
            return null;
        }
        
        return \Illuminate\Support\Facades\Cache::remember(
            "admin_agency_id_{$this->id}",
            300,
            fn() => @$this->agency?->id
        );
    }

    public function getImgAttribute()
    {
        return $this->attributes['avatar'];
    }

    public function getImageAttribute()
    {

        return getImagePath($this->attributes['avatar']);
    }

    public function agencies()
    {
        if (!AgencyPackageHelper::isAgencyInstalled() || !Schema::hasTable('agencies')) {
            return $this->hasMany(Agency::class, 'agency_manger_id')->whereRaw('1 = 0');
        }
        return $this->hasMany(Agency::class, 'agency_manger_id');
    }

    public function per()
    {
        if (!AgencyPackageHelper::isAgencyInstalled() || !Schema::hasTable('agencies')) {
            return $this->hasMany(Agency::class, 'agency_manger_id')->whereRaw('1 = 0');
        }
        return $this->hasMany(Agency::class, 'agency_manger_id');
    }

    public function countriesQuery()
    {
        return Country::whereHas('regions', function ($q) {
            $q->where('manager_id', $this->id);
        });
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class, 'manager_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Listen for the 'deleting' event of the admin model
        self::deleting(function ($admin) {
            if (!AgencyPackageHelper::isAgencyInstalled() || !Schema::hasTable('agencies')) {
                return;
            }
            
            $appId = $admin->app_id;
            $agencies = Agency::where('agency_manger_id', $appId)->get();
            $config = Config::where('name', 'system_default_manger')->first();

            $user = User::where('uuid', $config?->value)->first();
            $agenciesId = [];
            foreach ($agencies as $agency) {
                $agenciesId[] = $agency->id;
                $agency->agency_manger_id = $user?->id;
                $agency->save();
            }
            $agencyIds = implode(',', $agenciesId);
            
            if (class_exists(\App\Models\AgencyMangerDeleted::class)) {
                AgencyMangerDeleted::create([
                    'admin_id' => Auth::id(),
                    'agency_manger_id' => $appId,
                    'agencies_id' => $agencyIds,
                ]);
            }
        });
    }
}
