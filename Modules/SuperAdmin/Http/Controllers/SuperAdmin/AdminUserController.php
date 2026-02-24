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
        \Log::info('AdminUserController grid method called', [
            'controller_class' => get_class($this),
            'request_url' => request()->url(),
            'request_path' => request()->path()
        ]);

        $grid =  parent::grid();

        $grid->actions(function ( $actions) {
                \Log::info('Grid actions callback called');
                $actions->disableDelete(); 
                $actions->add(new DeleteSubSuperAdminAction()); 
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
                    function copyAreaManagerUrl() {
                        const url = '{$areaManagerUrl}';
                        navigator.clipboard.writeText(url).then(() => {
                            toastr.success('تم نسخ الرابط بنجاح');
                        }).catch(() => {
                            alert('تعذر نسخ الرابط');
                        });
                    }
                    
                    // تعديل action الحذف ليشير للسوبر أدمن
                    $(document).ready(function() {
                        // البحث عن جميع forms التي تحتوي على _handle_action_
                        $('form[action*="_handle_action_"]').each(function() {
                            var currentAction = $(this).attr('action');
                            if (currentAction.includes('/admin/')) {
                                var newAction = currentAction.replace('/admin/', '/superadmin/');
                                $(this).attr('action', newAction);
                            }
                        });
                        
                        // مراقبة إضافة forms جديدة
                        $(document).on('submit', 'form[action*="_handle_action_"]', function(e) {
                            var currentAction = $(this).attr('action');
                            if (currentAction.includes('/admin/')) {
                                var newAction = currentAction.replace('/admin/', '/superadmin/');
                                $(this).attr('action', newAction);
                            }
                        });
                    });
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
