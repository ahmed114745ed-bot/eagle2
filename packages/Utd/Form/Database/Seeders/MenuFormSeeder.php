<?php

namespace Utd\Form\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class MenuFormSeeder extends Seeder
{
    public function run(): void
    {
        $agencySystem = Menu::where('title', 'Agency System')->where('parent_id', 0)->first();
        $agencyParentId = $agencySystem?->id ?? 0;

        $parentOrder = Menu::where('parent_id', $agencyParentId)->max('order') ?? 0;

        $formParent = Menu::firstOrCreate(
            [
                'title' => 'registration forms',
                'parent_id' => $agencyParentId,
            ],
            [
                'order' => $parentOrder + 1,
                'icon' => '📝',
            ]
        );

        $this->createChildMenu(
            $formParent->id,
            'form-templates',
            'form-templates',
            'browse-templates-form',
            '📄'
        );

        $this->createChildMenu(
            $formParent->id,
            'form-requests',
            'form-requests',
            'browse-form-request',
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
