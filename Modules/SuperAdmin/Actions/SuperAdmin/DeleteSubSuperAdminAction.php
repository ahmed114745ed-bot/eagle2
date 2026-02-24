<?php

namespace Modules\SuperAdmin\Actions\SuperAdmin;

use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DeleteSubSuperAdminAction extends RowAction
{
    public function name(): string
    {
        return __('Delete');
    }

    public function handle(Model $model, Request $request)
    {
        try {
            // if ($model) {
            //     if ($model->isRole('admin') || $model->isRole('developer')) {
            //         return $this->response()->error(__('admin cant be deleted'))->refresh();
            //     }
            // }

            $OldUserAppId = User::find($model->app_id);
            if ($OldUserAppId) {
                $OldUserAppId->is_sub_super_admin = 0;
                $OldUserAppId->save();
            }

            $model->delete();

            return $this->response()->success('تم الحذف بنجاح')->refresh();
            
        } catch (\Exception $e) {
            return $this->response()->error('حدث خطأ أثناء الحذف: ' . $e->getMessage())->refresh();
        }
    }

    public function dialog()
    {
        $this->confirm('هل أنت متأكد من حذف هذا المستخدم؟', 'سيتم حذف المستخدم نهائياً', []);
    }
}