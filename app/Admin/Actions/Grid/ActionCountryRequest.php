<?php

namespace App\Admin\Actions\Grid;;

use App\Models\User;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class ActionCountryRequest extends BatchAction
{
    public $name;

    public function __construct()
    {
        parent::__construct();
        $this->name = __('status');
    }

    public function handle(Collection $collection, Request $request)
    {
        $status = $request->get('status');
        foreach ($collection as $model) {
            $model->update([
                'status' => $status,
            ]);
            App::setLocale($user->lan ?? 'en');
            $user = User::find($model->user_id);
            if ($status == 'accepted') {
                $title = __('Change Country Request');
                $body = __('Your country change request has been accepted');
                Common::sendOfficialMessage($user->id, $title, $body);
                Common::send_firebase_notification($user->notification_id, $title, $body);
            } else {
                $title = __('Change Country Request');
                $body = __('Your country change request has been accepted');
                Common::sendOfficialMessage($user->id, $title, $body);
                Common::send_firebase_notification($user->notification_id, $title, $body);
            }
        }



        return $this->response()->success(__('emoji moved successfully'))->refresh();
    }



    public function form()
    {

        $this->select('status', __('status'))
            ->options(['accepted' => __('accept'), 'rejected' => __('reject')])->required();
    }
}
