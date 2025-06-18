<?php

namespace App\Models;

use App\Builders\VipCollectionBuilderService;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Vip extends Model
{
    use HasFactory, TimestampsWithTimezone;
    public static int $useCache = 1;

    protected $fillable = [
        'type',
        'img',
        'exp',
        'level',
        'di',
        'co',
        'name_en',
        'name_ar',
    ];

    public static function getCached(): Collection
    {
        return Cache::rememberForever('vips', fn () => self::all());
    }

    public static function vipCollectionBuilder(): VipCollectionBuilderService
    {
        return new VipCollectionBuilderService();
    }

    /*public function gifts()
    {
        return $this->hasMany(GiftRoomLevel::class,'level_id');
    }*/
}
