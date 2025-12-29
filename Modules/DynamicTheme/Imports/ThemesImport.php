<?php

namespace Modules\DynamicTheme\Imports;

use Modules\DynamicTheme\Entities\Widget;
use Modules\DynamicTheme\Entities\WidgetTheme;
use Modules\DynamicTheme\Entities\ThemeAsset;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ThemesImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'themes' => new ThemesSheetImport(),
            'assets' => new ThemeAssetsSheetImport(),
        ];
    }
}

class ThemesSheetImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['theme_key']) || empty($row['widget_key'])) {
                continue;
            }

            $widget = Widget::where('widget_key', $row['widget_key'])->first();
            if (!$widget) {
                continue;
            }

            WidgetTheme::updateOrCreate(
                ['theme_key' => $row['theme_key']],
                [
                    'widget_id' => $widget->id,
                    'theme_name' => $row['theme_name'],
                    'preview_image' => $row['preview_image'] ?? null,
                    'description' => $row['description'] ?? null,
                    'is_default' => $this->parseBoolean($row['is_default'] ?? false),
                    'is_active' => $this->parseBoolean($row['is_active'] ?? true),
                ]
            );
        }
    }

    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower((string) $value), ['true', '1', 'yes', 'نعم']);
    }
}

class ThemeAssetsSheetImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['theme_key']) || empty($row['asset_key'])) {
                continue;
            }

            $theme = WidgetTheme::where('theme_key', $row['theme_key'])->first();
            if (!$theme) {
                continue;
            }

            ThemeAsset::updateOrCreate(
                [
                    'theme_id' => $theme->id,
                    'asset_key' => $row['asset_key'],
                ],
                [
                    'asset_label' => $row['asset_label'],
                    'asset_type' => $row['asset_type'] ?? 'image',
                    'default_url' => $row['default_url'] ?? null,
                    'is_required' => $this->parseBoolean($row['is_required'] ?? false),
                    'order' => (int) ($row['order'] ?? 0),
                ]
            );
        }
    }

    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower((string) $value), ['true', '1', 'yes', 'نعم']);
    }
}
