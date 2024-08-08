<?php

namespace Database\Seeders;
use App\Models\Config;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PusherConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $pusherConfigs = [
            ['name' => 'app_id', 'value' =>''],
            ['name' => 'app_key', 'value' => ''],
            ['name' => 'app_secret', 'value' => ''],
            ['name' => 'app_cluster', 'value' => ''],
        ];
            foreach ($pusherConfigs as $pusherConfig) {

                $pusherConfigExist = Config::query()->where('name', $pusherConfig['name'])->exists();
                if ($pusherConfigExist  ) continue;
    
                DB::table('configs')->insert($pusherConfig);
    
            }
    }
}