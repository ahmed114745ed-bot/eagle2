<?php

namespace Utd\RoleRewards\Actions;

use Encore\Admin\Actions\RowAction;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Facades\Admin;
use Utd\RoleRewards\Services\RoleRewardService;

class DeleteRoleReward extends RowAction
{
    public function name(): string
    {
        return __('Delete');
    }

    public function handle(Model $model)
    {
        $role = Role::find($model->role_id);

        if ($role) {
            $slug = $role->slug;

            app(RoleRewardService::class)->revokeSpecificRewardFromAllUsers(
                $model->role_id,
                $slug,
                $model->id,
                $model->rewardable_type,
                $model->rewardable_id
            );
        }

        $model->delete();

        return $this->response()->success('Deleted successfully')->refresh();
    }
}
