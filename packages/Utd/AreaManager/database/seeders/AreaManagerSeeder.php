<?php

namespace Utd\AreaManager\Database\Seeders;

use App\Models\Country;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Utd\AreaManager\Entities\AreaManager;
use Utd\AreaManager\Entities\Region;

class AreaManagerSeeder extends Seeder
{
    public function run(): void
    {
        $manager = AreaManager::create([
            'username' => 'default-area-manager',
            'name' => 'Default Area Manager',
            'password' => bcrypt('password123'),  
            'type' => 'area-manager',
            'default' => 1,
            
        ]);
        $defaultRegion = Region::firstOrCreate(
            ['name' => 'Default Region for Default Manager', 'manager_id' => $manager->id]
        );

        $countriesWithoutRegion = Country::whereDoesntHave('regions')->get();

        foreach ($countriesWithoutRegion as $country) {
            $country->regions()->attach($defaultRegion->id);
        }
    }
}
