<?php

namespace Utd\RankingReward\Http\Controllers;

use App\Admin\Controllers\MainController;
use Utd\RankingReward\Entities\RankingReward;

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
            'redirect' => admin_url('ranking-rewards/'.$ranking_range_id),
        ];
    }
}
