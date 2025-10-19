<?php

namespace Database\Seeders;

use App\Models\Bd;
use App\Models\SuperAdmin;use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DefaultSuperAdminBdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSuperAdmin = SuperAdmin::where('default', 1)->where('country_id', 0 )->first();

        if (!$defaultSuperAdmin){
            $defaultSuperAdmin = SuperAdmin::create([
                'username' => 'defaultSuperAdmin',
                'password' => Hash::make('defaultSuperAdmin'),
                'name' => 'default Super Admin',
                'type' => 'superadmin',
                'default' => 1,
                'country_id' => 0,
            ]);
        }

        $defaultBd = Bd::where('default', 1)->where('country_id', 0 )->first();

        Log::info(['defaultBd'=>$defaultBd]);
        if ($defaultBd){
        Log::info(['defaultSuperAdmin'=>$defaultSuperAdmin->id]);

            $defaultBd->update(['parent_id' => $defaultSuperAdmin->id]);
        }else {
            Bd::create([
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
