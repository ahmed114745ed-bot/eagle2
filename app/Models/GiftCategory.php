<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftCategory extends Model
{
    use HasFactory, TimestampsWithTimezone;
    protected $guarded = [];
    protected $casts = [
        'title' => 'array',
    ];
    
    // Disable caching for Octane compatibility
    public $timestamps = true;
    
    // Prevent Octane from caching this model
    protected static $unguarded = false;

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];
}
