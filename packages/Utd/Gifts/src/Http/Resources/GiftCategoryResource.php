<?php

namespace Utd\Gifts\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class GiftCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        $locale = App::getLocale();

        return [
            'id' => $this->id,
            'title' => $this->getLocalizedValue($this->title, $locale),
            'type' => $this->type,
        ];
    }

    private function getLocalizedValue($value, $locale)
    {
        if (is_array($value)) {
            return $value[$locale] ?? ($value['en'] ?? '');
        }

        return $value;
    }
}
