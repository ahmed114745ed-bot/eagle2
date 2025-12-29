<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScreenWidgetChildResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->child_key,
            'child_key' => $this->child_key,
            'child_type' => $this->child_type,
            'label' => $this->label,
            'is_visible' => $this->is_visible,
            'is_active' => $this->is_active,
        ];

        // Add action if exists
        if ($this->action) {
            $data['action'] = $this->action;
        }

        // Add assets if exists
        if ($this->assets) {
            $data['assets'] = $this->assets;
        }

        // Add position for special children
        if ($this->position) {
            $data['position'] = $this->position;
        }

        return $data;
    }
}
