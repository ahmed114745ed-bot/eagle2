<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeChildAssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Build file_url from file_path or use default_url
        $fileUrl = $this->default_url;
        if ($this->file_path) {
            $fileUrl = asset('storage/' . $this->file_path);
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
