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

        $grid =  parent::grid();

        $grid->actions(function ( $actions) {
                $actions->disableDelete();              
                $deleteUrl = request()->getSchemeAndHttpHost() . "/superadmin/auth-users/" . $actions->getKey();
                $actions->append('<a href="javascript:void(0);" onclick="customDelete(' . $actions->getKey() . ')" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>');
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
                    
                    function customDelete(id) {
                        if (confirm('هل أنت متأكد من حذف هذا المستخدم؟')) {
                            fetch('/superadmin/auth-users/' + id, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                    'Content-Type': 'application/json',
                                },
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.error) {
                                    toastr.error(data.message || data.error);
                                } else {
                                    toastr.success('تم الحذف بنجاح');
                                    location.reload();
                                }
                            })
                            .catch(error => {
                                toastr.error('حدث خطأ أثناء الحذف');
                                console.error('Error:', error);
                            });
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
        try {
            $user = $this->model->find($id);
            if ($user){
                if ($user->isRole('admin') || $user->isRole('developer')){
                    return response ()->json (['error' => true, 'message' => __('admin cant be deleted')]);
                }
            }

            $OldUserAppId = User::find($user->app_id);
            if ($OldUserAppId) {
                $OldUserAppId->is_sub_super_admin = 0;
                $OldUserAppId->save();
            }

            Agency::query ()->where ('owner_id',$id)->delete ();

            // حذف المستخدم
            $user->delete();
            
            return response()->json(['success' => true, 'message' => 'تم الحذف بنجاح']);
            
        } catch (\Exception $e) {
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
