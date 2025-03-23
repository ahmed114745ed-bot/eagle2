<?php

namespace App\Admin\Actions;

use App\Models\User;
use Illuminate\Http\Request;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\RowAction;
use App\Models\AchievementValidImage;
use Illuminate\Database\Eloquent\Model;
use Modules\Achievement\Entities\UserAchievement;
use Modules\Achievement\Entities\UserAchievementLevel;


class AchievementDedicate extends Action
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
        $user = User::query()->searchByUuid($request->user_uuid)->first();

        if (!$user) {
            return $this->response()->error('User not found')->refresh();
        }

        $achievement =    AchievementValidImage::find($request->id);
        $userAchievement =   UserAchievementLevel::where(['user_id' => $user->id, 'custom_image' =>  $achievement->file,])->exists();
        if ($userAchievement) {
            return $this->response()->error('Achievement already added!')->refresh();
        }
        $attributes = [
            'user_id'       => $user->id,
            'custom_image' =>  $achievement->file,
        ];

        UserAchievementLevel::create($attributes);

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
