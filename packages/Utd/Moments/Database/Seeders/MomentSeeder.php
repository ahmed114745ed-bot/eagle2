<?php

namespace Utd\Moments\Database\Seeders;

use App\Models\MomentGallery;
use Illuminate\Database\Seeder;
use Utd\Moments\Entities\Moment;

class MomentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Moment::factory()->count(500)->create();

        // $Moments = Moment::inRandomOrder()->take(300)->get();
        // foreach($Moments as $moment){
        //     MomentLikes::create([
        //         'moment_id'=> $moment->id,
        //         'user_id' => rand(1,2)
        //     ]);
        // }

        // $Moments = Moment::inRandomOrder()->take(300)->get();

        // foreach($Moments as $moment){
        //     MomentCommint::create([
        //         'moment_id'=> $moment->id,
        //         'user_id' => rand(1,2),
        //         'comment' => fake()->text()
        //     ]);
        // }


        $moments = Moment::get();

        foreach($moments as $moment)
        {
            if($moment->img)
            {
                MomentGallery::create([
                    'moment_id' =>  $moment->id,
                    'image' => $moment->img,
                ]);
            }


        }



    }
}
