<?php

namespace App\Traits;


trait DynamicGiftsTrait
{
    /**
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function userGiftsPackage()
    {
        if (!class_exists('Utd\Gifts\Entities\Gift')) {
            return $this->belongsToMany(self::class, 'user_gifts')->whereRaw('1 = 0');
        }

        return $this->belongsToMany(
            'Utd\Gifts\Entities\Gift',
            'user_gifts',
            'user_id',
            'gift_id'
        )->withPivot('quantity', 'expire')->withTimestamps();
    }

    /**
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sentGiftsPackage()
    {
        if (!class_exists('Utd\Gifts\Entities\GiftLog')) {
            return $this->hasMany(self::class, 'id')->whereRaw('1 = 0');
        }

        return $this->hasMany('Utd\Gifts\Entities\GiftLog', 'sender_id');
    }

    /**
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receivedGiftsPackage()
    {
        if (!class_exists('Utd\Gifts\Entities\GiftLog')) {
            return $this->hasMany(self::class, 'id')->whereRaw('1 = 0');
        }

        return $this->hasMany('Utd\Gifts\Entities\GiftLog', 'receiver_id');
    }

    /**
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function luckyGiftsPackage()
    {
        if (!class_exists('Utd\Gifts\Entities\UserLuckyGift')) {
            return $this->hasMany(self::class, 'id')->whereRaw('1 = 0');
        }

        return $this->hasMany('Utd\Gifts\Entities\UserLuckyGift', 'user_id');
    }

    /**
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function giftRankingsPackage()
    {
        if (!class_exists('Utd\Gifts\Entities\GiftRanking')) {
            return $this->morphMany(self::class, 'ranker')->whereRaw('1 = 0');
        }

        return $this->morphMany('Utd\Gifts\Entities\GiftRanking', 'ranker');
    }
}
