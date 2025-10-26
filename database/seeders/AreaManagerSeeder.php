<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AreaManager;
use App\Models\Country;
use Illuminate\Support\Str;

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

        $countries = Country::all();
        foreach ($countries as $country) {
            $country->area_manager_id = $manager->id;
            $country->save();
        }
    }
}
