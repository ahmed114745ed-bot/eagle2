<?php

namespace Utd\Family\Database\Seeders;

use Illuminate\Database\Seeder;
use Utd\Family\Entities\Family;

class FamilyCountryUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $families = Family::with('owner')->get();

        foreach ($families as $family) {
            $family->country_id = @$family->owner?->country_id ?? null;
            $family->save();
        }
    }
}
