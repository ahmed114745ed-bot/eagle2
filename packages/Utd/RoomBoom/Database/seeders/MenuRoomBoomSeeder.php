<?php

namespace Utd\RoomBoom\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class MenuRoomBoomSeeder extends Seeder
{
    public function run(): void
    {
        $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;

        $roomBoomParent = Menu::firstOrCreate(
            [
                'title'     => 'Room Boom',
                'parent_id' => 0,
            ],
            [
                'order' => $parentOrder + 1,
                'icon'  => '💣',
            ]
        );

        $this->createChildMenu(
            $roomBoomParent->id,
            'Room Boom Levels',
            'room-boom-levels',
            'browse-room-boom-levels',
            '💥'
        );

        $this->createChildMenu(
            $roomBoomParent->id,
            'Room Boom Winners',
            'room-boom-winners',
            'browse-room-boom-winners',
            '🏆'
        );

        $this->createChildMenu(
            $roomBoomParent->id,
            'Rules',
            'super-boom-rules',
            'browse-super-boom-rules',
            '📋'
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
