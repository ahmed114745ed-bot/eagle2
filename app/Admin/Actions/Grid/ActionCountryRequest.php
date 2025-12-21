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
            // Update model status
            $model->update(['status' => $status]);

            // Get user and set locale
            $user = User::find($model->user_id);
            App::setLocale($user->lan ?? 'en');

            // Prepare title and body based on status
            $title = __('Change Country Request');
            $body = $status === 'accepted'
                ? __('Your country change request has been accepted')
                : __('Your country change request has been rejected');

            // Send notifications
            Common::sendOfficialMessage($user->id, $title, $body);
            Common::send_firebase_notification($user->notification_id, $title, $body);
        }




        return $this->response()->success(__('emoji moved successfully'))->refresh();
    }



    public function form()
    {

        $this->select('status', __('status'))
            ->options(['accepted' => __('accept'), 'rejected' => __('reject')])->required();
    }
}
