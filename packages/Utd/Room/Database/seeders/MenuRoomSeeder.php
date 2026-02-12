<?php

namespace Utd\Room\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class MenuRoomSeeder extends Seeder
{
    public function run(): void
    {
        $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;

        $roomParent = Menu::firstOrCreate(
            [
                'title' => 'Room',
                'parent_id' => 0,
            ],
            [
                'order' => $parentOrder + 1,
                'icon' => '🏠',
            ]
        );

        $this->createChildMenu(
            $roomParent->id,
            'Live Rooms',
            'live-rooms',
            null,
            '📺'
        );

        $this->createChildMenu(
            $roomParent->id,
            'Rooms',
            'rooms',
            'browse-rooms',
            '🚪'
        );

        $this->createChildMenu(
            $roomParent->id,
            'Room categories',
            'categories',
            'browse-categories',
            '📂'
        );

        $this->createChildMenu(
            $roomParent->id,
            'room vips',
            'room-vips',
            'browse-room-vip',
            '👑'
        );

        $this->createChildMenu(
            $roomParent->id,
            'Backgrounds',
            'backgrounds',
            'browse-room-background',
            '🖼️'
        );

        $this->createChildMenu(
            $roomParent->id,
            'Room Settings',
            'room-settings',
            'browse-room-settings',
            '⚙️'
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
