<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Bd;
use Illuminate\Database\Seeder;

class SyncAgencyCountrySeeder extends Seeder
{
    public function run(): void
    {
        Agency::with('owner')
            ->whereNull('country_id')
            ->chunk(100, function ($agencies) {
                foreach ($agencies as $agency) {
                    if ($agency->bd && $agency->bd->country_id) {
                        $agency->country_id = $agency->bd->country_id;
                    }
                    if ($agency->owner && $agency->owner->country_id) {
                        $agency->country_id = $agency->owner->country_id;
                    }

                    if ($agency->country_id) {
                        try {
                            $agency->save();
                        }catch (\Exception $e){}
                    }
                }
            });

        $bds = Bd::where('default', 1)->whereNotNull('country_id')->get()->keyBy('country_id');

        Agency::whereNull('bd_id')
            ->whereNotNull('country_id')
            ->chunk(100, function ($agencies) use ($bds){
                foreach ($agencies as $agency) {
                    $bd = $bds->get($agency->country_id);

                    if ($bd) {
                        $agency->bd_id = $bd->id;
                        $agency->save();
                    }
                }
            });
    }
}
