<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ware;
use App\Models\Emoji;
use App\Models\Gift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\SpecialId\Entities\UserWare;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SortGiftEmojiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


    public function run(): void
    {
        DB::transaction(function () {

            // ---------- EMOJIS ----------
            DB::statement('SET @i := 0');
            DB::statement("
            UPDATE emojis
            SET sort = (@i := @i + 1)
            ORDER BY id
        ");

            // ---------- GIFTS ----------
            DB::statement('SET @i := 0');
            DB::statement("
            UPDATE gifts
            SET sort = (@i := @i + 1)
            ORDER BY id
        ");
        });
    }
}
