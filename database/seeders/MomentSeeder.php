<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Moment\Entities\Moment;
use Modules\Moment\Entities\MomentCommint;
use Modules\Moment\Entities\MomentLikes;

class MomentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Moment::factory()->count(500)->create();

        $moments = Moment::inRandomOrder()->take(300)->get();
        foreach($moments as $moment){
            MomentLikes::create([
                'moment_id'=> $moment->id,
                'user_id' => rand(1,2)
            ]);
        }

        $moments = Moment::inRandomOrder()->take(300)->get();

        foreach($moments as $moment){
            MomentCommint::create([
                'moment_id'=> $moment->id,
                'user_id' => rand(1,2),
                'comment' => fake()->text()
            ]);
        }

    }
}
