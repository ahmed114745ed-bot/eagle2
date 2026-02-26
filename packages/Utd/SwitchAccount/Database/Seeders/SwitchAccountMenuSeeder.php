<?php

namespace Utd\SwitchAccount\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class SwitchAccountMenuSeeder extends Seeder
{
    public function run(): void
    {
        $parent = Menu::where('title', 'users')->where('parent_id', 0)->first();
        $parentId = $parent ? $parent->id : 0;

        $this->createChildMenu(
            $parentId,
            'User Devices Histories',
            '/user-devices-histories',
            'browse-users-devices',
            '📱'
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
