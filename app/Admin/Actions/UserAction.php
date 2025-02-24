<?php

namespace App\Admin\Actions;

use App\Facades\CustomNotification;
use App\Models\Ban;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;


class UserAction extends Action
{
    public $name = 'حذف الحظر';
    public $id;
    public $charge_status;
    public $transfer_salary;
    public $show_invite_code;
    public $hide_chat;
    public $can_play;
    protected $selector = '.delete-ban';

    public function __construct($id = 0, $charge_status = 0, $transfer_salary = 0, $show_invite_code = 0, $hide_chat = 0, $can_play = 3)
    {
        $this->id = $id;
        $this->charge_status = $charge_status;
        $this->transfer_salary = $transfer_salary;
        $this->show_invite_code = $show_invite_code;
        $this->hide_chat = $hide_chat;
        $this->can_play = $can_play;


        parent::__construct();
    }

    public function handle(\Illuminate\Http\Request $request)
    {

        return $this->response()->success('success')->refresh();
    }


    public function form()
    {
        $this->hidden('id', __('id'))->attribute('id', 'id');
        $this->hidden('type', __('id'))->attribute('id', 'type');
        $this->hidden('ban_type_id', __('id'))->attribute('id', 'ban_type_id');

        /*$this->hidden('uid', 'uid')->value($this->id);
        $this->hidden('type', 'type')->value($this->type);
        $this->hidden('ban_type_id', 'ban_type_id')->value($this->ban_type_id);*/
    }



    public function html()
    {
        return '<a href="#" onclick="pu(\'' . $this->id . '\', \'' . $this->charge_status . '\', \'' . $this->transfer_salary . '\',  \'' . $this->show_invite_code . '\', \'' . $this->hide_chat . '\',' . $this->can_play . ')" class="btn btn-sm btn-success delete-ban">' . __('admin.delete') . '</a>
        <script>
            function pu(val, type, ban_type_id) {
                console.log(val, type, ban_type_id)
                $("#id").val(val);
                $("#type").val(type);
                $("#ban_type_id").val(ban_type_id);
            }
        </script>';
    }
}
