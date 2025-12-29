<?php

namespace Modules\DynamicTheme\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\DynamicTheme\Entities\ClientConfiguration;
use Modules\DynamicTheme\Entities\ConfigScreenOverride;
use Modules\DynamicTheme\Entities\ConfigWidgetOverride;
use Modules\DynamicTheme\Entities\ConfigWidgetChildOverride;
use Modules\DynamicTheme\Entities\ConfigThemeChildOverride;
use Modules\DynamicTheme\Entities\ConfigThemeAssetOverride;

use Illuminate\Support\Str;
use Carbon\Carbon;

class ClientConfigurationsSeeder extends Seeder
{
    public function run(): void
    {

 
        $config1 = ClientConfiguration::create([
            'client_id' => 'default',
            'name' => 'Configuration 1',
            'description' => 'Default layout 1',
            'is_active' => true, 
        ]);

        ConfigScreenOverride::create([
            'configuration_id' => $config1->id,
            'screen_id' => 1,
            'is_visible' => true,
            'display_order' => 0,
        ]);

        ConfigWidgetOverride::create([
            'configuration_id' => $config1->id,
            'screen_widget_id' => 1,
            'widget_id' => 1,
            'screen_id' => 1,
            'is_visible' => true,
            'display_order' => 0,
            'selected_theme_id' => null,
        ]);

        ConfigThemeChildOverride::create([
                'configuration_id' => $config1->id,
                'theme_child_id' => 1,
                'is_visible' => true,
                'order' => 0,
                'action' => null,
                'position' => null,
            ]);

            ConfigThemeAssetOverride::create([
                'configuration_id' => $config1->id,
                'theme_asset_id' => 1,
                'override_url' => null,
                'file_path' => null,
                'original_filename' => null,
            ]);

        $config2 = ClientConfiguration::create([
            'client_id' => 'default',
            'name' => 'Configuration 2',
            'description' => 'Alternate layout 2',
            'is_active' => false,
        ]);

        ConfigScreenOverride::create([
            'configuration_id' => $config2->id,
            'screen_id' => 1,
            'is_visible' => false, 
            'display_order' => 1,
        ]);

        ConfigWidgetOverride::create([
            'configuration_id' => $config2->id,
            'screen_widget_id' => 1,
            'widget_id' => 1,
            'screen_id' => 1,
            'is_visible' => false,
            'display_order' => 1,
            'selected_theme_id' => null,
        ]);

 

        ConfigThemeChildOverride::create([
            'configuration_id' => $config2->id,
            'theme_child_id' => 1,
            'is_visible' => false,
            'order' => 1,
            'action' => null,
            'position' => null,
        ]);

        ConfigThemeAssetOverride::create([
            'configuration_id' => $config2->id,
            'theme_asset_id' => 1,
            'override_url' => '/assets/custom/logo2.png',
            'file_path' => '/storage/assets/custom/logo2.png',
            'original_filename' => 'logo2.png',
        ]);
    }
}
