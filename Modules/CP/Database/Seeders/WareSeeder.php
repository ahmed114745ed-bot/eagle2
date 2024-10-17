<?php

namespace Modules\CP\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table ('wares')->insert (
            [
                ['name'=>'ثلاث مقاعد اضافيه', 'type'=>'100','get_type'=>'100', 'expire'=>0, 'value'=>3],
            ]
        );
    }
}
