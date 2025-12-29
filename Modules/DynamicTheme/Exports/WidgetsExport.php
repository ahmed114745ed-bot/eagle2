<?php

namespace Modules\DynamicTheme\Exports;

use Modules\DynamicTheme\Entities\Widget;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WidgetsExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'widgets' => new WidgetsSheetExport(),
            'settings' => new WidgetSettingsSheetExport(),
            'actions' => new WidgetActionsSheetExport(),
        ];
    }
}

// Widgets Sheet
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WidgetsSheetExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function collection()
    {
        return Widget::all()->map(function ($widget) {
            return [
                'widget_type' => $widget->widget_type,
                'widget_key' => $widget->widget_key,
                'display_name' => $widget->display_name,
                'description' => $widget->description,
                'is_repeatable' => $widget->is_repeatable ? 'true' : 'false',
                'has_children' => $widget->has_children ? 'true' : 'false',
                'min_app_version' => $widget->min_app_version,
                'icon' => $widget->icon,
                'is_active' => $widget->is_active ? 'true' : 'false',
            ];
        });
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
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}

// Widget Settings Sheet
use Modules\DynamicTheme\Entities\WidgetSettingsDefinition;

class WidgetSettingsSheetExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function collection()
    {
        return WidgetSettingsDefinition::with('widget')->get()->map(function ($setting) {
            return [
                'widget_key' => $setting->widget->widget_key ?? '',
                'setting_key' => $setting->setting_key,
                'setting_label' => $setting->setting_label,
                'setting_type' => $setting->setting_type,
                'setting_category' => $setting->setting_category,
                'default_value' => $setting->default_value,
                'options' => $setting->options ? json_encode($setting->options) : '',
                'validation_rules' => $setting->validation_rules ? json_encode($setting->validation_rules) : '',
                'is_hidden' => $setting->is_hidden ? 'true' : 'false',
                'order' => $setting->order,
            ];
        });
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
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}

// Widget Actions Sheet
use Modules\DynamicTheme\Entities\WidgetAction;

class WidgetActionsSheetExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function collection()
    {
        return WidgetAction::with('widget')->get()->map(function ($action) {
            return [
                'widget_key' => $action->widget->widget_key ?? '',
                'action_type' => $action->action_type,
                'action_label' => $action->action_label,
                'requires_target' => $action->requires_target ? 'true' : 'false',
                'target_type' => $action->target_type,
                'description' => $action->description,
                'is_active' => $action->is_active ? 'true' : 'false',
            ];
        });
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
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}
