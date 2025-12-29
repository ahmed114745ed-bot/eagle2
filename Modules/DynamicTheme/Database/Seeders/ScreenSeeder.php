<?php

namespace Modules\DynamicTheme\Database\Seeders;

use Modules\DynamicTheme\Entities\Screen;
use Modules\DynamicTheme\Entities\Widget;
use Modules\DynamicTheme\Entities\WidgetTheme;
use Modules\DynamicTheme\Entities\ScreenWidget;
use Modules\DynamicTheme\Entities\ScreenWidgetChild;
use Illuminate\Database\Seeder;

class ScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Home Hot Screen
        $homeHotScreen = Screen::create([
            'screen_key' => 'home_hot',
            'screen_name' => 'Home - Hot',
            'min_app_version' => '2.0.0',
            'layout' => [
                'direction' => 'vertical',
                'background_color' => '#FFFFFF',
                'background_asset' => null,
            ],
            'is_active' => true,
        ]);

        // Create Home Egypt Screen
        $homeEgyptScreen = Screen::create([
            'screen_key' => 'home_egypt',
            'screen_name' => 'Home - Egypt',
            'min_app_version' => '2.0.0',
            'layout' => [
                'direction' => 'vertical',
                'background_color' => '#FFFFFF',
                'background_asset' => null,
            ],
            'is_active' => true,
        ]);

        // Create Home Following Screen
        $homeFollowingScreen = Screen::create([
            'screen_key' => 'home_following',
            'screen_name' => 'Home - Following',
            'min_app_version' => '2.0.0',
            'layout' => [
                'direction' => 'vertical',
                'background_color' => '#FFFFFF',
                'background_asset' => null,
            ],
            'is_active' => true,
        ]);

        // Add widgets to Home Hot Screen
        $this->addWidgetsToHomeScreen($homeHotScreen);
    }

    private function addWidgetsToHomeScreen(Screen $screen): void
    {
        $order = 1;

        // 1. Tab Bar Widget
        $tabBarWidget = Widget::where('widget_key', 'main_navigation')->first();
        $tabBarTheme = WidgetTheme::where('theme_key', 'tab_bar_golden')->first();

        if ($tabBarWidget && $tabBarTheme) {
            $screenWidget = ScreenWidget::create([
                'screen_id' => $screen->id,
                'widget_id' => $tabBarWidget->id,
                'theme_id' => $tabBarTheme->id,
                'order' => $order++,
                'is_positioned' => false,
                'primary_settings' => [
                    'height_percentage' => 8,
                    'is_sticky' => true,
                ],
                'secondary_settings' => [
                    'assets' => [],
                ],
                'min_app_version' => '2.0.0',
                'is_active' => true,
            ]);

            // Add Tab Children
            $tabChildren = [
                ['child_key' => 'hot', 'label' => 'Hot', 'is_active' => true, 'screen_key' => 'home_hot'],
                ['child_key' => 'egypt', 'label' => 'مصر', 'is_active' => false, 'screen_key' => 'home_egypt'],
                ['child_key' => 'following', 'label' => 'Following', 'is_active' => false, 'screen_key' => 'home_following'],
            ];

            $childOrder = 1;
            foreach ($tabChildren as $child) {
                ScreenWidgetChild::create([
                    'screen_widget_id' => $screenWidget->id,
                    'child_key' => $child['child_key'],
                    'child_type' => 'tab',
                    'label' => $child['label'],
                    'order' => $childOrder++,
                    'is_visible' => true,
                    'is_active' => $child['is_active'],
                    'action' => [
                        'type' => 'screen',
                        'screen_key' => $child['screen_key'],
                    ],
                    'assets' => null,
                ]);
            }

            // Add Special Children (Search, Join Room)
            ScreenWidgetChild::create([
                'screen_widget_id' => $screenWidget->id,
                'child_key' => 'search',
                'child_type' => 'special',
                'label' => 'Search',
                'order' => 100,
                'is_visible' => true,
                'is_active' => false,
                'action' => [
                    'type' => 'internal',
                    'action_key' => 'open_search',
                ],
                'assets' => null,
                'position' => 'right',
            ]);

            ScreenWidgetChild::create([
                'screen_widget_id' => $screenWidget->id,
                'child_key' => 'join_room',
                'child_type' => 'special',
                'label' => 'Join Room',
                'order' => 101,
                'is_visible' => true,
                'is_active' => false,
                'action' => [
                    'type' => 'internal',
                    'action_key' => 'open_join_room_dialog',
                ],
                'assets' => null,
                'position' => 'right',
            ]);
        }

        // 2. Banner Widget
        $bannerWidget = Widget::where('widget_key', 'banner_slider')->first();
        $bannerTheme = WidgetTheme::where('theme_key', 'banner_slider_auto')->first();

        if ($bannerWidget && $bannerTheme) {
            ScreenWidget::create([
                'screen_id' => $screen->id,
                'widget_id' => $bannerWidget->id,
                'theme_id' => $bannerTheme->id,
                'order' => $order++,
                'is_positioned' => false,
                'primary_settings' => [
                    'banner_type' => 'live_banners',
                    'height_percentage' => 18,
                    'auto_scroll' => true,
                    'scroll_interval_seconds' => 5,
                ],
                'secondary_settings' => [
                    'assets' => [],
                    'border_radius' => 12,
                    'show_indicators' => true,
                    'indicator_style' => 'dots',
                ],
                'min_app_version' => '2.0.0',
                'is_active' => true,
            ]);
        }

        // 3. Categories Widget
        $categoriesWidget = Widget::where('widget_key', 'room_categories')->first();
        $categoriesTheme = WidgetTheme::where('theme_key', 'categories_pills')->first();

        if ($categoriesWidget && $categoriesTheme) {
            $screenWidget = ScreenWidget::create([
                'screen_id' => $screen->id,
                'widget_id' => $categoriesWidget->id,
                'theme_id' => $categoriesTheme->id,
                'order' => $order++,
                'is_positioned' => false,
                'primary_settings' => [
                    'height_percentage' => 5,
                ],
                'secondary_settings' => [
                    'assets' => [],
                    'scroll_direction' => 'horizontal',
                ],
                'min_app_version' => '2.0.0',
                'is_active' => true,
            ]);

            // Add Category Children
            $categories = [
                ['child_key' => 'trending', 'label' => 'رائج', 'filter_value' => 'trending', 'is_active' => true],
                ['child_key' => 'party', 'label' => 'حفلة', 'filter_value' => 'party', 'is_active' => false],
                ['child_key' => 'video_room', 'label' => 'غرفة الفيديو', 'filter_value' => 'video', 'is_active' => false],
                ['child_key' => 'live_room', 'label' => 'غرف اللايف', 'filter_value' => 'live', 'is_active' => false],
            ];

            $childOrder = 1;
            foreach ($categories as $category) {
                ScreenWidgetChild::create([
                    'screen_widget_id' => $screenWidget->id,
                    'child_key' => $category['child_key'],
                    'child_type' => 'category',
                    'label' => $category['label'],
                    'order' => $childOrder++,
                    'is_visible' => true,
                    'is_active' => $category['is_active'],
                    'action' => [
                        'type' => 'filter',
                        'filter_key' => 'room_type',
                        'filter_value' => $category['filter_value'],
                    ],
                    'assets' => null,
                ]);
            }
        }

        // 4. Ranking Widget
        $rankingWidget = Widget::where('widget_key', 'ranking_display')->first();
        $rankingTheme = WidgetTheme::where('theme_key', 'ranking_cards_scroll')->first();

        if ($rankingWidget && $rankingTheme) {
            $screenWidget = ScreenWidget::create([
                'screen_id' => $screen->id,
                'widget_id' => $rankingWidget->id,
                'theme_id' => $rankingTheme->id,
                'order' => $order++,
                'is_positioned' => false,
                'primary_settings' => [
                    'height_percentage' => 12,
                ],
                'secondary_settings' => [
                    'assets' => [],
                    'show_top_count' => 3,
                ],
                'min_app_version' => '2.0.0',
                'is_active' => true,
            ]);

            // Add Ranking Children
            $rankings = [
                ['child_key' => 'star', 'label' => 'Star', 'screen_key' => 'ranking_star_detail'],
                ['child_key' => 'wealth_star', 'label' => 'Wealth Star', 'screen_key' => 'ranking_wealth_detail'],
                ['child_key' => 'room_star', 'label' => 'Room Star', 'screen_key' => 'ranking_room_detail'],
            ];

            $childOrder = 1;
            foreach ($rankings as $ranking) {
                ScreenWidgetChild::create([
                    'screen_widget_id' => $screenWidget->id,
                    'child_key' => $ranking['child_key'],
                    'child_type' => 'category',
                    'label' => $ranking['label'],
                    'order' => $childOrder++,
                    'is_visible' => true,
                    'is_active' => false,
                    'action' => [
                        'type' => 'screen',
                        'screen_key' => $ranking['screen_key'],
                    ],
                    'assets' => null,
                ]);
            }
        }

        // 5. Top Rooms Widget (1-4)
        $roomWidget = Widget::where('widget_key', 'room_display')->first();
        $roomTheme = WidgetTheme::where('theme_key', 'room_grid_frames')->first();

        if ($roomWidget && $roomTheme) {
            ScreenWidget::create([
                'screen_id' => $screen->id,
                'widget_id' => $roomWidget->id,
                'theme_id' => $roomTheme->id,
                'order' => $order++,
                'is_positioned' => false,
                'primary_settings' => [
                    'from_rank' => 1,
                    'to_rank' => 4,
                    'layout' => 'grid',
                    'columns' => 2,
                    'height_percentage' => 45,
                    'expand_remaining' => false,
                ],
                'secondary_settings' => [
                    'assets' => [],
                    'show_room_name' => true,
                    'show_host_name' => true,
                    'show_viewer_count' => true,
                    'show_country_flag' => true,
                    'room_name_max_lines' => 1,
                ],
                'min_app_version' => '2.0.0',
                'is_active' => true,
            ]);
        }

        // 6. Country Filter Widget
        $countryWidget = Widget::where('widget_key', 'country_flags')->first();
        $countryTheme = WidgetTheme::where('theme_key', 'country_flags_horizontal')->first();

        if ($countryWidget && $countryTheme) {
            ScreenWidget::create([
                'screen_id' => $screen->id,
                'widget_id' => $countryWidget->id,
                'theme_id' => $countryTheme->id,
                'order' => $order++,
                'is_positioned' => false,
                'primary_settings' => [
                    'height_percentage' => 5,
                ],
                'secondary_settings' => [
                    'assets' => [],
                    'flag_size' => 32,
                    'show_country_name' => false,
                ],
                'min_app_version' => '2.0.0',
                'is_active' => true,
            ]);
        }

        // 7. Remaining Rooms Widget (5+)
        $roomSimpleTheme = WidgetTheme::where('theme_key', 'room_grid_simple')->first();

        if ($roomWidget && $roomSimpleTheme) {
            ScreenWidget::create([
                'screen_id' => $screen->id,
                'widget_id' => $roomWidget->id,
                'theme_id' => $roomSimpleTheme->id,
                'order' => $order++,
                'is_positioned' => false,
                'primary_settings' => [
                    'from_rank' => 5,
                    'to_rank' => -1,
                    'layout' => 'grid',
                    'columns' => 2,
                    'height_percentage' => null,
                    'expand_remaining' => true,
                ],
                'secondary_settings' => [
                    'assets' => [],
                    'show_room_name' => true,
                    'show_host_name' => false,
                    'show_viewer_count' => true,
                    'show_country_flag' => true,
                ],
                'min_app_version' => '2.0.0',
                'is_active' => true,
            ]);
        }

        // 8. Floating Games Button (Positioned)
        $floatingWidget = Widget::where('widget_key', 'floating_action')->first();
        $floatingTheme = WidgetTheme::where('theme_key', 'floating_animated_circle')->first();

        if ($floatingWidget && $floatingTheme) {
            ScreenWidget::create([
                'screen_id' => $screen->id,
                'widget_id' => $floatingWidget->id,
                'theme_id' => $floatingTheme->id,
                'order' => 100,
                'is_positioned' => true,
                'position' => [
                    'anchor' => 'bottom_right',
                    'bottom_percentage' => 15,
                    'right_percentage' => 3,
                ],
                'primary_settings' => [
                    'size_percentage' => 12,
                ],
                'secondary_settings' => [
                    'assets' => [],
                ],
                'action' => [
                    'type' => 'screen',
                    'screen_key' => 'games_screen',
                ],
                'min_app_version' => '2.3.0',
                'is_active' => true,
            ]);
        }

        // 9. Floating Event Button (Positioned with WebView)
        $floatingWebviewTheme = WidgetTheme::where('theme_key', 'floating_webview_trigger')->first();

        if ($floatingWidget && $floatingWebviewTheme) {
            ScreenWidget::create([
                'screen_id' => $screen->id,
                'widget_id' => $floatingWidget->id,
                'theme_id' => $floatingWebviewTheme->id,
                'order' => 101,
                'is_positioned' => true,
                'position' => [
                    'anchor' => 'bottom_right',
                    'bottom_percentage' => 28,
                    'right_percentage' => 3,
                ],
                'primary_settings' => [
                    'size_percentage' => 10,
                ],
                'secondary_settings' => [
                    'assets' => [],
                ],
                'action' => [
                    'type' => 'webview',
                    'url' => 'https://events.app.com/ramadan',
                    'title' => 'Ramadan Event',
                ],
                'min_app_version' => '2.5.0',
                'is_active' => true,
            ]);
        }
    }
}
