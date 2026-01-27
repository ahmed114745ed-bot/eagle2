<?php

namespace Utd\Achievements\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Utd\Achievements\Entities\Achievement;
use Utd\Achievements\Enums\AchievementType;

class AchievementTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        Achievement::query()->create([
            'type' => AchievementType::RECHARGE_TARGET,
            'valid_image' => '/test',
            'invalid_image' => '/test2',
        ]);

        Achievement::query()->create([
            'type' => AchievementType::ROOM_TARGET,
            'valid_image' => '/test',
            'invalid_image' => '/test2',
        ]);

        Achievement::query()->create([
            'type' => AchievementType::GIFT_TARGET,
            'valid_image' => '/test',
            'invalid_image' => '/test2',
        ]);
    }
}
