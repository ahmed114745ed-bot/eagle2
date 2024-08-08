<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Ware extends Model
{
    protected $table = 'wares';
    protected $guarded=['id'];
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if ($model->status) {
                unset($model->status);
            }
        });
        // Listen for the 'deleting' event of the Agency model
        static::deleting(function ($id) {

            $pack = Pack::where('target_id', $id->id)->delete();
        });
    }

    public function packs()
    {
        return $this->hasMany(Pack::class, 'target_id');
    }

    public function scopeIsNotUsedInPacks(Builder $query)
    {
        return $query->whereDoesntHave("packs", function ($q) {
            $q->where(fn($q)=>$q->where('packs.expire', 0)->orWhere('packs.expire', '>=', time()));
        });
    }

    public function scopeShowUserCustom(Builder $query, int $userId)
    {
        return $query->where(fn($q) =>
            $q->whereDoesntHave('ware_users') 
            ->orWhereHas('ware_users', fn($q) => $q->where('user_id', $userId))
        );
    }
    

    public function ware_users()
    {
        return $this->belongsToMany(User::class, 'user_ware','ware_id','user_id')->withPivot('disable');
    }


}
