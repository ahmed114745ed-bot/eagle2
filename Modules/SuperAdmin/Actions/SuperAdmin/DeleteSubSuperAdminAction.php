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

    // Override الـ html method لاستخدام custom URL
    public function html()
    {
        $key = $this->getKey();
        return <<<HTML
        <a href="javascript:void(0);" 
           onclick="superAdminDelete('{$key}')" 
           class="btn btn-xs btn-danger">
            <i class="fa fa-trash"></i> {$this->name()}
        </a>
        HTML;
    }

    // Override الـ script method لإضافة JavaScript مخصص
    public function script()
    {
        return <<<SCRIPT
        function superAdminDelete(id) {
            if (confirm('هل أنت متأكد من حذف هذا المستخدم؟')) {
                $.ajax({
                    method: 'DELETE',
                    url: '/superadmin/auth-users/' + id,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('تم الحذف بنجاح');
                            location.reload();
                        } else {
                            toastr.error(response.message || 'حدث خطأ');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('حدث خطأ أثناء الحذف');
                    }
                });
            }
        }
        SCRIPT;
    }
}