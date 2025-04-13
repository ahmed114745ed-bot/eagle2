<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UpdateMonthlyDiamondSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('type_user', '!=', 0)->get();

        foreach ($users as $user) {
            $totalDays = $user->getTotalDays();
            
            $user->update([
                'monthly_days' => $totalDays
            ]);
        }
    }
} 