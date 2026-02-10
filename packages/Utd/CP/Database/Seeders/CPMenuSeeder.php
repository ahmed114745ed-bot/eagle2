<?php

namespace Utd\CP\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class CPMenuSeeder extends Seeder
{
    public function run(): void
    {
        $cpParent = Menu::where('title', 'CP')
            ->where('parent_id', 0)
            ->whereNull('uri')
            ->first();

        if (!$cpParent) {
            $parentOrder = Menu::where('parent_id', 0)->max('order') ?? 0;
            $cpParent = Menu::create([
                'title'     => 'CP',
                'parent_id' => 0,
                'order'     => $parentOrder + 1,
                'icon'      => '🧑‍❤️‍👩',
                'uri'       => null,
            ]);
        }

        $this->createChildMenu($cpParent->id, 'cp relations', 'cp-relations', 'browse-cp-relation', '🔗');
        $this->createChildMenu($cpParent->id, 'weekly cp', 'weekly-cp', 'browse-weekly-cp', '📅');
        $this->createChildMenu($cpParent->id, 'cp reports', 'cp-reports', 'browse-cp-report', '📈');
        $this->createChildMenu($cpParent->id, 'cp-settings', 'cp-settings', null, '⚙️');
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
                'title'     => $title,
                'parent_id' => $parentId,
            ],
            [
                'order'      => $order + 1,
                'uri'        => $uri,
                'permission' => $permission,
                'icon'       => $icon,
            ]
        );
    }
}
