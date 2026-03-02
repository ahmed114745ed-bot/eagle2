<?php

namespace Utd\Milestones\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class MilestonesMenuSeeder extends Seeder
{
    public function run(): void
    {
        $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;

        $milestonesParent = Menu::firstOrCreate(
            [
                'title' => 'milestones',
                'parent_id' => 0,
            ],
            [
                'order' => $parentOrder + 1,
                'icon' => '🏁',
            ]
        );

        $this->createChildMenu(
            $milestonesParent->id,
            'milestones',
            '/milestones',
            'browse-milestone',
            '📍'
        );

        $this->createChildMenu(
            $milestonesParent->id,
            'user-history-rewards',
            '/user-history-rewards',
            'browse-user-reward',
            '🎁'
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
