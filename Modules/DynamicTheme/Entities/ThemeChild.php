<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThemeChild extends Model
{
    protected $fillable = [
        'theme_id', 'child_key', 'label', 'child_type',
        'action', 'is_visible', 'is_active', 'position', 'hide'
    ];

    public function theme() {
        return $this->belongsTo(WidgetTheme::class);
    }

       public function assets(): HasMany
    {
        return $this->hasMany(ThemeAsset::class, 'child_id')->orderBy('order');
    }
}
