<?php

namespace App\Admin\Controllers;

use App\Models\Pack;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\InfoBox;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;

class UserStatisticsController extends Controller
{

    public function index(Content $content)
    {
        return $content
        ->row(function (Row $row) {
            $row->column(12, $this->showColSearch());
        })
        ->row(
            function ($row){
                $user=null;
                if (request("user") != null) {
                    $user = User::where("uuid",request("user"))->first();
                }
                if ($user != null) {
                    $userCommon = new UserCommon();

                    $data=$userCommon->userMoreStatistics($user);
                    $totalLosed = ($data['losed']['charges'] * -1 ?? 0) + ($data['losed']['request_background_images']??0) + ($data['losed']['packs'] ?? 0) + ($data['losed']['coin_games'] ??0) + ($data['losed']['gift_logs'] ??0);
                    $totalMinusBetween = (@$data['total']['earned'] ??0) - ($totalLosed ?? 0);

                    $userRacksPrice = $user->userPacks->where('user_id',$user->id)->whereIn('type',[4,5,6])->where('sender_id',null)->sum('price');
                  $senderPacksPrice =  $user->sendPacks->whereIn('type',[4,5,6])->sum('price');
                 $totalPacksPrice = ($userRacksPrice ?? 0) + ($senderPacksPrice ?? 0);
                }
                $row->column(6, new InfoBox(__('charges'), 'dollar', 'green', '?type=balance_details', @$data['earned']['charges'] ?? 0));
                $row->column(6, new InfoBox(__('charges'), 'dollar', 'red', '?type=balance_details', @$data['losed']['charges'] * -1 ?? 0));

                $row->column(6, new InfoBox(__('coin_logs'), 'dollar', 'green', '?type=balance_details',  @$data['earned']['coin_logs'] ?? 0 ));
                $row->column(6, new InfoBox(__('packs'), 'dollar', 'red', '?type=balance_details', @$totalPacksPrice ?? 0));

                $row->column(6, new InfoBox(__('exchange_logs'), 'dollar', 'green', '?type=balance_details', @$data['earned']['exchange_logs'] ??0 ));
                $row->column(6, new InfoBox(__('request_background_images'), 'dollar', 'red', '?type=balance_details', @$data['losed']['request_background_images'] ?? 0 ));

                $row->column(6, new InfoBox(__('coin_games'), 'dollar', 'green', '?type=balance_details', @$data['earned']['coin_games'] ?? 0 ));
                $row->column(6, new InfoBox(__('coin_games'), 'dollar', 'red', '?type=balance_details', @$data['losed']['coin_games'] ?? 0 ));

                $row->column(6, new InfoBox(__('lucky_gifts'), 'dollar', 'green', '?type=balance_details', @$data['earned']['lucky_gifts'] ?? 0 ));
                $row->column(6, new InfoBox(__('gift_logs'), 'dollar', 'red', '?type=balance_details',  @$data['losed']['gift_logs'] ?? 0 ));

                $row->column(6, new InfoBox(__('total earned'), 'dollar', 'blue', '?type=balance_details', @$data['total']['earned'] ?? 0 ));
                $row->column(6, new InfoBox(__('total losed'), 'dollar', 'blue', '?type=balance_details', @$totalLosed ?? 0 ));

                $row->column(6, new InfoBox(__('total mins'), 'dollar', 'yellow', '?type=balance_details', @$totalMinusBetween ?? 0 ));
                $row->column(6, new InfoBox(__('total'), 'dollar', 'yellow', '?type=balance_details', @$user?->di ??0 ));
            }
        );
    }

    protected function showColSearch()
    {
        $form = new Box();
        $form->view('admin.grid.users.userChargeView');

        return $form;
    }
}
