<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThemeChild extends Model
{
    protected $fillable = [
        'theme_id', 'child_key', 'label', 'child_type',
        'action', 'is_visible', 'is_active', 'position', 'hide',
        // Designer fields
        'width', 'height', 'x', 'y', 'rotation', 'scale', 'opacity', 'z_index',
        'background_color', 'border_style', 'border_width', 'border_color',
        'border_radius_tl', 'border_radius_tr', 'border_radius_bl', 'border_radius_br',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_active' => 'boolean',
        'hide' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
        'x' => 'integer',
        'y' => 'integer',
        'rotation' => 'float',
        'scale' => 'float',
        'opacity' => 'float',
        'z_index' => 'integer',
        'border_width' => 'integer',
        'border_radius_tl' => 'integer',
        'border_radius_tr' => 'integer',
        'border_radius_bl' => 'integer',
        'border_radius_br' => 'integer',
    ];

    public function theme() {
        return $this->belongsTo(WidgetTheme::class);
    }

       public function assets(): HasMany
    {
        return $this->hasMany(ThemeAsset::class, 'child_id')->orderBy('order');
    }
}
