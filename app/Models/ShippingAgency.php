<?php

namespace App\Models;

/**
 * This is just an alias for backward compatibility.
 * The actual model is in Utd\Agency\Entities\ShippingAgency
 */
if (class_exists('\Utd\Agency\Entities\ShippingAgency')) {
    class_alias(
        \Utd\Agency\Entities\ShippingAgency::class,
        __NAMESPACE__ . '\ShippingAgency'
    );
} else {
    // Fallback: Create empty class to prevent errors
    class ShippingAgency extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'shipping_agencies';
        protected $guarded = [];
    }
}
