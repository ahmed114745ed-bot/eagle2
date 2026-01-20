<?php

return [
    'name' => 'Moments',
    
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
