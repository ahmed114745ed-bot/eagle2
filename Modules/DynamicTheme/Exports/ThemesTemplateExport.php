<?php

namespace Modules\DynamicTheme\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ThemesTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'themes' => new ThemesTemplateSheet(),
            'assets' => new AssetsTemplateSheet(),
        ];
    }
}

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ThemesTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            [
                'custom_banner',                 // widget_key
                'custom_banner_modern',          // theme_key
                'Modern Banner Theme',           // theme_name
                '',                              // preview_image
                'A modern looking banner',       // description
                'true',                          // is_default
                'true',                          // is_active
            ],
            [
                'custom_banner',
                'custom_banner_classic',
                'Classic Banner Theme',
                '',
                'A classic looking banner',
                'false',
                'true',
            ],
        ];
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
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7030A0'],
            ],
        ]);

        $sheet->getComment('A1')->getText()->createTextRun('The widget_key must exist in widgets table');
        $sheet->getComment('B1')->getText()->createTextRun('Unique key for the theme (snake_case)');
        $sheet->getComment('D1')->getText()->createTextRun('URL to preview image for dashboard');
        $sheet->getComment('F1')->getText()->createTextRun('Only one theme per widget should be default');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 25,
            'D' => 30,
            'E' => 30,
            'F' => 12,
            'G' => 12,
        ];
    }
}

class AssetsTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            [
                'custom_banner_modern',      // theme_key
                'background',                // asset_key
                'Background Image',          // asset_label
                'image',                     // asset_type
                '',                          // default_url
                'false',                     // is_required
                '1',                         // order
            ],
            [
                'custom_banner_modern',
                'frame',
                'Banner Frame',
                'svga',
                '',
                'true',
                '2',
            ],
            [
                'custom_banner_modern',
                'animation',
                'Animation Effect',
                'vap',
                '',
                'false',
                '3',
            ],
        ];
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
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '00B0F0'],
            ],
        ]);

        $sheet->getComment('A1')->getText()->createTextRun('The theme_key must exist in themes sheet');
        $sheet->getComment('D1')->getText()->createTextRun('Asset types: image, svga, vap, alpha');
        $sheet->getComment('F1')->getText()->createTextRun('Required assets must be provided by the client');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 25,
            'D' => 15,
            'E' => 40,
            'F' => 15,
            'G' => 10,
        ];
    }
}
