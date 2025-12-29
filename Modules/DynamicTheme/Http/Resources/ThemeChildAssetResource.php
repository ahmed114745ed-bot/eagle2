<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeChildAssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'child_id' => $this->child_id,
            'asset_key' => $this->asset_key,
            'asset_label' => $this->asset_label,
            'asset_type' => $this->asset_type,
            'default_url' => $this->default_url,
            'file_path' => $this->file_path,
            'original_filename' => $this->original_filename,
            'is_required' => (bool) $this->is_required,
            'order' => $this->order,
        ];
    }
}
