<?php

namespace Utd\Agency\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeleteShippingAgencyAction extends RowAction
{
    public $name;

    public function __construct($id = 0)
    {
        $this->name = __("dashboard.delete");
        parent::__construct();
    }

    /**
     * Get user model class from config
     */
    protected function getUserModel(): string
    {
        return config('agency-package.models.user', \App\Models\User::class);
    }

    /**
     * Check if milestones module is enabled and get helper
     */
    protected function getMilestoneHelper(): ?string
    {
        if (!config('agency-package.modules.milestones.enabled', false)) {
            return null;
        }
        
        $helperClass = config('agency-package.modules.milestones.helper');
        return class_exists($helperClass) ? $helperClass : null;
    }

    public function handle(Model $model, Request $request)
    {
        try {
            DB::beginTransaction();
            
            $userModel = $this->getUserModel();
            $owner = $userModel::find($model->app_owner_id);
            
            // Remove milestone reward if module is available
            $milestoneHelper = $this->getMilestoneHelper();
            if ($milestoneHelper && $owner) {
                $milestoneHelper::removeReward($owner, 'charge-agency-owner');
            }

            $model->delete();
            DB::commit();
            return $this->response()->success(__('dashboard.successful'))->refresh();
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->response()->error($exception->getMessage())->refresh();
        }
    }

    public function dialog()
    {
        $this->confirm(__('dashboard.chickDelete'), __('messages.deleteShipping'), [
            'icon' => 'warning',
            'showCancelButton' => true,
            'confirmButtonText' => __('messages.yes'),
            'cancelButtonText' => __('messages.cancel'),
        ]);
    }
}
