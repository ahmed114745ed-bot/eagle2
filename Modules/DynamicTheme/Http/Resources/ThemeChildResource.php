<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeChildResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'theme_id' => $this->theme_id,
            'child_key' => $this->child_key,
            'child_type' => $this->child_type,
            'label' => $this->label,
            'order' => $this->order,
            'is_visible' => (bool) $this->is_visible,
            'is_active' => (bool) $this->is_active,
            'action' => $this->action,
            'position' => $this->position,
     
            'assets' => ThemeChildAssetResource::collection($this->whenLoaded('assets')) ?? [],
        ];
    }
}
