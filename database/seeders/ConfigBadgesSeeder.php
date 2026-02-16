<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Config as ConfigModel;
use Modules\Badge\Entities\Badge;

class ConfigBadgesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            'host',
            'agency_owner',
            'shipping',
            'bd',
        ];

        $lang = ['en', 'ar', 'tr', 'hi', 'id'];
        $configKeys = [];

        foreach ($types as $type) {
            $badge =  Badge::create([
                'type' => 'top',
                'name' => $type
            ]);
            foreach ($lang as $l) {
                $configKeys = "{$l}_{$type}";
                $configs = ConfigModel::where('name', $configKeys)->value('value');
                if ($configs) {
                    $badge->images()->create([
                        'language' => $l,
                        'image' => $configs,
                        'show_image' => $configs,
                        'image_type' => 'image',
                    ]);
                }
            }
        }
    }
}
