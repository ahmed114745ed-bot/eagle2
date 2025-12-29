<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetResource extends JsonResource
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
            'widget_type' => $this->widget_type,
            'widget_key' => $this->widget_key,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'is_repeatable' => $this->is_repeatable,
            'has_children' => $this->has_children,
            'min_app_version' => $this->min_app_version,
            'icon' => $this->icon,
            'themes' => WidgetThemeResource::collection($this->whenLoaded('activeThemes')),
            'primary_settings' => WidgetSettingsDefinitionResource::collection(
                $this->whenLoaded('primarySettings', fn() => $this->primarySettings->where('is_hidden', false))
            ),
            'secondary_settings' => WidgetSettingsDefinitionResource::collection(
                $this->whenLoaded('secondarySettings', fn() => $this->secondarySettings->where('is_hidden', false))
            ),
            'actions' => WidgetActionResource::collection($this->whenLoaded('actions')),
        ];
    }
}
