<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;


trait DynamicRealsTrait
{

    public function reals(): HasMany
    {
        if ($this->hasRealsFeature()) {
            return $this->hasMany(
                \Utd\Reals\Entities\Real::class,
                'user_id'
            );
        }

        return $this->hasMany(static::class, 'id', 'id')
            ->whereRaw('1 = 0');
    }

    public function realLikes(): HasMany
    {
        if ($this->hasRealsFeature()) {
            return $this->hasMany(
                \Utd\Reals\Entities\RealUserLike::class,
                'user_id'
            );
        }

        return $this->hasMany(static::class, 'id', 'id')
            ->whereRaw('1 = 0');
    }

    public function realComments(): HasMany
    {
        if ($this->hasRealsFeature()) {
            return $this->hasMany(
                \Utd\Reals\Entities\RealUserComment::class,
                'user_id'
            );
        }

        return $this->hasMany(static::class, 'id', 'id')
            ->whereRaw('1 = 0');
    }

    /**
     * Snake case alias for realComments
     */
    public function real_comments(): HasMany
    {
        return $this->realComments();
    }

    /**
     * Snake case alias for realLikes
     */
    public function real_likes(): HasMany
    {
        return $this->realLikes();
    }

    public function reelSetting(): HasOne
    {
        if ($this->hasRealsFeature()) {
            return $this->hasOne(
                \Utd\Reals\Entities\ReelsUserSetting::class,
                'user_id'
            );
        }

        return $this->hasOne(static::class, 'id', 'id')
            ->whereRaw('1 = 0');
    }

    public function getDisplayReals(int $limit = 3): Collection
    {
        if (!$this->hasRealsFeature()) {
            return collect([]);
        }

        return $this->reals()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($real) => [
                'id' => $real->id,
                'thumbnail' => $real->thumbnail ?? null,
                'video_url' => $real->video_url ?? null,
                'likes_count' => $real->likes_count ?? 0,
            ]);
    }


    public function getRealsCountAttribute(): int
    {
        if (!$this->hasRealsFeature()) {
            return 0;
        }

        return $this->reals()->count();
    }


    protected function getRealSetting()
    {
        if (!$this->hasRealsFeature()) {
            return new class {
                public $all_unique_value = 0;
                public $following_unique_value = 0;
                public function update(array $data) { }
            };
        }

        $setting = $this->reelSetting;
        
        if (!$setting) {
            $settingClass = \Utd\Reals\Entities\ReelsUserSetting::class;
            $setting = $settingClass::create([
                'user_id' => $this->id,
                'all_unique_value' => random_int(1, 10000),
                'following_unique_value' => random_int(1, 10000),
            ]);
            $this->setRelation('reelSetting', $setting);
        }

        return $setting;
    }

    public function getRealTypeAttribute(): int
    {
        return $this->getRealSetting()->all_unique_value ?? 0;
    }


    public function setRealTypeAttribute($value): void
    {
        if (!$this->hasRealsFeature()) {
            return;
        }

        $realSetting = $this->getRealSetting();
        $realSetting->update(['all_unique_value' => $value]);
        $this->setRelation('reelSetting', $realSetting);
    }

    public function getFollowingUniqueValueAttribute(): int
    {
        return $this->getRealSetting()->following_unique_value ?? 0;
    }

    public function setFollowingUniqueValueAttribute($value): void
    {
        if (!$this->hasRealsFeature()) {
            return;
        }

        $realSetting = $this->getRealSetting();
        $realSetting->update(['following_unique_value' => $value]);
        $this->setRelation('reelSetting', $realSetting);
    }

 
    public function hasRealsFeature(): bool
    {
        return class_exists(\Utd\Reals\Entities\Real::class);
    }
}
