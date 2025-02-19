<?php

namespace App\Admin\Actions;

use App\Models\User;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Achievement\Entities\UserAchievement;

class AchievementDedicateAction extends RowAction
{
    public $name = 'Dedicate';

    public function handle(Model $model,Request $request)
    {
        $user = User::query()->searchByUuid($request->user_uuid)->first();
        $achievementId = $model->achievement_id; // Assuming 'id' is the primary key

        if (!$user) {
            return $this->response()->error('User not found')->refresh();
        }

        $achievement = UserAchievement::where('user_id', $user->id)->where('achievement_id', $achievementId)->exists();
        if($achievement){
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

    public function form(){
        $this->text('user_uuid', 'user uuid');
    }

}
