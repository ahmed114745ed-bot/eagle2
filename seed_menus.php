<?php

use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Support\Facades\DB;

// Find Area Manager parent or top level
$areaMenu = Menu::where('title', 'like', '%Area Manager%')->orWhere('uri', 'like', '%areaManager%')->first();
$parentIdArea = $areaMenu ? $areaMenu->id : 0;

if (!Menu::where('uri', 'coin-rate-settings')->where('title', 'Coin Rate Settings')->exists()) {
    $amMenu = Menu::create([
        'parent_id' => $parentIdArea,
        'order'     => 10,
        'title'     => 'Coin Rate Settings',
        'icon'      => 'fa-money',
        'uri'       => 'coin-rate-settings',
    ]);
    

    
    // Attach to Role if exists
    $amRole = Role::where('slug', 'area-manager')->orWhere('slug', 'like', '%area%')->first();
    if ($amRole) {
        $amMenu->roles()->save($amRole);
        echo "Attached AreaManager Menu to Role {$amRole->name}\n";
    }
}

// Find Super Admin module parent
// Since SuperAdmin dashboard has prefix 'superadmin'
$saMenu = Menu::where('title', 'like', '%Super Admin%')->orWhere('uri', 'like', '%superadmin%')->first();
$parentIdSa = $saMenu ? $saMenu->id : 0;

if (!Menu::where('uri', 'superadmin/coin-rate-settings')->where('title', 'Coin Rate Settings')->exists() && !Menu::where('uri', 'coin-rate-settings')->count() > 1) {
    // Note: Laravel Admin URI for Super Admin might just be coin-rate-settings if running under its own admin table?
    // Let's check how Super Admin routes are registered. They use `superadmin.` prefix and `superadmin` prefix uri.
    $saMenu = Menu::create([
        'parent_id' => $parentIdSa,
        'order'     => 10,
        'title'     => 'Coin Rate Settings',
        'icon'      => 'fa-money',
        'uri'       => 'coin-rate-settings',
    ]);
    
    $saRole = Role::where('slug', 'super-admin')->orWhere('slug', 'like', '%super%')->first();
    if ($saRole) {
        $saMenu->roles()->save($saRole);
        echo "Attached SuperAdmin Menu to Role {$saRole->name}\n";
    }
}

echo "Menus seeded successfully.\n";
