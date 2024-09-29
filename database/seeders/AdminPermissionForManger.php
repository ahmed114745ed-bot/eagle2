<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class AdminPermissionForManger extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            'moment',
            'wares-dedicate',
            'vips-dedicate',
            'users',
            'version',
            'bans',
            'auth-users',
            'user-levels',
            'agency-manager',
            'manger-agency-manager',
            'carousel',
            'manger-type',
            'official-messages',
            'banners',
            'wares-vips',
            'achievement',
            'user_achievement_level',
            'level',
            'achievement_level',
            'offers',
            'trashed-account-user',
            'special-history',
            'special-Ware',
            'special-frame',
            'event',
            'ovip',
            'room-vip',
            'withdraw-type',
            'weekly-star',
            'general-roles',
            'pk-event',
            'pk-event-rewards',
            'target-event',
            'weekly_star_rewards',
            'weekly-star',
            
        ];

        $methods = [
            'browse',
            'show',
            'create',
            'update',
            'delete',
            'edit',
        ];

        foreach ($permissions as $permission) {
            foreach ($methods as $method) {
                $slug = $method . '-' . $permission;
                $name = $method . ' ' . str_replace('-', ' ', $permission);
//                $lname = 'roles.' . $method . ' ' . str_replace('-', ' ', $permission);
                $isExist = DB::table('admin_permissions')->where('slug', $slug)->exists();

                if (!$isExist) {
//                    Lang::addLines([$lname => $name], 'en', );

                    // Insert the permission and its translation
                    DB::table('admin_permissions')->insert([
                                                               "name"        => $name,
                                                               "slug"        => $slug,
                                                               "http_method" => NULL,
                                                               "http_path"   => NULL,
                                                               "created_at"  => now(),
                                                               "updated_at"  => now(),
                                                           ]);

                    // Append the translation to the 'roles' language file
                }
            }
        }
    }

}
