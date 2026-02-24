<?php

namespace Utd\Events\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class EventsMenuSeeder extends Seeder
{
    public function run(): void
    {
        $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;

        $eventsParent = Menu::firstOrCreate(
            [
                'title' => 'events',
                'parent_id' => 0,
            ],
            [
                'order' => $parentOrder + 1,
                'icon' => '🎉',
            ]
        );

        $this->createChildMenu(
            $eventsParent->id,
            'Charging Events',
            '/target-events',
            'browse-target-event',
            '⚡'
        );

        $this->createChildMenu(
            $eventsParent->id,
            'Weekly Star Events',
            '/weekly-events-new',
            'browse-weekly-star',
            '⭐'
        );

        $this->createChildMenu(
            $eventsParent->id,
            'pk event',
            '/pk-events',
            'browse-pk-event',
            '⚔️'
        );

        $this->createChildMenu(
            $eventsParent->id,
            'event period',
            'event-period',
            'browse-event-period',
            '📅'
        );

        $this->createChildMenu(
            $eventsParent->id,
            'Events reports',
            '/event-reports',
            'browse-event_report',
            '📊'
        );

        $this->createChildMenu(
            $eventsParent->id,
            'General rules',
            '/general-rols',
            'browse-general-roles',
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
