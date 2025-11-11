<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Storage;
use Str;

class FlagSyrianSeeder extends Seeder
{
    public function run()
    {
        $flagDir = public_path('images/flags');
        $localPath = $flagDir . '/syr.svg'; // single file path, not glob

        if (!file_exists($localPath)) {
            return; // stop if image doesn't exist locally
        }

        $filename = basename($localPath);
        $gcsPath = 'images/flags/' . $filename;

        $country = Country::where('iso3', 'SYR')->first();
        if (!$country) {
            return; // stop if country not found
        }

        //Delete old image if exists
        if ($country->flag && Storage::disk('gcs')->exists($country->flag)) {
            Storage::disk('gcs')->delete($country->flag);
        }

        // Upload new flag
        Storage::disk('gcs')->put($gcsPath, file_get_contents($localPath), 'public');

        // Update database
        $country->update([
            'flag' => $gcsPath,
        ]);

        $bazel =  $flagDir . '/bra.svg';
        if (!file_exists($bazel)) {
            return; // stop if image doesn't exist locally
        }

        $filename = basename($bazel);
        $braPath = 'images/flags/' . $filename;
        $country = Country::where('iso3', 'BRA')->first();
        if (!$country) {
            return; // stop if country not found
        }

        //Delete old image if exists
        if ($country->flag && Storage::disk('gcs')->exists($country->flag)) {
            Storage::disk('gcs')->delete($country->flag);
        }

        // Upload new flag
        Storage::disk('gcs')->put($braPath, file_get_contents($bazel), 'public');

        // Update database
        $country->update([
            'flag' => $braPath,
        ]);

        Country::where('iso3', 'ISR')->delete();
    }
}
