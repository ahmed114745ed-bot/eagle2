<?php

namespace Utd\Family\Observers;

use Utd\Family\Entities\Family;
use Utd\Family\Entities\FamilyUser;
use Modules\Milestones\Entities\Milestone;
use Modules\Milestones\Helpers\MilestoneHelper;

class FamilyObserver
{
    /**
     * Handle the Family "deleted" event.
     *
     * @return void
     */
    public function deleted(Family $family)
    {
        $owner = User::find($family->user_id);
        MilestoneHelper::removeReward($owner, 'family-owner');
        User::query()->where('family_id', $family->id)->update(['family_id' => null]);
        FamilyUser::query()->where('family_id', $family->id)->delete();
        MilestoneHelper::removeReward($owner, 'family-owner');
    }
}
