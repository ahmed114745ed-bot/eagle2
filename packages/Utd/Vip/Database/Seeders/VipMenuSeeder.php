<?php

namespace Utd\Vip\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class VipMenuSeeder extends Seeder
{
    public function run(): void
    {
        $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;

        $vipParent = Menu::firstOrCreate(
            [
                'title' => 'Vip',
                'parent_id' => 0,
            ],
            [
                'order' => $parentOrder + 1,
                'icon' => '👑',
            ]
        );

        $this->createChildMenu(
            $vipParent->id,
            'VIPs',
            'ovip',
            'browse-VIPs',
            '💎'
        );

        $this->createChildMenu(
            $vipParent->id,
            'VIP Privileges',
            'vip_privilege',
            'browse-vip-privilege',
            '🎖️'
        );

        $this->createChildMenu(
            $vipParent->id,
            'Vip Settings',
            'soon',
            'browse-VIPs',
            '⚙️'
        );

        $fastOrdersParent = Menu::where('title', 'Fast orders')->where('parent_id', 0)->first();
        if ($fastOrdersParent) {
            $this->createChildMenu(
                $fastOrdersParent->id,
                'Gift VIP',
                '/vips_dedicate',
                'browse-gift-VIP',
                '👑'
            );
        }
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
