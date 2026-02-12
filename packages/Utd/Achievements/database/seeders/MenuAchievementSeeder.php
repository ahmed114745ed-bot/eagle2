<?php

namespace Utd\Achievements\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class MenuAchievementSeeder extends Seeder
{
    public function run(): void
    {
        $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;

        $achievementParent = Menu::firstOrCreate(
            [
                'title' => 'Achievements',
                'parent_id' => 0,
            ],
            [
                'order' => $parentOrder + 1,
                'icon' => '🏅',
            ]
        );

        $this->createChildMenu(
            $achievementParent->id,
            'achievements',
            '/achievements',
            'browse-achievement',
            '⭐'
        );

        $this->createChildMenu(
            $achievementParent->id,
            'Achievement Reports',
            '/user-achievement-levels',
            'browse-user_achievement_level',
            '📄'
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
