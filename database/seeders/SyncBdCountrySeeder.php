<?php

namespace Database\Seeders;

use App\Models\Bd;
use App\Models\SuperAdmin;
use Illuminate\Database\Seeder;

class SyncBdCountrySeeder extends Seeder
{
    public function run(): void
    {
        Bd::with('appUser')
            ->whereNull('country_id')
            ->chunk(100, function ($bds) {
                foreach ($bds as $bd) {
                    if ($bd->parent && $bd->parent->country_id) {
                        $bd->country_id = $bd->parent->country_id;
                    } elseif ($bd->appUser && $bd->appUser->country_id) {
                        $bd->country_id = $bd->appUser->country_id;
                    }

                    if ($bd->country_id) {
                        $bd->save();
                    }
                }
            });

        $superAdmins = SuperAdmin::all()->keyBy('country_id');

        Bd::whereNull('parent_id')
            ->whereNotNull('country_id')
            ->chunk(100, function ($bds) use ($superAdmins){
                foreach ($bds as $bd) {
                    $superAdmin = $superAdmins->get($bd->country_id);

                    if ($superAdmin) {
                        $bd->parent_id = $superAdmin->id;
                        $bd->save();
                    }
                }
            });
    }
}
