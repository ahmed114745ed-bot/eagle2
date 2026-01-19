<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ThemeChildAssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Build file_url from file_path first, then fallback to default_url
        $fileUrl = null;
        
        // Priority 1: Use file_path with Storage::url() for GCS
        if ($this->file_path) {
            $fileUrl = Storage::disk('admin')->url($this->file_path);
        }
        
        // Priority 2: Use default_url but fix it if it's using local storage
        if (!$fileUrl && $this->default_url) {
            $defaultUrl = $this->default_url;
            // Fix old local storage URLs to use GCS
            if (str_contains($defaultUrl, '/storage/') && !str_contains($defaultUrl, 'googleapis.com')) {
                // Extract the path after /storage/
                $path = preg_replace('/^.*\/storage\//', '', $defaultUrl);
                if ($path) {
                    $fileUrl = Storage::disk('admin')->url($path);
                }
            } else {
                $fileUrl = $defaultUrl;
            }
        }
        
        // For text assets, include the text content
        $textContent = null;
        if ($this->asset_type === 'text') {
            $textContent = $this->text_content ?? $this->asset_label ?? $this->asset_key;
        }
        
        return [
            'id' => $this->id,
            'child_id' => $this->child_id,
            'asset_key' => $this->asset_key,
            'asset_label' => $this->asset_label,
            'name' => $this->asset_label ?? $this->asset_key,
            'asset_type' => $this->asset_type,
            'default_url' => $this->default_url,
            'file_url' => $fileUrl,
            'url' => $fileUrl,
            'file_path' => $this->file_path,
            'original_filename' => $this->original_filename,
            'is_required' => (bool) $this->is_required,
            'order' => $this->order,
            'text_content' => $textContent,
            // Designer fields - use database values or defaults
            'width' => $this->width ?? 80,
            'height' => $this->height ?? 80,
            'x' => $this->x ?? 0,
            'y' => $this->y ?? 0,
            'rotation' => $this->rotation ?? 0,
            'scale' => $this->scale ?? 1,
            'opacity' => $this->opacity ?? 1,
            'z_index' => $this->z_index ?? 0,
            'is_visible' => true,
        ];
    }
}
