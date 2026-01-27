<?php

namespace Database\Seeders;


use App\Helpers\Common;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class VipBackgroundImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $localPath = public_path('images/Vip');

        for ($level = 1; $level <= 7; $level++) {

            $fileName = "vip_background{$level}.png";
            $fullPath = $localPath . DIRECTORY_SEPARATOR . $fileName;

            if (!file_exists($fullPath)) {
                $this->command->warn("File not found: {$fileName}");
                continue;
            }

            // Convert local file to Laravel File object
            $file = new File($fullPath);

            // Upload to Google Storage using your function
            $newPath = Common::upload('images', $file);
            // 'gcs' = google cloud disk name (change if different)

            // Update ovip record
            DB::table('ovips')
                ->where('level', $level)
                ->update([
                    'background_img' => $newPath
                ]);

            $this->command->info("Uploaded & updated level {$level}");
        }

        $this->command->info('VIP background images uploaded to Google Storage successfully.');
    }
}
