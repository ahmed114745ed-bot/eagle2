<?php

namespace App\Admin\Actions;

use App\Facades\CustomNotification;
use App\Models\Ban;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;


class DeleteBans extends Action
{
    public $name = 'حذف الحظر';
    public $id;
    public $type;
    public $ban_type_id;
    protected $selector = '.delete-ban';

    public function __construct($uid = 0 , $type = 0 ,$ban_type_id = 0 )
    {
        $this->id = $uid;
        $this->type = $type;
        $this->ban_type_id = $ban_type_id;

        parent::__construct();

    }

    public function handle( \Illuminate\Http\Request $request)
    {
        $user = User::query ()->where ('uuid',$request->uid)->first();
        if (!$user){
            return $this->response()->error(__('user not found'))->refresh();
        }
        Ban::query ()->where('uid',$request->uid)->where('type',$request->type)->where('ban_type_id',$request->ban_type_id)->delete();
        CustomNotification::removeBanUser($user);
        return $this->response()->success('success')->refresh();
    }


    public function form()
    {
        $this->hidden('uid', __('id'))->attribute('id', 'uid');
        $this->hidden('type', __('id'))->attribute('id', 'type');
        $this->hidden('ban_type_id', __('id'))->attribute('id', 'ban_type_id');

        /*$this->hidden('uid', 'uid')->value($this->id);
        $this->hidden('type', 'type')->value($this->type);
        $this->hidden('ban_type_id', 'ban_type_id')->value($this->ban_type_id);*/
    }



    public function html()
    {
        return '<a href="#" onclick="pu(\'' . $this->id .'\', \'' . $this->type .'\', '.$this->ban_type_id.')" class="btn btn-sm btn-success delete-ban">'.__('admin.delete').'</a>
        <script>
            function pu(val, type, ban_type_id) {
                console.log(val, type, ban_type_id)
                $("#uid").val(val);
                $("#type").val(type);
                $("#ban_type_id").val(ban_type_id);
            }
        </script>';
    }

}
