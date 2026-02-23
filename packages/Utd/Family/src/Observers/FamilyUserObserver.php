<?php

namespace Utd\Family\Observers;

use Utd\Family\Entities\Family;
use Utd\Family\Entities\FamilyUser;

class FamilyUserObserver
{
    /**
     * Handle the FamilyUser "created" event.
     *
     * @return void
     */
    public function created(FamilyUser $familyUser)
    {
        if ($familyUser->status === 1) {
            $userModel = family_model_or_fail('user');
            $userModel::query()->where('id', $familyUser->user_id)->update(['family_id' => $familyUser->family_id]);
        }
    }

    /**
     * Handle the FamilyUser "updated" event.
     *
     * @return void
     */
    public function updated(FamilyUser $familyUser)
    {
        if ($familyUser->status === 1) {
            $userModel = family_model_or_fail('user');
            $userModel::query()->where('id', $familyUser->user_id)->update(['family_id' => $familyUser->family_id]);
        }
    }

    /**
     * Handle the FamilyUser "deleted" event.
     *
     * @return void
     */
    public function deleted(FamilyUser $familyUser)
    {
        $userModel = family_model_or_fail('user');
        $family = Family::query()->where('id', $familyUser->family_id)->first();
        if ($family) {
            $userModel::query()->where('id', $familyUser->user_id)->where('id', '!=', $family->user_id)->update(['family_id' => 0]);
        } else {
            $userModel::query()->where('id', $familyUser->user_id)->update(['family_id' => 0]);
        }
    }
}
