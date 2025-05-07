<?php

namespace App\Admin\Actions;

use App\Models\AgencyJoinRequest;
use Encore\Admin\Actions\Action;


class RefuseAgencyJoinRequestAction extends Action
{

    public $id;

    protected $selector = '.salary_action';

    public function __construct($id = 0)
    {

        $this->id = $id;
        parent::__construct();
    }
    public function handle(\Illuminate\Http\Request $request)
    {
        $agencyJoinRequest = AgencyJoinRequest::where('id', $request->id)->with('user')->fist();
        if (!$agencyJoinRequest) return $this->response()->error(__('not found'))->refresh();
        $agencyJoinRequest->status = 2;
        $agencyJoinRequest->save();

        return $this->response()->success('success')->refresh();
    }


    public function form()
    {
        $this->hidden('id', __('id'))->attribute('id', 'vid');

        $this->confirm(__('messages.confirm_delete'), __('messages.refuse'), [
            'icon' => 'warning',
            'showCancelButton' => true,
            'confirmButtonText' => __('messages.yes'),
            'cancelButtonText' => __('messages.cancel'),
        ]);
    }


    public function html()
    {
        return '<a href="javascript:void(0);" onclick="pu(' . $this->id . ')" class="btn btn-sm btn-info salary_action ">' . __('refuse') . '</a>
<script>
function pu(val) {

  $("#vid").val(val)
}
</script>
';
    }
}
