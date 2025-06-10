<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'default_image', 'localized_images', 'priority'];

    protected $casts = [
        'localized_images' => 'array',
    ];

    public function getLocalizedImage(string $langCode): ?string
    {
        return $this->localized_images[$langCode] ?? null;
    }

    public function setLocalizedImage(string $langCode, string $imagePath): void
    {
        $images = $this->localized_images ?? [];
        $images[$langCode] = $imagePath;
        $this->localized_images = $images;
    }
}
