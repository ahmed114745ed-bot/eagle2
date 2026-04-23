<?php

namespace Utd\UsersWallet\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class WalletMenuSeeder extends Seeder
{
    public function run(): void
    {
        $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;

        $walletParent = Menu::firstOrCreate(
            [
                'title' => 'Wallet',
                'parent_id' => 0,
            ],
            [
                'order' => $parentOrder + 1,
                'icon' => '💰',
            ]
        );

        $this->createChildMenu(
            $walletParent->id,
            'e_wallet',
            'wallet-templates',
            'browse-wallet-template',
            '👛'
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
