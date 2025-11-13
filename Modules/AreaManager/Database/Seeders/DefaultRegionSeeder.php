<?php

namespace Modules\AreaManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AreaManager\Entities\AreaManager;
use Modules\AreaManager\Entities\Region;
use App\Models\Country;

class DefaultRegionSeeder extends Seeder
{
    public function run()
    {
        $defaultManager = AreaManager::where('default', 1)->first();

        if (!$defaultManager) {
            return;
        }

        $defaultRegion = Region::firstOrCreate(
            ['name' => 'Default Region for Default Manager', 'manager_id' => $defaultManager->id]
        );

        $countriesWithoutRegion = Country::whereDoesntHave('regions')->get();

        foreach ($countriesWithoutRegion as $country) {
            $country->regions()->attach($defaultRegion->id);
        }

        $this->command->info('✅');
    }
}
