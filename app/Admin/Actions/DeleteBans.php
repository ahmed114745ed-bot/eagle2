<?php

namespace App\Admin\Actions;

use App\Models\Ban;
use App\Models\User;
use Encore\Admin\Actions\Action;
use App\Facades\CustomNotification;
use Encore\Admin\Actions\RowAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Facades\Admin;


class DeleteBans extends Action
{
    public $name = 'حذف الحظر';
    public $id;
    public $type;
    public $ban_type_id;
    protected $selector = '.delete-ban';
    public $permission_name = 'bans';

    public function __construct($uid = 0, $type = 0, $ban_type_id = 0)
    {
        $this->id = $uid;
        $this->type = $type;
        $this->ban_type_id = $ban_type_id;

        parent::__construct();
    }

    public function handle(\Illuminate\Http\Request $request)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('delete-' . $this->permission_name);
        }
        $user = User::query()->where('uuid', $request->uid)->first();
        if (!$user) {
            return $this->response()->error(__('user not found'))->refresh();
        }
        Ban::query()->where('uid', $request->uid)->where('type', $request->type)->where('ban_type_id', $request->ban_type_id)->delete();
        CustomNotification::removeBanUser($user);
        return $this->response()->success('success')->refresh();
    }


    public function form()
    {
        $this->hidden('uid', __('id'))->default($this->id);
        $this->hidden('type', __('id'))->default($this->type);
        // $this->hidden('ban_type_id', __('id'))->default($this->ban_type_id);

        $this->confirm(__('messages.confirm_delete'), __('messages.are_you_sure'), [
            'icon' => 'warning',
            'showCancelButton' => true,
            'confirmButtonText' => __('messages.yes_delete'),
            'cancelButtonText' => __('messages.cancel'),
        ]);
    }


    public function html()
    {
        return '<a href="#" onclick="pu(\'' . $this->id . '\', \'' . $this->type . '\', ' . $this->ban_type_id . ')" class="btn btn-sm btn-success delete-ban">' . __('admin.delete') . '</a>
        <script>
            function pu(val, type, ban_type_id) {
                console.log(val, type, ban_type_id)
                $("#uid").val(val);
                $("#type").val(type);
                $("#deleteModal").modal("hide");
                $("#ban_type_id").val(ban_type_id);
                
            }
        </script>';
    }
}
