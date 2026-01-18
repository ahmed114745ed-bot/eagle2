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
            // Designer fields
            'width' => $this->width ?? 300,
            'height' => $this->height ?? 200,
            'x' => $this->x ?? 0,
            'y' => $this->y ?? 0,
            'rotation' => $this->rotation ?? 0,
            'scale' => $this->scale ?? 1,
            'opacity' => $this->opacity ?? 1,
            'z_index' => $this->z_index ?? 0,
            'background_color' => $this->background_color,
            'border_style' => $this->border_style,
            'assets' => ThemeChildAssetResource::collection($this->whenLoaded('assets')) ?? [],
        ];
    }
}
