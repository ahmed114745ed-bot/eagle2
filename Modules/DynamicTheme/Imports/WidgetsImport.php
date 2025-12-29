<?php

namespace Modules\DynamicTheme\Imports;

use Modules\DynamicTheme\Entities\Widget;
use Modules\DynamicTheme\Entities\WidgetAction;
use Modules\DynamicTheme\Entities\WidgetSettingsDefinition;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WidgetsImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'widgets' => new WidgetsSheetImport(),
            'settings' => new WidgetSettingsSheetImport(),
            'actions' => new WidgetActionsSheetImport(),
        ];
    }
}

class WidgetsSheetImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['widget_key'])) {
                continue;
            }

            Widget::updateOrCreate(
                ['widget_key' => $row['widget_key']],
                [
                    'widget_type' => $row['widget_type'],
                    'display_name' => $row['display_name'],
                    'description' => $row['description'] ?? null,
                    'is_repeatable' => $this->parseBoolean($row['is_repeatable'] ?? false),
                    'has_children' => $this->parseBoolean($row['has_children'] ?? false),
                    'min_app_version' => $row['min_app_version'] ?? '1.0.0',
                    'icon' => $row['icon'] ?? null,
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

class WidgetSettingsSheetImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['widget_key']) || empty($row['setting_key'])) {
                continue;
            }

            $widget = Widget::where('widget_key', $row['widget_key'])->first();
            if (!$widget) {
                continue;
            }

            $options = null;
            if (!empty($row['options'])) {
                $options = json_decode($row['options'], true);
            }

            $validationRules = null;
            if (!empty($row['validation_rules'])) {
                $validationRules = json_decode($row['validation_rules'], true);
            }

            WidgetSettingsDefinition::updateOrCreate(
                [
                    'widget_id' => $widget->id,
                    'setting_key' => $row['setting_key'],
                ],
                [
                    'setting_label' => $row['setting_label'],
                    'setting_type' => $row['setting_type'] ?? 'text',
                    'setting_category' => $row['setting_category'] ?? 'primary',
                    'default_value' => $row['default_value'] ?? null,
                    'options' => $options,
                    'validation_rules' => $validationRules,
                    'is_hidden' => $this->parseBoolean($row['is_hidden'] ?? false),
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

class WidgetActionsSheetImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['widget_key']) || empty($row['action_type'])) {
                continue;
            }

            $widget = Widget::where('widget_key', $row['widget_key'])->first();
            if (!$widget) {
                continue;
            }

            WidgetAction::updateOrCreate(
                [
                    'widget_id' => $widget->id,
                    'action_type' => $row['action_type'],
                ],
                [
                    'action_label' => $row['action_label'],
                    'requires_target' => $this->parseBoolean($row['requires_target'] ?? true),
                    'target_type' => $row['target_type'] ?? null,
                    'description' => $row['description'] ?? null,
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
