<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PreviewAdmin extends Administrator
{
    protected $appends = ['agency_id'];

    public function agency()
    {
        $agencyClass = config('agency-package.models.agency', \App\Models\Agency::class);
        if (!class_exists($agencyClass)) {
            return $this->hasOne(self::class, 'owner_id')->whereRaw('1 = 0');
        }
        return $this->hasOne($agencyClass, 'owner_id');
    }

    public function getAgencyIdAttribute()
    {
        return @$this->agency->id;
    }

    public function getImgAttribute()
    {
        return $this->attributes['avatar'];
    }

    public function agencies()
    {
        $agencyClass = config('agency-package.models.agency', \App\Models\Agency::class);
        if (!class_exists($agencyClass)) {
            return $this->hasMany(self::class, 'agency_manger_id')->whereRaw('1 = 0');
        }
        return $this->hasMany($agencyClass, 'agency_manger_id');
    }

    public function per()
    {
        $agencyClass = config('agency-package.models.agency', \App\Models\Agency::class);
        if (!class_exists($agencyClass)) {
            return $this->hasMany(self::class, 'agency_manger_id')->whereRaw('1 = 0');
        }
        return $this->hasMany($agencyClass, 'agency_manger_id');
    }

    protected static function boot()
    {
        parent::boot();

        self::addGlobalScope('preview', function (Builder $builder) {
            $builder->where('status', 'active');
        });

        // Listen for the 'deleting' event of the admin model
        self::deleting(function ($admin) {
            $agencyClass = config('agency-package.models.agency', \App\Models\Agency::class);
            if (!class_exists($agencyClass)) {
                return;
            }
            $appId = $admin->app_id;
            $agencies = $agencyClass::where('agency_manger_id', $appId)->get();
            $config = Config::where('name', 'system_default_manger')->first();

            $user = User::where('uuid', $config->value)->first();
            $agenciesId = [];
            foreach ($agencies as $agency) {
                $agenciesId[] = $agency->id;
                $agency->agency_manger_id = $user->id;
                $agency->save();
            }
            $agencyIds = implode(',', $agenciesId);
            AgencyMangerDeleted::create([
                'admin_id' => Auth::id(),
                'agency_manger_id' => $appId,
                'agencies_id' => $agencyIds,
            ]);
        });
    }
}
