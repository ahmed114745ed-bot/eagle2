<?php

namespace Database\Seeders;


use App\Models\Bd;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EditCountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('agencies')
            ->join('users', 'agencies.app_owner_id', '=', 'users.id')
            ->update(['agencies.country_id' => DB::raw('users.country_id')]);


        Bd::whereNull('country_id')
            ->has('appUser')
            ->with('appUser')
            ->chunk(100, function ($bds) {
                foreach ($bds as $bd) {
                    $bd->country_id = $bd->appUser->country_id;
                    $bd->save();
                }
            });
    }
}
