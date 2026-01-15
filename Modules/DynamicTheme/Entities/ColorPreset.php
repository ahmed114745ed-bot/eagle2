<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColorPreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'configuration_id',
        'name',
        'description',
        'colors',
        'is_default',
    ];

    protected $casts = [
        'colors' => 'array',
        'is_default' => 'boolean',
    ];

    /**
     * Get the configuration
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(ClientConfiguration::class, 'configuration_id');
    }

    /**
     * Get primary color
     */
    public function getPrimaryColor(): ?string
    {
        return $this->colors['primary'] ?? null;
    }

    /**
     * Get secondary color
     */
    public function getSecondaryColor(): ?string
    {
        return $this->colors['secondary'] ?? null;
    }

    /**
     * Get accent color
     */
    public function getAccentColor(): ?string
    {
        return $this->colors['accent'] ?? null;
    }

    /**
     * Export as CSS variables
     */
    public function toCSSVariables(): string
    {
        $vars = '';
        foreach ($this->colors as $key => $value) {
            $vars .= "--color-{$key}: {$value};";
        }
        return $vars;
    }
}
