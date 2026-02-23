<?php

namespace Utd\Family\Observers;

use Utd\Family\Entities\Family;
use Utd\Family\Entities\FamilyUser;

class FamilyObserver
{
    /**
     * Handle the Family "deleted" event.
     *
     * @return void
     */
    public function deleted(Family $family)
    {
        $userClass = family_model('user');
        if (! $userClass) {
            return;
        }

        $owner = $userClass::find($family->user_id);
        $milestoneHelper = family_module('milestones', 'helper');

        if ($milestoneHelper && $owner) {
            $milestoneHelper::removeReward($owner, 'family-owner');
        }

        $userClass::query()->where('family_id', $family->id)->update(['family_id' => null]);
        FamilyUser::query()->where('family_id', $family->id)->delete();

        if ($milestoneHelper && $owner) {
            $milestoneHelper::removeReward($owner, 'family-owner');
        }
    }
}
