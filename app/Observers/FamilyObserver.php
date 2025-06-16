<?php

namespace App\Observers;

use App\Models\Family;
use App\Models\FamilyUser;
use App\Models\User;

class FamilyObserver
{
    /**
     * Handle the Family "deleted" event.
     *
     * @return void
     */
    public function deleted(Family $family)
    {
        User::query()->where('family_id', $family->id)->update(['family_id' => null]);
        FamilyUser::query()->where('family_id', $family->id)->delete();
    }
}
