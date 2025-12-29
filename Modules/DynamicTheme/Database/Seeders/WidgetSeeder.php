<?php

namespace Modules\DynamicTheme\Database\Seeders;

use Modules\DynamicTheme\Entities\Widget;
use Modules\DynamicTheme\Entities\WidgetAction;
use Modules\DynamicTheme\Entities\WidgetSettingsDefinition;
use Illuminate\Database\Seeder;

class WidgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $widgets = [
            // Tab Bar Widget
            [
                'widget_type' => 'tab_bar',
                'widget_key' => 'main_navigation',
                'display_name' => 'Tab Bar Navigation',
                'description' => 'Main navigation tab bar with tabs and special buttons',
                'is_repeatable' => false,
                'has_children' => true,
                'min_app_version' => '2.0.0',
                'icon' => 'tabs',
                'settings' => [
                    ['setting_key' => 'height_percentage', 'setting_label' => 'Height (%)', 'setting_type' => 'number', 'setting_category' => 'primary', 'default_value' => '8', 'validation_rules' => ['min' => 5, 'max' => 15], 'is_hidden' => false, 'order' => 1],
                    ['setting_key' => 'is_sticky', 'setting_label' => 'Sticky Header', 'setting_type' => 'boolean', 'setting_category' => 'primary', 'default_value' => 'true', 'is_hidden' => false, 'order' => 2],
                ],
                'actions' => [
                    ['action_type' => 'screen', 'action_label' => 'Navigate to Screen', 'requires_target' => true, 'target_type' => 'screen_key'],
                    ['action_type' => 'internal', 'action_label' => 'Internal Action', 'requires_target' => true, 'target_type' => 'action_key'],
                ],
            ],

            // Banner Widget
            [
                'widget_type' => 'banner',
                'widget_key' => 'banner_slider',
                'display_name' => 'Banner Slider',
                'description' => 'Sliding banner with auto-scroll support',
                'is_repeatable' => true,
                'has_children' => false,
                'min_app_version' => '2.0.0',
                'icon' => 'image',
                'settings' => [
                    ['setting_key' => 'banner_type', 'setting_label' => 'Banner Type', 'setting_type' => 'select', 'setting_category' => 'primary', 'default_value' => 'live_banners', 'options' => [['value' => 'live_banners', 'label' => 'Live Banners'], ['value' => 'voice_room_banners', 'label' => 'Voice Room Banners'], ['value' => 'event_banners', 'label' => 'Event Banners']], 'is_hidden' => true, 'order' => 1],
                    ['setting_key' => 'height_percentage', 'setting_label' => 'Height (%)', 'setting_type' => 'number', 'setting_category' => 'primary', 'default_value' => '18', 'validation_rules' => ['min' => 10, 'max' => 30], 'is_hidden' => false, 'order' => 2],
                    ['setting_key' => 'auto_scroll', 'setting_label' => 'Auto Scroll', 'setting_type' => 'boolean', 'setting_category' => 'primary', 'default_value' => 'true', 'is_hidden' => false, 'order' => 3],
                    ['setting_key' => 'scroll_interval_seconds', 'setting_label' => 'Scroll Interval (seconds)', 'setting_type' => 'number', 'setting_category' => 'primary', 'default_value' => '5', 'validation_rules' => ['min' => 2, 'max' => 10], 'is_hidden' => false, 'order' => 4],
                    ['setting_key' => 'border_radius', 'setting_label' => 'Border Radius', 'setting_type' => 'number', 'setting_category' => 'secondary', 'default_value' => '12', 'is_hidden' => false, 'order' => 1],
                    ['setting_key' => 'show_indicators', 'setting_label' => 'Show Indicators', 'setting_type' => 'boolean', 'setting_category' => 'secondary', 'default_value' => 'true', 'is_hidden' => false, 'order' => 2],
                    ['setting_key' => 'indicator_style', 'setting_label' => 'Indicator Style', 'setting_type' => 'select', 'setting_category' => 'secondary', 'default_value' => 'dots', 'options' => [['value' => 'dots', 'label' => 'Dots'], ['value' => 'lines', 'label' => 'Lines'], ['value' => 'numbers', 'label' => 'Numbers']], 'is_hidden' => false, 'order' => 3],
                ],
                'actions' => [],
            ],

            // Room Widget
            [
                'widget_type' => 'room',
                'widget_key' => 'room_display',
                'display_name' => 'Room Display',
                'description' => 'Display rooms in grid or list layout',
                'is_repeatable' => true,
                'has_children' => false,
                'min_app_version' => '2.0.0',
                'icon' => 'grid',
                'settings' => [
                    ['setting_key' => 'from_rank', 'setting_label' => 'From Rank', 'setting_type' => 'number', 'setting_category' => 'primary', 'default_value' => '1', 'validation_rules' => ['min' => 1], 'is_hidden' => false, 'order' => 1],
                    ['setting_key' => 'to_rank', 'setting_label' => 'To Rank (-1 for all)', 'setting_type' => 'number', 'setting_category' => 'primary', 'default_value' => '4', 'validation_rules' => ['min' => -1], 'is_hidden' => false, 'order' => 2],
                    ['setting_key' => 'layout', 'setting_label' => 'Layout', 'setting_type' => 'select', 'setting_category' => 'primary', 'default_value' => 'grid', 'options' => [['value' => 'grid', 'label' => 'Grid'], ['value' => 'list', 'label' => 'List']], 'is_hidden' => false, 'order' => 3],
                    ['setting_key' => 'columns', 'setting_label' => 'Columns', 'setting_type' => 'number', 'setting_category' => 'primary', 'default_value' => '2', 'validation_rules' => ['min' => 1, 'max' => 4], 'is_hidden' => false, 'order' => 4],
                    ['setting_key' => 'height_percentage', 'setting_label' => 'Height (%)', 'setting_type' => 'number', 'setting_category' => 'primary', 'default_value' => '45', 'validation_rules' => ['min' => 20, 'max' => 80], 'is_hidden' => false, 'order' => 5],
                    ['setting_key' => 'expand_remaining', 'setting_label' => 'Expand to Fill Remaining', 'setting_type' => 'boolean', 'setting_category' => 'primary', 'default_value' => 'false', 'is_hidden' => false, 'order' => 6],
                    ['setting_key' => 'show_room_name', 'setting_label' => 'Show Room Name', 'setting_type' => 'boolean', 'setting_category' => 'secondary', 'default_value' => 'true', 'is_hidden' => false, 'order' => 1],
                    ['setting_key' => 'show_host_name', 'setting_label' => 'Show Host Name', 'setting_type' => 'boolean', 'setting_category' => 'secondary', 'default_value' => 'true', 'is_hidden' => false, 'order' => 2],
                    ['setting_key' => 'show_viewer_count', 'setting_label' => 'Show Viewer Count', 'setting_type' => 'boolean', 'setting_category' => 'secondary', 'default_value' => 'true', 'is_hidden' => false, 'order' => 3],
                    ['setting_key' => 'show_country_flag', 'setting_label' => 'Show Country Flag', 'setting_type' => 'boolean', 'setting_category' => 'secondary', 'default_value' => 'true', 'is_hidden' => false, 'order' => 4],
                    ['setting_key' => 'room_name_max_lines', 'setting_label' => 'Room Name Max Lines', 'setting_type' => 'number', 'setting_category' => 'secondary', 'default_value' => '1', 'validation_rules' => ['min' => 1, 'max' => 3], 'is_hidden' => false, 'order' => 5],
                ],
                'actions' => [],
            ],

            // Ranking Widget
            [
                'widget_type' => 'ranking',
                'widget_key' => 'ranking_display',
                'display_name' => 'Ranking Display',
                'description' => 'Display user rankings (Wealth Star, Charm Star, etc.)',
                'is_repeatable' => false,
                'has_children' => true,
                'min_app_version' => '2.0.0',
                'icon' => 'trophy',
                'settings' => [
                    ['setting_key' => 'height_percentage', 'setting_label' => 'Height (%)', 'setting_type' => 'number', 'setting_category' => 'primary', 'default_value' => '12', 'validation_rules' => ['min' => 8, 'max' => 20], 'is_hidden' => false, 'order' => 1],
                    ['setting_key' => 'show_top_count', 'setting_label' => 'Show Top Count', 'setting_type' => 'number', 'setting_category' => 'secondary', 'default_value' => '3', 'validation_rules' => ['min' => 1, 'max' => 10], 'is_hidden' => false, 'order' => 1],
                ],
                'actions' => [
                    ['action_type' => 'screen', 'action_label' => 'Navigate to Ranking Detail', 'requires_target' => true, 'target_type' => 'screen_key'],
                ],
            ],

            // Categories Widget
            [
                'widget_type' => 'categories',
                'widget_key' => 'room_categories',
                'display_name' => 'Room Categories',
                'description' => 'Category filter for rooms (Trending, Party, Video, etc.)',
                'is_repeatable' => false,
                'has_children' => true,
                'min_app_version' => '2.0.0',
                'icon' => 'filter',
                'settings' => [
                    ['setting_key' => 'height_percentage', 'setting_label' => 'Height (%)', 'setting_type' => 'number', 'setting_category' => 'primary', 'default_value' => '5', 'validation_rules' => ['min' => 4, 'max' => 10], 'is_hidden' => false, 'order' => 1],
                    ['setting_key' => 'scroll_direction', 'setting_label' => 'Scroll Direction', 'setting_type' => 'select', 'setting_category' => 'secondary', 'default_value' => 'horizontal', 'options' => [['value' => 'horizontal', 'label' => 'Horizontal'], ['value' => 'vertical', 'label' => 'Vertical']], 'is_hidden' => false, 'order' => 1],
                ],
                'actions' => [
                    ['action_type' => 'filter', 'action_label' => 'Filter Rooms', 'requires_target' => true, 'target_type' => 'filter_key'],
                ],
            ],

            // Country Filter Widget
            [
                'widget_type' => 'country_filter',
                'widget_key' => 'country_flags',
                'display_name' => 'Country Filter',
                'description' => 'Filter rooms by country',
                'is_repeatable' => false,
                'has_children' => false,
                'min_app_version' => '2.0.0',
                'icon' => 'flag',
                'settings' => [
                    ['setting_key' => 'height_percentage', 'setting_label' => 'Height (%)', 'setting_type' => 'number', 'setting_category' => 'primary', 'default_value' => '5', 'validation_rules' => ['min' => 4, 'max' => 8], 'is_hidden' => false, 'order' => 1],
                    ['setting_key' => 'flag_size', 'setting_label' => 'Flag Size', 'setting_type' => 'number', 'setting_category' => 'secondary', 'default_value' => '32', 'validation_rules' => ['min' => 24, 'max' => 48], 'is_hidden' => false, 'order' => 1],
                    ['setting_key' => 'show_country_name', 'setting_label' => 'Show Country Name', 'setting_type' => 'boolean', 'setting_category' => 'secondary', 'default_value' => 'false', 'is_hidden' => false, 'order' => 2],
                ],
                'actions' => [
                    ['action_type' => 'filter', 'action_label' => 'Filter by Country', 'requires_target' => true, 'target_type' => 'filter_key'],
                ],
            ],

            // Floating Button Widget
            [
                'widget_type' => 'floating_button',
                'widget_key' => 'floating_action',
                'display_name' => 'Floating Button',
                'description' => 'Floating action button (Games, Events, etc.)',
                'is_repeatable' => true,
                'has_children' => false,
                'min_app_version' => '2.3.0',
                'icon' => 'circle-plus',
                'settings' => [
                    ['setting_key' => 'size_percentage', 'setting_label' => 'Size (%)', 'setting_type' => 'number', 'setting_category' => 'primary', 'default_value' => '12', 'validation_rules' => ['min' => 8, 'max' => 20], 'is_hidden' => false, 'order' => 1],
                ],
                'actions' => [
                    ['action_type' => 'screen', 'action_label' => 'Navigate to Screen', 'requires_target' => true, 'target_type' => 'screen_key'],
                    ['action_type' => 'webview', 'action_label' => 'Open WebView', 'requires_target' => true, 'target_type' => 'url'],
                ],
            ],
        ];

        foreach ($widgets as $widgetData) {
            $settings = $widgetData['settings'] ?? [];
            $actions = $widgetData['actions'] ?? [];
            unset($widgetData['settings'], $widgetData['actions']);

            $widget = Widget::create($widgetData);

            // Create settings definitions
            foreach ($settings as $setting) {
                $setting['widget_id'] = $widget->id;
                if (isset($setting['options'])) {
                    $setting['options'] = $setting['options'];
                }
                if (isset($setting['validation_rules'])) {
                    $setting['validation_rules'] = $setting['validation_rules'];
                }
                WidgetSettingsDefinition::create($setting);
            }

            // Create actions
            foreach ($actions as $action) {
                $action['widget_id'] = $widget->id;
                WidgetAction::create($action);
            }
        }
    }
}
