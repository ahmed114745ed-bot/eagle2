<?php

namespace Modules\SuperAdmin\Http\Controllers\SuperAdmin;

use App\Models\User;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\Agency;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use Modules\RoleRewards\Actions\DeleteUser;

use Modules\SuperAdmin\Actions\SuperAdmin\DeleteSubSuperAdminAction;
use Modules\SuperAdmin\Actions\SuperAdmin\CustomDeleteAction;
use Modules\RoleRewards\Helpers\UserRoleRewardHelper;
use App\Admin\Controllers\MainController;


class AdminUserController extends EncorUsersController
{
// \Encore\Admin\Controllers\UserController

    protected $model;

    public $permission_name = 'auth-users';

    public function __construct ()
    {
        $userModel = Admin::class;
        $this->model = new $userModel;
    }

    public function edit ( $id , Content $content )
    {

        return parent ::edit ( $id , $content );
    }

    public function grid ()
    {
        \Log::info('AdminUserController grid method called - COMPLETE OVERRIDE', [
            'controller_class' => get_class($this),
            'request_url' => request()->url(),
            'request_path' => request()->path()
        ]);

        $permission_name = $this->permission_name;

        $grid = new \Encore\Admin\Grid(new \App\Models\Admin());
        $authId = auth()->user()->type == 'superadmin' ? auth()->user()->id : auth()->user()->parent_id;

        $grid->model()->where(function ($q) use ($authId) {
            $q->where('parent_id', $authId);
        })
            ->where('is_preview', 0)
            ->where('type', 'sub_super_admin')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('slug', 'agency-owner');
            });

        $grid->column('id', 'ID')->sortable();
        $grid->column('username', trans('admin.username'))->sortable();
        $grid->column('name', trans('admin.name'))->sortable();
        $grid->column('roles', trans('admin.roles'))->pluck('name')->label();

        $grid->column('createdBy.name', __('created by'))->display(function () {
            $user = $this->createdBy;
            $name = $user->name ?? '';

            if (request()->filled('_export_')) {
                return $name;
            }
            if (!$user) return "<span style='color: red;'>غير مرتبط</span>";

            $id = $user->id ?? 'غير معروف';
            $path = $user->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = url("superadmin/superadmin-profile/{$user->id}");

            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='font-size: smaller;'>ID: $id</span>
                    </div>
                </div>
            ";
        });

        $grid->column('created_at', trans('admin.created_at'))->sortable();
        $grid->column('updated_at', trans('admin.updated_at'))->sortable();

        // الآن أضع الـ actions مع الاحتفاظ بالعرض والتعديل وإضافة حذف مخصص
        $grid->actions(function (\Encore\Admin\Grid\Displayers\Actions $actions) {
                \Log::info('Custom Grid actions callback called with full control');
                
                $actions->disableDelete(); // تعطيل الحذف الافتراضي فقط
                
                // إضافة زر حذف مخصص بتنسيق مناسب لـ Laravel Admin
                $id = $actions->getKey();
                $actions->append('<li><a href="javascript:void(0);" onclick="customSuperAdminDelete(' . $id . ')" class="text-danger"><i class="fa fa-trash"></i>&nbsp;&nbsp;Delete</a></li>');
                
                \Log::info('Custom delete action added successfully', ['id' => $id]);
        });

        $grid->tools(function ($tools) {
                $logoutUrl = route('superadmin.superadmin.logout');
                $loginText = __('login');
                $areaManagerUrl = url('/superadmin/login');

                $customButtonHTML = <<<HTML
                <div style="display: contents; align-items: center;">
                    <a href="{$logoutUrl}" class="btn btn-sm btn-danger" style="margin-right: 10px;">
                        <i class="fa fa-sign-in"></i> {$loginText}
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" onclick="copyAreaManagerUrl()">
                        <i class="fa fa-copy"></i>
                    </button>

                </div>
                <script>
                    if (typeof copyAreaManagerUrl === 'undefined') {
                        function copyAreaManagerUrl() {
                            const url = '{$areaManagerUrl}';
                            navigator.clipboard.writeText(url).then(() => {
                                toastr.success('تم نسخ الرابط بنجاح');
                            }).catch(() => {
                                alert('تعذر نسخ الرابط');
                            });
                        }
                    }
                    
                    if (typeof customSuperAdminDelete === 'undefined') {
                        function customSuperAdminDelete(id) {
                            console.log('customSuperAdminDelete called with id:', id);
                            
                            if (confirm('هل أنت متأكد من حذف هذا المستخدم؟')) {
                                console.log('Delete confirmed, sending AJAX to: /superadmin/auth-users/' + id);
                                
                                // الحصول على CSRF token بطريقة آمنة
                                var csrfToken = '';
                                var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                                if (csrfMeta) {
                                    csrfToken = csrfMeta.getAttribute('content');
                                } else if (window.Laravel && window.Laravel.csrfToken) {
                                    csrfToken = window.Laravel.csrfToken;
                                } else if ($('meta[name="csrf-token"]').length) {
                                    csrfToken = $('meta[name="csrf-token"]').attr('content');
                                }
                                
                                console.log('CSRF Token:', csrfToken);
                                
                                fetch('/superadmin/auth-users/' + id, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Content-Type': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(response => {
                                    console.log('Response status:', response.status);
                                    return response.json();
                                })
                                .then(data => {
                                    console.log('Delete response:', data);
                                    if (data.success) {
                                        toastr.success('تم الحذف بنجاح');
                                        location.reload();
                                    } else {
                                        toastr.error(data.message || 'حدث خطأ');
                                    }
                                })
                                .catch(error => {
                                    console.error('Delete error:', error);
                                    toastr.error('حدث خطأ أثناء الحذف');
                                });
                            }
                        }
                    }
                </script>
                HTML;

                $tools->append($customButtonHTML);
            });

        return $grid;

    }

    public function update ( $id )
    {
        $user = Admin::query ()->findOrFail ($id);
        if (\request ('password') != $user->password || \request ('username') != $user->username){
            Agent::where("id",$user->id)->update([
                "remember_token" => null
            ]);
            DB::table ('sessions')->where ('user_id',$user->id)->delete ();
        }
        return parent ::update ($id);
    }

    public function destroy ( $id )
    {
        \Log::info('AdminUserController destroy method called', [
            'id' => $id,
            'controller_class' => get_class($this),
            'request_url' => request()->url(),
            'request_method' => request()->method(),
            'request_path' => request()->path(),
            'current_route' => request()->route()->getName()
        ]);
        
        try {
            $user = $this->model->find($id);
            if ($user){
                if ($user->isRole('admin') || $user->isRole('developer')){
                    \Log::warning('Attempted to delete admin/developer user', ['user_id' => $id]);
                    return response ()->json (['error' => true, 'message' => __('admin cant be deleted')]);
                }
            }

            $OldUserAppId = User::find($user->app_id);
            if ($OldUserAppId) {
                $OldUserAppId->is_sub_super_admin = 0;
                $OldUserAppId->save();
                
                \Log::info('Updated user is_sub_super_admin in destroy method', [
                    'user_id' => $OldUserAppId->id,
                    'app_id' => $user->app_id
                ]);
            }

            Agency::query ()->where ('owner_id',$id)->delete ();

            // حذف المستخدم
            $user->delete();
            
            \Log::info('User deleted successfully in destroy method', ['user_id' => $id]);
            
            return response()->json(['success' => true, 'message' => 'تم الحذف بنجاح']);
            
        } catch (\Exception $e) {
            \Log::error('Error in AdminUserController destroy method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => true, 'message' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()]);
        }
    }

    public function form ()
    {

        $form =  parent ::form ();
        $form->select('app_id', __('validation.select_user'))->options(function ($value) {
            $ops2 = [];
            foreach (User::Where('id', $value)->get() as $user) {
                $ops2[$user->id] = $user->uuid . '_' . $user->name;
            }
            return $ops2;
        })->ajax('/api/search/users-subsuperadmin', 'id', 'name')->rules('required');
        return $form;
    }


}
