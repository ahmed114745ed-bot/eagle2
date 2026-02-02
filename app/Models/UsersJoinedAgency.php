<?php

namespace App\Models;

/**
 * This is just an alias for backward compatibility.
 * The actual model is in Utd\Agency\Entities\UsersJoinedAgency
 */
if (class_exists('\Utd\Agency\Entities\UsersJoinedAgency')) {
    class_alias(
        \Utd\Agency\Entities\UsersJoinedAgency::class,
        __NAMESPACE__ . '\UsersJoinedAgency'
    );
} else {
    // Fallback: Create empty class to prevent errors
    class UsersJoinedAgency extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'users_joined_agencies';
        protected $guarded = [];
    }
}
