<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetThemeV2Resource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'widget_id' => $this->widget_id,
            'theme_key' => $this->theme_key,
            'theme_name' => $this->theme_name,
            'preview_image' => $this->preview_image,
            'description' => $this->description,
            'is_default' => (bool) $this->is_default,
            'is_active' => (bool) $this->is_active,
            'children' => ThemeChildResource::collection($this->whenLoaded('children')),
        ];
    }
}
