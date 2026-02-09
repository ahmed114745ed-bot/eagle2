<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Modules\Badge\Entities\Badge;


class BadgeImageSeeder extends Seeder
{
    public function run()
    {

        $badges = Badge::get();

        foreach ($badges as $badge) {
            $badge->images()->create([
                'language' => 'en',
                'image' => $badge->image,
                'image_type' => $badge->image_type ?? 'image',
                'show_image' => $badge->show_image,
            ]);
        }
    }
}
