<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends \App\Models\Administrator
{
    protected $table = 'admin_users';
    protected $appends = ['agency_id'];
    protected $fillable = ['username', 'password', 'name', 'avatar', 'is_preview','app_manager_id'];

    protected $guarded = [];
    public function agency(){
        return $this->hasOne (Agency::class,'owner_id');
    }


    public function getAgencyIdAttribute(){
        return @$this->agency->id;
    }

    public function getImgAttribute(){
        return $this->attributes['avatar'];
    }

    public function agencies() {
        return $this->hasMany(Agency::class, 'agency_manger_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Listen for the 'deleting' event of the admin model
        static::deleting(function ($admin) {
             $appId =  $admin->app_id;
             $agencies = Agency::where('agency_manger_id',$appId)->get();
             $config = Config::where('name','system_default_manger')->first();

             $user = User::where('uuid',$config->value)->first();
             $agenciesId = [];
             foreach($agencies as $agency){
                $agenciesId[] = $agency->id;
                $agency->agency_manger_id = $user->id;
                $agency->save();
             }
             $agencyIds = implode(",", $agenciesId);
             AgencyMangerDeleted::create([
               'admin_id' =>Auth::id(),
               'agency_manger_id' => $appId,
               'agencies_id' => $agencyIds,
             ]);
        });
    }


    public function per() {
        return $this->hasMany(Agency::class, 'agency_manger_id');
    }



}
