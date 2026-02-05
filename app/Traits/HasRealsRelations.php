<?php

namespace App\Traits;

/**
 * HasRealsRelations Trait
 * 
 * Provides Reals package relations to User model
 * Relations are only available when Reals package is installed
 */
trait HasRealsRelations
{
    /**
     * Get user's reals (videos)
     */
    public function reals()
    {
        if (class_exists(\Utd\Reals\Entities\Real::class)) {
            return $this->hasMany(\Utd\Reals\Entities\Real::class, 'user_id');
        }
        
        // Return empty relation if package not installed
        return $this->hasMany(\App\Models\NullModel::class);
    }

    /**
     * Get comments made by user on reals
     */
    public function real_comments()
    {
        if (class_exists(\Utd\Reals\Entities\RealUserComment::class)) {
            return $this->hasMany(\Utd\Reals\Entities\RealUserComment::class, 'user_id');
        }
        
        return $this->hasMany(\App\Models\NullModel::class);
    }

    /**
     * Get likes made by user on reals
     */
    public function real_likes()
    {
        if (class_exists(\Utd\Reals\Entities\RealUserLike::class)) {
            return $this->hasMany(\Utd\Reals\Entities\RealUserLike::class, 'user_id');
        }
        
        return $this->hasMany(\App\Models\NullModel::class);
    }
}
