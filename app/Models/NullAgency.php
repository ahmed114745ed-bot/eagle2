<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Null Object Pattern for Agency relationship
 * Used when Agency package is not installed
 * Prevents column mismatch errors by providing a stub model
 */
class NullAgency extends Model
{
    protected $table = 'users'; // Use existing table to avoid table not found errors
    
    protected $guarded = [];
    
    protected $casts = [
        'id' => 'integer',
        'app_owner_id' => 'integer',
    ];
    
    // Default attributes to match Agency structure
    protected $attributes = [
        'id' => 0,
        'name' => '',
        'img' => '',
        'app_owner_id' => 0,
        'status' => 0,
    ];
    
    /**
     * Override getAttribute to return safe default values
     */
    public function getAttribute($key)
    {
        // Return safe defaults for common agency attributes
        $defaults = [
            'id' => 0,
            'name' => '',
            'img' => '',
            'notice' => '',
            'phone' => '',
            'url' => '',
            'app_owner_id' => 0,
            'status' => 0,
            'type' => 0,
        ];
        
        return $defaults[$key] ?? parent::getAttribute($key);
    }
    
    /**
     * Return null for any relationship
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'app_owner_id')->whereRaw('1 = 0');
    }
}
