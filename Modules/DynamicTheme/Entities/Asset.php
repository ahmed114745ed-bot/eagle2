<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_name',
        'file_path',
        'file_url',
        'asset_type',
        'mime_type',
        'file_size',
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Scope by asset type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('asset_type', $type);
    }

    /**
     * Scope for images
     */
    public function scopeImages($query)
    {
        return $query->where('asset_type', 'image');
    }

    /**
     * Scope for SVGA files
     */
    public function scopeSvga($query)
    {
        return $query->where('asset_type', 'svga');
    }

    /**
     * Scope for VAP files
     */
    public function scopeVap($query)
    {
        return $query->where('asset_type', 'vap');
    }

    /**
     * Scope for Alpha files
     */
    public function scopeAlpha($query)
    {
        return $query->where('asset_type', 'alpha');
    }

    /**
     * Get asset types
     */
    public static function getAssetTypes(): array
    {
        return ['image', 'svga', 'vap', 'alpha'];
    }

    /**
     * Get file size in human readable format
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Delete the file when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($asset) {
            if ($asset->file_path && Storage::exists($asset->file_path)) {
                Storage::delete($asset->file_path);
            }
        });
    }
}
