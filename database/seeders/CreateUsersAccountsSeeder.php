<?php

namespace Database\Seeders;



use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Vip\Helpers\VipCommon;
use Illuminate\Support\Facades\Hash;
use Modules\Vip\Entities\OVip;
use Modules\Vip\Entities\UserVip;

class CreateUsersAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basePhone = '+2010';
        $startNumber = 12; // This will increment: 12, 22, 32, 42 ...
        $suffix = '22222'; // Last 6 digits
        $vips = OVip::all();
        for ($i = 0; $i < 20; $i++) {
            $phoneMiddle = $startNumber + ($i * 10); // 12, 22, 32, 42...
            $fullPhone = $basePhone . $phoneMiddle . $suffix;
            $userFind = User::where('phone', $fullPhone)->first();
            if ($userFind) {
                $userFind->forceDelete();
            }
            $user = User::create([
                'phone' => $fullPhone,
                'password' => 111,
            ]);

            $vip = $vips->random();
            VipCommon::createUserVip($vip, $user, $vip->expire, null, '', 1, 0, 0, 'buy-vip');
            $userVip = UserVip::where('user_id', $user->id)->first();

            VipCommon::handleVipActivation($userVip);
        }
    }
}
