<?php

namespace App\Admin\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Facades\Admin;

class DeleteRole extends RowAction
{
    public function name(): string
    {
        return __('Delete'); 
    }

    public function handle(Model $model)
    {
            \Utd\RoleRewards\Helpers\UserRoleRewardHelper::revokeRewardsFromAllUsersForRole(
                $model->id,
                $model->slug
            );

        $model->delete();

        return $this->response()->success('Deleted successfully')->refresh();
    }
}
