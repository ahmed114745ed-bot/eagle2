<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class GiftCategory extends Model implements Sortable
{
    use HasFactory, TimestampsWithTimezone, SortableTrait;
    protected $guarded = [];
    protected $casts = [
        'title' => 'array',
    ];

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];
}
