<?php

namespace Traits\HelperTraits;

use App\Helpers\Common;
use App\Models\User;
use App\Traits\HelperTraits\CalcsTrait;
use Tests\TestCase;

class CalcsTraitTest extends TestCase
{

    public function testLevel_center()
    {

        $user = User::query()->first();
        echo $user->total_diamond_received . '-------';
        echo $user->sub_receiver_num . '-------';

        Common::level_center($user->id);
    }
}
