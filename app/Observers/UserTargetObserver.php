<?php

namespace App\Observers;

use App\Models\Pk;
use App\Models\UserTarget;

class UserTargetObserver
{
    /**
     * Handle the PK "created" event.
     *
     * @param  \App\Models\PK  $pK
     * @return void
     */
    public function created(UserTarget $userTarget)
    {

    }
}
