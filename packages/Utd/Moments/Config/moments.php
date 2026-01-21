<?php

return [
    'name' => 'Moments',

    /**
     * License key for the package (domain-based validation)
     * Generate: hash('sha256', $domain . $license_secret)
     */
    'license_key' => env('MOMENTS_LICENSE_KEY', ''),

    /**
     * License secret for hash validation
     */
    'license_secret' => env('MOMENTS_LICENSE_SECRET', 'moments-secret-key'),
    
    /**
     * The default moment privacy setting
     */
    'default_privacy' => 'public',
    
    /**
     * Maximum file size for moment uploads (in KB)
     */
    'max_file_size' => 10240,
    
    /**
     * Allowed file types for moment uploads
     */
    'allowed_file_types' => ['image/jpeg', 'image/png', 'image/gif', 'video/mp4'],
    
    /**
     * Enable/disable moment comments
     */
    'comments_enabled' => true,
    
    /**
     * Enable/disable moment likes
     */
    'likes_enabled' => true,
];
