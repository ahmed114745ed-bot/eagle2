<?php

namespace App\Admin\Actions;

use App\Contracts\RoleRewardContract;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class DeleteRole extends RowAction
{
    public function name(): string
    {
        return __('Delete');
    }

    public function handle(Model $model)
    {
        app(RoleRewardContract::class)->revokeRewardsFromAllUsersForRole(
            $model->id,
            $model->slug
        );

        $model->delete();

        return $this->response()->success('Deleted successfully')->refresh();
    }
}
