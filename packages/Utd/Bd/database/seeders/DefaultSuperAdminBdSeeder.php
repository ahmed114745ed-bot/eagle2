<?php

namespace Utd\Bd\Database\Seeders;

use Utd\Bd\Entities\Bd;
use Modules\SuperAdmin\Entities\SuperAdmin;use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultSuperAdminBdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSuperAdmin = SuperAdmin::where('default', 1)->where(function ($q) {
            $q->where('country_id', 0)
                ->orWhereNull('country_id');
        })->first();

        if (!$defaultSuperAdmin){
            $defaultSuperAdmin = SuperAdmin::create([
                'username' => 'defaultSuperAdmin',
                'password' => Hash::make('defaultSuperAdmin'),
                'name' => 'default Super Admin',
                'type' => 'superadmin',
                'default' => 1,
                'country_id' => 0,
            ]);
        } else {
            if (is_null($defaultSuperAdmin->country_id)) {
                $defaultSuperAdmin->update(['country_id' => 0]);
            }
        }

        $defaultBd = Bd::where('default', 1)->where(function ($q) {
            $q->where('country_id', 0)
                ->orWhereNull('country_id');
        })->first();

        if ($defaultBd){
            if (is_null($defaultBd->country_id)) {
                $defaultBd->update(['country_id' => 0]);
            }

            $defaultBd->update(['parent_id' => $defaultSuperAdmin->id]);
        }else {
            Bd::create([
                'parent_id' => $defaultSuperAdmin->id,
                'username' => 'defaultBd',
                'password' => Hash::make('defaultBd'),
                'name' => 'default Bd',
                'type' => 'bd',
                'default' => 1,
                'country_id' => 0,
            ]);
        }
    }
}
