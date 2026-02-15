<?php

namespace Utd\Chat\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class ChatMenuSeeder extends Seeder
{
    public function run(): void
    {
        $currentParent = Menu::where('title', 'chat')
            ->where('parent_id', 0)
            ->first();

        if ($currentParent) {
            $chatParent = $currentParent;
        } else {
            $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;
            $chatParent = Menu::create([
                'title' => 'chat',
                'parent_id' => 0,
                'order' => $parentOrder + 1,
                'icon' => '💬',
                'uri' => 'group-chat',
                'permission' => 'browse-group-chat',
            ]);
        }

        $this->createChildMenu(
            $chatParent->id,
            'group Chat',
            'group-chat',
            'browse-group-chat',
            '💭'
        );

        $this->createChildMenu(
            $chatParent->id,
            'Settings',
            'setting-group-char',
            'browse-updates_group_chat',
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
