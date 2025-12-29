<?php

namespace Modules\DynamicTheme\Database\Seeders;

use Modules\DynamicTheme\Entities\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            WidgetSeeder::class,
            WidgetThemeSeeder::class,
            ScreenSeeder::class,
            ClientConfigurationsSeeder::class,
        ]);
    }
}
