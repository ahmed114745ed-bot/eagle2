<?php

namespace App\Admin\Actions;

use App\Helpers\Common;
use Encore\Admin\Facades\Admin;
use App\Models\Agency;
use App\Models\Ban;
use App\Models\Charge;
use App\Models\CoinLog;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Facades\CustomNotification;
use Encore\Admin\Auth\Permission;

class RemoveBanUser extends Action
{
    public $name;

    protected $selector = '.remove_ban_user_action';
    public $permission_name = 'bans';

    public function handle(Request $request)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('delete-' . $this->permission_name);
        }

        $banType = $request->ban_type;

        if ($banType === 'uuid') {
            $user = User::query()->searchByUuid('uuid', $request->uid)->first();
            if (!$user) {
                return $this->response()->error(__('User not found'))->refresh();
            }

            Ban::query()->where('uid', $user->original_uuid)->delete();
            CustomNotification::removeBanUser($user);

        } elseif ($banType === 'ip') {
            if (!$request->ip_address) {
                return $this->response()->error(__('IP Address is required'))->refresh();
            }
            Ban::query()->where('ip', $request->ip_address)->delete();

        } elseif ($banType === 'device') {
            if (!$request->device_token) {
                return $this->response()->error(__('Device Token is required'))->refresh();
            }
            Ban::query()->where('device_number', $request->device_token)->delete();
        }

        return $this->response()->success(__('Ban(s) successfully removed'))->refresh();
    }

    public function form()
    {
        $this->select('ban_type', __('Select Ban Removal Type'))
            ->options([
                'uuid'   => __('By UUID'),
                'ip'     => __('By IP Address'),
                'device' => __('By Device Token'),
            ])
            ->attribute(['id' => 'ban_type_selector'])
            ->rules('required');

        $this->text('uid', __('UUID'))->attribute(['id' => 'uuid_input']);
        $this->text('ip_address', __('IP Address'))->attribute(['id' => 'ip_input']);
        $this->text('device_token', __('Device Token'))->attribute(['id' => 'device_input']);
    }

    public function html()
    {
        Admin::script(<<<'JS'
            function toggleBanInputs() {
                var type = $('#ban_type_selector').val();
                $('#uuid_input').closest('.form-group').hide();
                $('#ip_input').closest('.form-group').hide();
                $('#device_input').closest('.form-group').hide();

                if (type === 'uuid') {
                    $('#uuid_input').closest('.form-group').show();
                } else if (type === 'ip') {
                    $('#ip_input').closest('.form-group').show();
                } else if (type === 'device') {
                    $('#device_input').closest('.form-group').show();
                }
            }

            $(document).on('change', '#ban_type_selector', toggleBanInputs);
            toggleBanInputs();
        JS);

        $removeBans = __('dashboard.remove_bans');

        return <<<HTML
           <a href="javascript:void(0);" class="remove_ban_user_action btn btn-sm text-white"
           style="background-color: var(--primary-color); border-color: var(--secondary-color); color: var(--text-secondary-color);">
                {$removeBans}
           </a>
        HTML;
    }
}
