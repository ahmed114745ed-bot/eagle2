<?php

namespace Utd\RoomCup\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class RoomCupMenuSeeder extends Seeder
{
    public function run(): void
    {
        $currentParent = Menu::where('title', 'room-cup-target')
            ->whereNull('uri')
            ->first();

        if ($currentParent) {
            $existingParent = Menu::whereId($currentParent->parent_id)->exists();
            if ($currentParent->parent_id != 0 && !$existingParent) {
                $currentParent->update(['parent_id' => 0]);
            }
            $roomCupParent = $currentParent;
        } else {
            $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;
            $roomCupParent = Menu::create([
                'title'     => 'room-cup-target',
                'parent_id' => 0,
                'order'     => $parentOrder + 1,
                'icon'      => '🏆',
                'uri'       => null,
            ]);
        }

        $this->createChildMenu(
            $roomCupParent->id,
            'room-cup-target',
            'room-cup-target',
            null,
            '🎯'
        );

        $this->createChildMenu(
            $roomCupParent->id,
            'room-cup-settings',
            'room-cup-settings',
            null,
            '⚙️'
        );

        $this->createChildMenu(
            $roomCupParent->id,
            'room-cup-reports',
            'room-cup-reports',
            null,
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
