<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;

class SyncAgencyCountrySeeder extends Seeder
{
    public function run(): void
    {
        Agency::with('owner')
            ->whereNull('country_id')
            ->chunk(100, function ($agencies) {
                foreach ($agencies as $agency) {
                    if ($agency->owner && $agency->owner->country_id) {
                        $agency->country_id = $agency->owner->country_id;
                        $agency->save();
                    }
                }
            });
    }
}
