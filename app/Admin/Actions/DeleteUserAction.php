<?php

namespace App\Admin\Actions;

use App\Models\Agency;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Facades\Admin;
use App\Contracts\RoleRewardContract;

class DeleteUserAction extends RowAction
{
    public function name(): string
    {
        return __('Delete');
    }

    public function handle(Model $model)
    {
        if ($model){
            if ($model->isRole('admin') || $model->isRole('developer')){
                return $this->response()->error(__('admin cant be deleted'))->refresh();
            }
        }
        Agency::query()->where('owner_id', $model->id)->delete();
        $roles = $model->roles()->get(['id', 'slug']);

        foreach ($roles as $role) {
            app(RoleRewardContract::class)->revokeRewardsFromAllUsersForRole($role->id, $role->slug);
        }

        $model->delete();

        return $this->response()->success('Deleted successfully')->refresh();
    }
}
