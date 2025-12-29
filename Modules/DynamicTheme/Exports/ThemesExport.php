<?php

namespace Modules\DynamicTheme\Exports;

use Modules\DynamicTheme\Entities\WidgetTheme;
use Modules\DynamicTheme\Entities\ThemeAsset;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ThemesExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'themes' => new ThemesSheetExport(),
            'assets' => new ThemeAssetsSheetExport(),
        ];
    }
}

// Themes Sheet
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ThemesSheetExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function collection()
    {
        return WidgetTheme::with('widget')->get()->map(function ($theme) {
            return [
                'widget_key' => $theme->widget->widget_key ?? '',
                'theme_key' => $theme->theme_key,
                'theme_name' => $theme->theme_name,
                'preview_image' => $theme->preview_image,
                'description' => $theme->description,
                'is_default' => $theme->is_default ? 'true' : 'false',
                'is_active' => $theme->is_active ? 'true' : 'false',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'widget_key',
            'theme_key',
            'theme_name',
            'preview_image',
            'description',
            'is_default',
            'is_active',
        ];
    }

    public function title(): string
    {
        return 'themes';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}

// Theme Assets Sheet
class ThemeAssetsSheetExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function collection()
    {
        return ThemeAsset::with('theme')->get()->map(function ($asset) {
            return [
                'theme_key' => $asset->theme->theme_key ?? '',
                'asset_key' => $asset->asset_key,
                'asset_label' => $asset->asset_label,
                'asset_type' => $asset->asset_type,
                'default_url' => $asset->default_url,
                'is_required' => $asset->is_required ? 'true' : 'false',
                'order' => $asset->order,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'theme_key',
            'asset_key',
            'asset_label',
            'asset_type',
            'default_url',
            'is_required',
            'order',
        ];
    }

    public function title(): string
    {
        return 'assets';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}
