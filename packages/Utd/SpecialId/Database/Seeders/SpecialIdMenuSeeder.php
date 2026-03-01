<?php

namespace Utd\SpecialId\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class SpecialIdMenuSeeder extends Seeder
{
    public function run(): void
    {
        $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;

        $specialIdParent = Menu::firstOrCreate(
            [
                'title' => 'Distinguished identifier',
                'parent_id' => 0,
            ],
            [
                'order' => $parentOrder + 1,
                'icon' => '🏷️',
            ]
        );

        $this->createChildMenu(
            $specialIdParent->id,
            'Featured ids',
            'special-wares',
            'browse-featured-ids',
            '⭐'
        );

        $this->createChildMenu(
            $specialIdParent->id,
            'History of unique identifiers',
            'special-histories',
            'browse-details-of-unique-identifiers',
            '📜'
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
