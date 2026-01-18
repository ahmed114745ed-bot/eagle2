<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetThemeResource extends JsonResource
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
            'theme_key' => $this->theme_key,
            'theme_name' => $this->theme_name,
            'description' => $this->description,
            'preview_image' => $this->preview_image,
            'is_default' => $this->is_default,
            'assets' => ThemeAssetResource::collection($this->whenLoaded('assets')),
            'children' => ThemeChildResource::collection($this->whenLoaded('children')),
        ];
    }
}
