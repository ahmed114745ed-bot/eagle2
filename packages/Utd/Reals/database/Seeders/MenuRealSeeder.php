<?php

namespace Utd\Reals\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class MenuRealSeeder extends Seeder
{
    public function run(): void
    {
        $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;

        $reelParent = Menu::firstOrCreate(
            [
                'title' => 'reels',
                'parent_id' => 0,
            ],
            [
                'order' => $parentOrder + 1,
                'icon'  => '🎬',
            ]
        );

        $this->createChildMenu(
            $reelParent->id,
            'reels',
            '/reels',
            'browse-Real',
            '📹'
        );

        $this->createChildMenu(
            $reelParent->id,
            'Reels Reports',
            'report-reels',
            'browse-report-real',
            '📊'
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
