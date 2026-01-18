<?php

namespace Utd\Achievements\Admin\Actions;

use Illuminate\Http\Request;
use Encore\Admin\Actions\Action;
use Utd\Achievements\Entities\UserAchievement;

class AchievementDedicateAction extends Action
{
    protected $selector = '.salary_action';
    public $id;

    public function __construct($id = 0)
    {
        $this->id = $id;
        parent::__construct();
    }

    public function handle(Request $request)
    {
        $userModel = config('achievements.models.user');
        $user = $userModel::query()->searchByUuid($request->user_uuid)->first();
        $achievementId = request('id');

        if (!$user) {
            return $this->response()->error('User not found')->refresh();
        }

        $achievement = UserAchievement::where('user_id', $user->id)
            ->where('achievement_id', $achievementId)
            ->exists();

        if ($achievement) {
            return $this->response()->error('Achievement already added!')->refresh();
        }

        UserAchievement::query()->create([
            'user_id'        => $user->id,
            'achievement_id' => $achievementId,
            'target'         => 0,
            'total_target'   => 0,
            'month'          => now()->month,
            'year'           => now()->year,
        ]);

        return $this->response()->success('Achievement dedicated successfully')->refresh();
    }

    public function form()
    {
        $this->hidden('id', __('id'))->attribute('id', 'vid');
        $this->text('user_uuid', 'user uuid');
    }

    public function html()
    {
        return '<a href="javascript:void(0);" onclick="pu(' . $this->id . ')" class="btn btn-sm btn-info salary_action ">' . __('dedicate') . '</a>
<script>
function pu(val) {
  $("#vid").val(val)
}
</script>
';
    }
}
