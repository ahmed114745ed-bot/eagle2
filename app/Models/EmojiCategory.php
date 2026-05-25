<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmojiCategory extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'title' => 'array',
    ];

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    protected static function booted(): void
    {
        static::saved(fn () => \Cache::forget('emoji_categories'));
        static::deleted(fn () => \Cache::forget('emoji_categories'));
    }
}
