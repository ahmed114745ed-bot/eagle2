<?php

namespace App\Admin\Actions;

use Admin;
use App\Facades\CustomNotification;
use App\Helpers\Common;
use App\Models\OVip;
use App\Models\Pack;
use App\Models\User;
use App\Models\UserVip;
use App\Models\Ware;
use Carbon\Carbon;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Achievement\Entities\UserAchievement;
use Modules\Public\Http\Services\UserCounterServices;

class AchievementDedicateAction extends RowAction
{
    public $name = 'Dedicate';

    public function handle(Model $model,Request $request)
    {
        $user = User::query()->searchByUuid($request->user_uuid)->first();
        $achievementId = $model->id; // Assuming 'id' is the primary key

        if (!$user) {
            return $this->response()->error('User not found')->refresh();
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
