<?php

namespace Utd\Moments\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class MenuMomentSeeder extends Seeder
{
    public function run(): void
    {
        $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;

        $momentParent = Menu::firstOrCreate(
            [
                'title' => 'Moment',
                'parent_id' => 0,
            ],
            [
                'order' => $parentOrder + 1,
                'icon' => '📸',
            ]
        );

        $this->createChildMenu(
            $momentParent->id,
            'Moment',
            '/moment-viewer',
            'browse-Moment',
            '🕐'
        );

        $this->createChildMenu(
            $momentParent->id,
            'Moment reports',
            '/report-moments',
            'browse-report-moment',
            '📈'
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
