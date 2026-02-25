<?php

namespace Utd\RankingReward\Http\Controllers;

use App\Admin\Controllers\MainController;
use App\Models\Gift;
use App\Models\Ware;
use App\Selectables\Badges;
use App\Selectables\WaresByType;
use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Modules\Badge\Entities\Badge;
use Utd\RankingReward\Entities\RankingReward;
use Utd\Vip\Entities\OVip;

class RankingRewardController extends MainController
{
    public function destroy($id)
    {
        $id = request()->route('id');
        $reward = RankingReward::findOrFail($id);
        $ranking_range_id = $reward->ranking_range_id;
        $reward->delete();

        admin_toastr(__('Deleted successfully'));

        return [
            'status' => true,
            'message' => __('Deleted successfully'),
            'redirect' => admin_url('ranking-rewards/' . $ranking_range_id),
        ];
    }
}
