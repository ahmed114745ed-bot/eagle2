<?php

namespace Utd\Family\Database\Seeders;

use Illuminate\Database\Seeder;
use Utd\Family\Entities\FamilyUser;

class FamilyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userModel = family_model_or_fail('user');
        $profileModel = family_model_or_fail('profile');

        $users = $userModel::factory(100)->create(['di' => 900000]);

        foreach ($users as $user) {
            $profileModel::factory()->create(['user_id' => $user->id]);
            FamilyUser::factory()->create(['user_id' => $user->id, 'family_id' => 325]);
        }
    }
}
