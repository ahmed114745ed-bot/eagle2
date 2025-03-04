<?php

namespace Database\Seeders;

use App\Models\Timezone;
use DateTimeZone;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimezonesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all PHP-supported timezones
        $timezones = DateTimeZone::listIdentifiers();
        // Clear existing timezones (optional)
        DB::table('timezones')->truncate();

        // Insert timezones
        foreach ($timezones as $index => $timezone) {
            Timezone::create([
                'name' => $timezone,
            ]);
        }
    }
}
