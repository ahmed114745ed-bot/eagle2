<?php

namespace Utd\Pk\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class PkMenuSeeder extends Seeder
{
    public function run(): void
    {
        $eventsParent = Menu::where('title', 'events')
            ->whereNull('uri')
            ->first();

        if (!$eventsParent) {
            $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;
            $eventsParent = Menu::create([
                'title'     => 'events',
                'parent_id' => 0,
                'order'     => $parentOrder + 1,
                'icon'      => '📅',
                'uri'       => null,
            ]);
        }

        $this->createChildMenu(
            $eventsParent->id,
            'pk event',
            '/pk-events',
            'browse-pk-event',
            '⚔️'
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
