<?php

namespace Database\Seeders;


use Utd\Bd\Entities\Bd;
use App\Support\PackageHelper;
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


        if (PackageHelper::isInstalled('bd')) {
            $bds = Bd::where('country_id', null)->with('appUser')->get();

            foreach ($bds as $bd) {
                $bd->country_id = $bd->appUser->country_id;
                $bd->save();
            }
        }
    }
}
