<?php

namespace App\Models;

use App\Builders\VipCollectionBuilderService;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDeleteIfCreatedByDeveloper;


class Vip extends Model
{
    use HasFactory ,PreventDeleteIfCreatedByDeveloper;
        protected $fillable = [
            'type',
            'img',
            'exp',
            'level',
            'di',
            'co',
            'name_en',
            'name_ar',
            'created_by',
            'updated_by'
        ];

    protected static function boot()
    {
        parent::boot();
        static::preventDeleteByDeveloper();

        // static::deleting(function ($vip) {
        //     if (auth()->user() && $vip->creator?->isRole('developer')) {
        //         abort(403);
        //     }
        // });
    }

    public function creator(){

        return $this->belongsTo(Admin::class, 'created_by');

    }
    public function getCreatedAtAttribute($value)
    {
        $cacheKey = 'timezone';
    }
    /*public function gifts()
    {
        return $this->hasMany(GiftRoomLevel::class,'level_id');
    }*/

    public static function getCached(): Collection
    {
        return Cache::rememberForever('vips', fn () => self::all());
    }

    public static function collectionBuilder(): VipCollectionBuilderService
    {
        return new VipCollectionBuilderService();
    }
}
