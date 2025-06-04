<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateChargesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('charges')
            ->where('charger_type', 'bd')
            ->whereNull('user_id')
            ->whereNotNull('agency_id')
            ->update([
                'user_id' => DB::raw('agency_id'),
                'user_type' => 'agency'
            ]);

            DB::table('charges')
            ->whereNull('user_id')
            ->whereNotNull('agency_id')
            ->update([
                'user_id' => DB::raw('agency_id'),
                'user_type' => 'agency'
            ]);

            DB::table('charges')
            ->where('charger_type', 'host_agency')
            ->update([
                'charger_type' => 'host_agency',
                'user_charger_type' => 'host_agency',
            ]);
        $this->command->info('Charges table updated where charger_type = bd and user_id is null and agency_id is not null.');
    }
}
