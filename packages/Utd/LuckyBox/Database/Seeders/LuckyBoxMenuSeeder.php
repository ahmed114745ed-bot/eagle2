<?php

namespace Utd\LuckyBox\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LuckyBoxMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if parent menu already exists
        $existingParent = DB::table('admin_menu')->where('title', 'lucky box')->first();

        if ($existingParent) {
            $parentId = $existingParent->id;
        } else {
            // Create parent menu item
            $parentId = DB::table('admin_menu')->insertGetId([
                'parent_id' => 0,
                'order' => 130,
                'title' => 'lucky box',
                'icon' => '🍀',
                'uri' => '',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create child menu items
        $menuItems = [
            ['title' => 'Dumped boxes', 'icon' => '🗑️', 'uri' => 'thrown-boxes', 'permission' => 'browse-box-use', 'order' => 131],
            ['title' => 'All Boxes', 'icon' => '📦', 'uri' => 'lucky-boxes', 'permission' => 'browse-boxes', 'order' => 132],
            ['title' => 'lucky box setting', 'icon' => '⚙️', 'uri' => 'lucky-box-settings', 'permission' => 'browse-box-settings', 'order' => 133],
        ];

        foreach ($menuItems as $item) {
            // Check if menu item already exists
            $exists = DB::table('admin_menu')
                ->where('uri', $item['uri'])
                ->where('parent_id', $parentId)
                ->exists();

            if (!$exists) {
                DB::table('admin_menu')->insert([
                    'parent_id' => $parentId,
                    'order' => $item['order'],
                    'title' => $item['title'],
                    'icon' => $item['icon'],
                    'uri' => $item['uri'],
                    'permission' => $item['permission'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
