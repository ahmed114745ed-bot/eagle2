<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetSettingsDefinitionResource extends JsonResource
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
            'setting_key' => $this->setting_key,
            'setting_label' => $this->setting_label,
            'setting_type' => $this->setting_type,
            'default_value' => $this->getCastedDefaultValue(),
            'options' => $this->options,
            'validation_rules' => $this->validation_rules,
        ];
    }
}
