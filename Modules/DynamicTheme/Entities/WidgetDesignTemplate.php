<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetDesignTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'configuration_id',
        'name',
        'description',
        'design_config',
        'preview_data',
        'is_public',
    ];

    protected $casts = [
        'design_config' => 'array',
        'preview_data' => 'array',
        'is_public' => 'boolean',
    ];

    /**
     * Get the configuration
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(ClientConfiguration::class, 'configuration_id');
    }

    /**
     * Scope for public templates
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Export template for reuse
     */
    public function export(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'design_config' => $this->design_config,
            'preview_data' => $this->preview_data,
        ];
    }

    /**
     * Import template from export
     */
    public static function importTemplate(array $data, int $configurationId): self
    {
        return static::create([
            'configuration_id' => $configurationId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'design_config' => $data['design_config'],
            'preview_data' => $data['preview_data'] ?? null,
            'is_public' => $data['is_public'] ?? false,
        ]);
    }
}
