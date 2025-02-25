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
        $this->hidden('id', __('ID'))->attribute('id', 'id');

        $this->select('charge_status', __('Charge Status'))
            ->options([1 => __('on'), 0 => __('off')])
            ->attribute('id', 'charge_status');

        $this->select('transfer_salary', __('Transfer Salary'))
            ->options([1 => __('on'), 0 => __('off')])
            ->attribute('id', 'transfer_salary'); // Fixed ID

        $this->select('show_invite_code', __('Show Invite Code'))
            ->options([1 => __('on'), 0 => __('off')])
            ->attribute('id', 'show_invite_code'); // Fixed ID

        $this->select('hide_chat', __('Hide Chat'))
            ->options([1 => __('on'), 0 => __('off')])
            ->attribute('id', 'hide_chat');

        $this->select('can_play', __('Can Play'))
            ->options([2 => __('on'), 3 => __('off')])
            ->attribute('id', 'can_play');
    }



    public function html()
    {
        return '<a href="#" onclick="openUserForm(' .
            '\'' . $this->id . '\', ' .
            '\'' . $this->charge_status . '\', ' .
            '\'' . $this->transfer_salary . '\', ' .
            '\'' . $this->show_invite_code . '\', ' .
            '\'' . $this->hide_chat . '\', ' .
            '\'' . $this->can_play . '\'' .
            ')" class="btn btn-sm btn-success delete-ban">' . __('admin.switch') . '</a>

        <script>
            function openUserForm(id, charge_status, transfer_salary, show_invite_code, hide_chat, can_play) {
                console.log(id, charge_status, transfer_salary, show_invite_code, hide_chat, can_play);
                
                $("#id").val(id);
                $("#charge_status").prop("checked", charge_status == 1);
                $("#transfer_salary").prop("checked", transfer_salary == 1);
                $("#show_invite_code").prop("checked", show_invite_code == 1);
                $("#hide_chat").prop("checked", hide_chat == 1);
                $("#can_play").val(can_play);
            }
        </script>';
    }
}
