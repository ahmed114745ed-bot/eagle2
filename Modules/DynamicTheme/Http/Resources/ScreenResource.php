<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScreenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       
        // Get active widgets ordered by order
        $widgets = $this->activeWidgets()
            ->with(['widget', 'theme.children', 'children'  => function ($query) {
                $query->orderBy('order');
            }])
            ->get();

        return [
            'screen_key' => $this->screen_key,
            'screen_name' => $this->screen_name,
            'min_app_version' => $this->min_app_version,
            'layout' => $this->layout ?? [
                'direction' => 'vertical',
                'background_color' => '#FFFFFF',
                'background_asset' => null,
            ],
            'widgets' => ScreenWidgetResource::collection($widgets),
        ];
    }
}
