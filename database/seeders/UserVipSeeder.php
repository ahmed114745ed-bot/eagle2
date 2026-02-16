<?php

namespace Database\Seeders;

use App\Support\PackageHelper;
use Utd\Vip\Entities\UserVip;
use Illuminate\Database\Seeder;


class UserVipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userVips = PackageHelper::isInstalled('vip') ? UserVip::where('expire', 0)->with('packs')->get() : collect();

        foreach ($userVips as $userVip) {
            $expires = $userVip->packs->pluck('expire')->filter(function ($value) {
                return $value !== null && $value != 0;
            });
            if ($expires->isNotEmpty()) {
                $userVip->update(['expire' => $expires->first()]);
            }
        }
    }
}
