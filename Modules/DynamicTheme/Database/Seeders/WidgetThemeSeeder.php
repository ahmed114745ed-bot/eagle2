<?php

namespace Modules\DynamicTheme\Database\Seeders;

use Modules\DynamicTheme\Entities\Widget;
use Modules\DynamicTheme\Entities\WidgetTheme;
use Modules\DynamicTheme\Entities\ThemeAsset;
use Illuminate\Database\Seeder;

class WidgetThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            // Tab Bar Themes
            'main_navigation' => [
                [
                    'theme_key' => 'tab_bar_golden',
                    'theme_name' => 'Golden Tab Bar',
                    'description' => 'Golden styled tab bar with animated indicators',
                    'is_default' => true,
                    'assets' => [
                        ['asset_key' => 'background', 'asset_label' => 'Background', 'asset_type' => 'image', 'is_required' => false],
                        ['asset_key' => 'active_indicator', 'asset_label' => 'Active Tab Indicator', 'asset_type' => 'svga', 'is_required' => false],
                    ],
                ],
                [
                    'theme_key' => 'tab_bar_minimal',
                    'theme_name' => 'Minimal Tab Bar',
                    'description' => 'Clean minimal tab bar design',
                    'is_default' => false,
                    'assets' => [
                        ['asset_key' => 'active_indicator', 'asset_label' => 'Active Tab Indicator', 'asset_type' => 'image', 'is_required' => false],
                    ],
                ],
            ],

            // Banner Themes
            'banner_slider' => [
                [
                    'theme_key' => 'banner_slider_auto',
                    'theme_name' => 'Auto Slider Banner',
                    'description' => 'Auto-scrolling banner with frame support',
                    'is_default' => true,
                    'assets' => [
                        ['asset_key' => 'frame', 'asset_label' => 'Banner Frame', 'asset_type' => 'image', 'is_required' => false],
                    ],
                ],
                [
                    'theme_key' => 'banner_carousel',
                    'theme_name' => 'Carousel Banner',
                    'description' => 'Carousel style banner with 3D effect',
                    'is_default' => false,
                    'assets' => [
                        ['asset_key' => 'frame', 'asset_label' => 'Banner Frame', 'asset_type' => 'image', 'is_required' => false],
                        ['asset_key' => 'shadow', 'asset_label' => 'Shadow Effect', 'asset_type' => 'image', 'is_required' => false],
                    ],
                ],
            ],

            // Room Themes
            'room_display' => [
                [
                    'theme_key' => 'room_grid_frames',
                    'theme_name' => 'Grid with Frames',
                    'description' => 'Room grid with customizable frames for each rank',
                    'is_default' => true,
                    'assets' => [
                        ['asset_key' => 'frame_rank_1', 'asset_label' => 'Rank 1 Frame (TOP1)', 'asset_type' => 'svga', 'is_required' => false],
                        ['asset_key' => 'frame_rank_2', 'asset_label' => 'Rank 2 Frame (TOP2)', 'asset_type' => 'svga', 'is_required' => false],
                        ['asset_key' => 'frame_rank_3', 'asset_label' => 'Rank 3 Frame', 'asset_type' => 'image', 'is_required' => false],
                        ['asset_key' => 'frame_rank_4', 'asset_label' => 'Rank 4 Frame', 'asset_type' => 'image', 'is_required' => false],
                        ['asset_key' => 'frame_default', 'asset_label' => 'Default Frame', 'asset_type' => 'image', 'is_required' => false],
                        ['asset_key' => 'live_indicator', 'asset_label' => 'Live Indicator', 'asset_type' => 'svga', 'is_required' => false],
                        ['asset_key' => 'rank_badge_1', 'asset_label' => 'Rank 1 Badge', 'asset_type' => 'svga', 'is_required' => false],
                        ['asset_key' => 'rank_badge_2', 'asset_label' => 'Rank 2 Badge', 'asset_type' => 'image', 'is_required' => false],
                        ['asset_key' => 'rank_badge_3', 'asset_label' => 'Rank 3 Badge', 'asset_type' => 'image', 'is_required' => false],
                    ],
                ],
                [
                    'theme_key' => 'room_grid_simple',
                    'theme_name' => 'Simple Grid',
                    'description' => 'Simple room grid without frames',
                    'is_default' => false,
                    'assets' => [
                        ['asset_key' => 'live_indicator', 'asset_label' => 'Live Indicator', 'asset_type' => 'image', 'is_required' => false],
                    ],
                ],
                [
                    'theme_key' => 'room_list_compact',
                    'theme_name' => 'Compact List',
                    'description' => 'Compact list view for rooms',
                    'is_default' => false,
                    'assets' => [
                        ['asset_key' => 'live_indicator', 'asset_label' => 'Live Indicator', 'asset_type' => 'image', 'is_required' => false],
                        ['asset_key' => 'separator', 'asset_label' => 'List Separator', 'asset_type' => 'image', 'is_required' => false],
                    ],
                ],
            ],

            // Ranking Themes
            'ranking_display' => [
                [
                    'theme_key' => 'ranking_cards_scroll',
                    'theme_name' => 'Scrollable Cards',
                    'description' => 'Horizontal scrollable ranking cards',
                    'is_default' => true,
                    'assets' => [
                        ['asset_key' => 'card_background', 'asset_label' => 'Card Background', 'asset_type' => 'image', 'is_required' => false],
                        ['asset_key' => 'crown_1st', 'asset_label' => '1st Place Crown', 'asset_type' => 'svga', 'is_required' => false],
                        ['asset_key' => 'crown_2nd', 'asset_label' => '2nd Place Crown', 'asset_type' => 'image', 'is_required' => false],
                        ['asset_key' => 'crown_3rd', 'asset_label' => '3rd Place Crown', 'asset_type' => 'image', 'is_required' => false],
                    ],
                ],
                [
                    'theme_key' => 'ranking_tabs',
                    'theme_name' => 'Tabbed Ranking',
                    'description' => 'Ranking with tabs for different categories',
                    'is_default' => false,
                    'assets' => [
                        ['asset_key' => 'tab_background', 'asset_label' => 'Tab Background', 'asset_type' => 'image', 'is_required' => false],
                        ['asset_key' => 'crown_1st', 'asset_label' => '1st Place Crown', 'asset_type' => 'svga', 'is_required' => false],
                    ],
                ],
            ],

            // Categories Themes
            'room_categories' => [
                [
                    'theme_key' => 'categories_pills',
                    'theme_name' => 'Pill Style',
                    'description' => 'Pill-shaped category buttons',
                    'is_default' => true,
                    'assets' => [
                        ['asset_key' => 'active_background', 'asset_label' => 'Active Background', 'asset_type' => 'image', 'is_required' => false],
                    ],
                ],
                [
                    'theme_key' => 'categories_underline',
                    'theme_name' => 'Underline Style',
                    'description' => 'Categories with underline indicator',
                    'is_default' => false,
                    'assets' => [
                        ['asset_key' => 'active_indicator', 'asset_label' => 'Active Indicator', 'asset_type' => 'svga', 'is_required' => false],
                    ],
                ],
            ],

            // Country Filter Themes
            'country_flags' => [
                [
                    'theme_key' => 'country_flags_horizontal',
                    'theme_name' => 'Horizontal Flags',
                    'description' => 'Horizontal scrollable country flags',
                    'is_default' => true,
                    'assets' => [
                        ['asset_key' => 'selected_border', 'asset_label' => 'Selected Border', 'asset_type' => 'image', 'is_required' => false],
                    ],
                ],
            ],

            // Floating Button Themes
            'floating_action' => [
                [
                    'theme_key' => 'floating_animated_circle',
                    'theme_name' => 'Animated Circle',
                    'description' => 'Circular floating button with animation',
                    'is_default' => true,
                    'assets' => [
                        ['asset_key' => 'icon', 'asset_label' => 'Button Icon', 'asset_type' => 'svga', 'is_required' => true],
                        ['asset_key' => 'background', 'asset_label' => 'Button Background', 'asset_type' => 'vap', 'is_required' => false],
                    ],
                ],
                [
                    'theme_key' => 'floating_webview_trigger',
                    'theme_name' => 'WebView Trigger',
                    'description' => 'Floating button that opens a WebView',
                    'is_default' => false,
                    'assets' => [
                        ['asset_key' => 'icon', 'asset_label' => 'Button Icon', 'asset_type' => 'alpha', 'is_required' => true],
                    ],
                ],
            ],
        ];

        foreach ($themes as $widgetKey => $widgetThemes) {
            $widget = Widget::where('widget_key', $widgetKey)->first();

            if (!$widget) {
                continue;
            }

            foreach ($widgetThemes as $themeData) {
                $assets = $themeData['assets'] ?? [];
                unset($themeData['assets']);

                $themeData['widget_id'] = $widget->id;
                $theme = WidgetTheme::create($themeData);

                // Create theme assets
                $order = 1;
                foreach ($assets as $asset) {
                    $asset['theme_id'] = $theme->id;
                    $asset['order'] = $order++;
                    ThemeAsset::create($asset);
                }
            }
        }
    }
}
