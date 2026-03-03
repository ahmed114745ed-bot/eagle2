<?php

namespace Utd\Badge\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class BadgeMenuSeeder extends Seeder
{
    public function run(): void
    {
        $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;

        $badgesParent = Menu::firstOrCreate(
            [
                'title' => 'badges',
                'parent_id' => 0,
            ],
            [
                'order' => $parentOrder + 1,
                'icon' => '🏅',
            ]
        );

        $this->createChildMenu(
            $badgesParent->id,
            'badges',
            '/badges',
            'browse-badges',
            '🎖️'
        );

        $this->createChildMenu(
            $badgesParent->id,
            'dedicate badges',
            '/dedicate-badges',
            'browse-dedicate-badges',
            '⭐'
        );
    }

    protected function createChildMenu(
        int $parentId,
        string $title,
        string $uri,
        ?string $permission = null,
        ?string $icon = null
    ): void {
        $order = Menu::where('parent_id', $parentId)->max('order') ?? 0;

        Menu::firstOrCreate(
            [
                'title' => $title,
                'parent_id' => $parentId,
            ],
            [
                'order' => $order + 1,
                'uri' => $uri,
                'permission' => $permission,
                'icon' => $icon,
            ]
        );
    }
}
