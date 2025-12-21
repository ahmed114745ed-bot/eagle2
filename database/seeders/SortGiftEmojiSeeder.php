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
            DB::statement('SET @i := 0');

            Emoji::whereNull('sort')
                ->orderBy('id')
                ->get()
                ->each(function ($emoji) {
                    DB::table('emojis')
                        ->where('id', $emoji->id)
                        ->update(['sort' => DB::raw('@i := @i + 1')]);
                });


            Gift::whereNull('sort')
                ->orderBy('id')
                ->get()
                ->each(function ($emoji) {
                    DB::table('gifts')
                        ->where('id', $emoji->id)
                        ->update(['sort' => DB::raw('@i := @i + 1')]);
                });
        });
    }
}
