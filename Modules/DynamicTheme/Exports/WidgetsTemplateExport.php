<?php

namespace Modules\DynamicTheme\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WidgetsTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'widgets' => new WidgetsTemplateSheet(),
            'settings' => new SettingsTemplateSheet(),
            'actions' => new ActionsTemplateSheet(),
        ];
    }
}

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WidgetsTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        // Sample data row
        return [
            [
                'banner',                    // widget_type
                'custom_banner',             // widget_key
                'Custom Banner Widget',      // display_name
                'A custom banner widget',    // description
                'true',                      // is_repeatable
                'false',                     // has_children
                '2.0.0',                     // min_app_version
                'image',                     // icon
                'true',                      // is_active
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'widget_type',
            'widget_key',
            'display_name',
            'description',
            'is_repeatable',
            'has_children',
            'min_app_version',
            'icon',
            'is_active',
        ];
    }

    public function title(): string
    {
        return 'widgets';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
        ]);

        // Add comments/notes to headers
        $sheet->getComment('A1')->getText()->createTextRun('Widget type: banner, room, ranking, tab_bar, categories, country_filter, floating_button');
        $sheet->getComment('B1')->getText()->createTextRun('Unique key for the widget (snake_case)');
        $sheet->getComment('E1')->getText()->createTextRun('Can this widget be added multiple times? (true/false)');
        $sheet->getComment('F1')->getText()->createTextRun('Does this widget have children like tabs? (true/false)');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 20,
            'C' => 25,
            'D' => 30,
            'E' => 15,
            'F' => 15,
            'G' => 18,
            'H' => 12,
            'I' => 12,
        ];
    }
}

class SettingsTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            [
                'custom_banner',             // widget_key
                'height_percentage',         // setting_key
                'Height (%)',                // setting_label
                'number',                    // setting_type
                'primary',                   // setting_category
                '20',                        // default_value
                '',                          // options (JSON for select type)
                '{"min": 10, "max": 50}',    // validation_rules
                'false',                     // is_hidden
                '1',                         // order
            ],
            [
                'custom_banner',
                'style',
                'Banner Style',
                'select',
                'secondary',
                'modern',
                '[{"value": "modern", "label": "Modern"}, {"value": "classic", "label": "Classic"}]',
                '',
                'false',
                '2',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'widget_key',
            'setting_key',
            'setting_label',
            'setting_type',
            'setting_category',
            'default_value',
            'options',
            'validation_rules',
            'is_hidden',
            'order',
        ];
    }

    public function title(): string
    {
        return 'settings';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '70AD47'],
            ],
        ]);

        $sheet->getComment('D1')->getText()->createTextRun('Types: text, number, boolean, select, color, json, range');
        $sheet->getComment('E1')->getText()->createTextRun('primary = affects data, secondary = affects appearance');
        $sheet->getComment('G1')->getText()->createTextRun('JSON array for select type: [{"value": "x", "label": "X"}]');
        $sheet->getComment('I1')->getText()->createTextRun('Hidden settings are not shown in dashboard');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 15,
            'E' => 18,
            'F' => 15,
            'G' => 50,
            'H' => 30,
            'I' => 12,
            'J' => 10,
        ];
    }
}

class ActionsTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            [
                'custom_banner',             // widget_key
                'screen',                    // action_type
                'Navigate to Screen',        // action_label
                'true',                      // requires_target
                'screen_key',                // target_type
                'Opens a new screen',        // description
                'true',                      // is_active
            ],
            [
                'custom_banner',
                'webview',
                'Open WebView',
                'true',
                'url',
                'Opens a web page',
                'true',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'widget_key',
            'action_type',
            'action_label',
            'requires_target',
            'target_type',
            'description',
            'is_active',
        ];
    }

    public function title(): string
    {
        return 'actions';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ED7D31'],
            ],
        ]);

        $sheet->getComment('B1')->getText()->createTextRun('Types: screen, webview, filter, internal');
        $sheet->getComment('E1')->getText()->createTextRun('Target types: screen_key, url, filter_key, action_key');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 15,
            'C' => 25,
            'D' => 18,
            'E' => 15,
            'F' => 30,
            'G' => 12,
        ];
    }
}
