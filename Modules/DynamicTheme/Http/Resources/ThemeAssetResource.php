<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeAssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_key' => $this->asset_key,
            'asset_label' => $this->asset_label,
            'asset_type' => $this->asset_type,
            'default_url' => $this->default_url,
            'is_required' => $this->is_required,
        ];
    }
}
