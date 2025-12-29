<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScreenWidgetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => (string) $this->id,
            'theme_id' => $this->theme_id,
            'widget_type' => $this->widget->widget_type,
            'widget_key' => $this->widget->widget_key,
            'order' => $this->order,
            'min_app_version' => $this->min_app_version,
            'is_positioned' => $this->is_positioned,
            'theme' => $this->theme ? new WidgetThemeV2Resource($this->theme) : null
        ];

        // Add position for positioned widgets
        if ($this->is_positioned && $this->position) {
            $data['position'] = $this->position;
        } else {
            $data['position'] = null;
        }

        // Add theme key
        $data['theme_key'] = $this->theme ? $this->theme->theme_key : null;

        // Merge primary settings with defaults
        $data['primary_settings'] = $this->getMergedPrimarySettings();

        // Merge secondary settings with defaults
        $data['secondary_settings'] = $this->getMergedSecondarySettings();

        // Add action if exists (for floating buttons, etc.)
        if ($this->action) {
            $data['action'] = $this->action;
        }

        // Add children if widget has children
        if ($this->widget->has_children && $this->children->count() > 0) {
            // Separate regular children from special children
            $regularChildren = $this->children->filter(fn($child) => $child->child_type !== 'special');
            $specialChildren = $this->children->filter(fn($child) => $child->child_type === 'special');

            $data['children'] = ScreenWidgetChildResource::collection($regularChildren);

            if ($specialChildren->count() > 0) {
                $data['special_children'] = ScreenWidgetChildResource::collection($specialChildren);
            }
        }

        return $data;
    }
}
