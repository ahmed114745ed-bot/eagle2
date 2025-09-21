<?php

namespace Database\Seeders;

use App\Models\Bd;
use Illuminate\Database\Seeder;

class SyncBdCountrySeeder extends Seeder
{
    public function run(): void
    {
        Bd::with('appUser')
            ->whereNull('country_id')
            ->chunk(100, function ($bds) {
                foreach ($bds as $bd) {
                    if ($bd->appUser && $bd->appUser->country_id) {
                        $bd->country_id = $bd->appUser->country_id;
                        $bd->save();
                    }
                }
            });
    }
}
