<?php

namespace Modules\RoleRewards\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Facades\Admin;

class DeleteRoleReward extends RowAction
{
    public function name(): string
    {
        return __('Delete'); 
    }

    public function handle(Model $model)
    {
        $role = \Encore\Admin\Auth\Database\Role::find($model->role_id);
        if ($role) {
            $slug = $role->slug;

            \Modules\RoleRewards\Helpers\UserRoleRewardHelper::revokeRewardsFromAllUsersForRole(
                $model->role_id,
                $slug
            );
        }

        $model->delete();

        return $this->response()->success('Deleted successfully')->refresh();
    }
}
