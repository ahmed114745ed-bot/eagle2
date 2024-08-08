<?php
namespace App\Admin\Controllers;
use App\Models\Charge;
use App\Models\CoinGameUser;
use App\Models\UserLuckyGift;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class CoinReportController extends MainController {
    public function index ( Content $content )
    {
        return $content
            ->title("Reports")
            ->description("Charges")
            ->row(function($row) {
                $row->column(2, view('admin.grid.common.report.coins'));
                $row->column(10, $this->grid());
            });
    }

    protected function grid()
    {
        $name= "lucky_gift";
        if (request("name") != null) {
            $name = request("name");
        }
        $grid = $this->{$name}();
        $grid->disableexport();
        $grid->disableActions ();
        $grid->disableCreateButton ();
        $grid->disableColumnSelector ();

        return $grid;
    }

    protected function lucky_gift()
    {
        $grid = new Grid(new UserLuckyGift());
        $grid->model()->selectRaw(
            'MIN(user_lucky_gifts.created_at) as earliest_created_at, ' .
            'SUM(user_lucky_gifts.number) as total_number, ' .
            'user_lucky_gifts.gift_id, ' .
            'user_lucky_gifts.user_id, ' .
            'MAX(users.name) as user_name, ' . // Aggregated using MAX
            'MAX(gifts.img) as gift_img, ' . // Aggregated using MAX
            'MAX(gifts.name) as gift_name, ' . // Aggregated using MAX
            'user_lucky_gifts.gift_price, ' .
            'SUM(CASE WHEN user_lucky_gifts.type = 1 THEN user_lucky_gifts.number ELSE 0 END) as total_number_win'
        )
        ->leftJoin('users', 'user_lucky_gifts.user_id', '=', 'users.id')
        ->leftJoin('gifts', 'user_lucky_gifts.gift_id', '=', 'gifts.id')
        ->groupBy(
            'user_lucky_gifts.gift_id',
            'user_lucky_gifts.user_id',
            'user_lucky_gifts.gift_price',
        )->orderByDesc('earliest_created_at');

        $grid->filter(function ($filter) {
            $filter->expand();
            $filter->column(1/2, function ($filter) {
                $filter->where(function ($query) {
                    $query->where('users.uuid', $this->input);
                }, __('Uid'), 'Uid');
            });
            $filter->column(1/2, function ($filter) {
                $filter->where(function ($query) {
                    $datt = \App\Helpers\UserCommon::arabicToEnglishNumbers($this->input);
                    $query->whereDate('user_lucky_gifts.created_at', '>=', $datt);
                }, __('from_date'), 'from_date')->date();
            });
            $filter->column(1/2, function ($filter) {
                $filter->where(function ($query) {
                    $datt=\App\Helpers\UserCommon::arabicToEnglishNumbers($this->input);

                    $query->whereDate('user_lucky_gifts.created_at', '<=',$datt);

                }, __('to_date'), 'to_date')->date();
            });
        });

        $grid->column('user.uuid', __('user Id'));
        $grid->column('user_name', __('user name'));
        $grid->column('gift.img', __('gift image'))->image();
        $grid->column('gift.name', __('gift name'));
        $grid->column('total_number', __('number'));
        $grid->column(__('cost'))->display(function () {
            return $this->total_number * $this->gift_price;
        });
        $grid->column(__('win'))->display(function () {
            return $this->total_number_win * $this->gift_price;
        });
        $grid->column('earliest_created_at', __('created at'));

        return $grid;
    }

    protected function games()
    {
        $grid = new Grid(new CoinGameUser());
        $grid->model()->selectRaw('MIN(coin_game_users.created_at) as earliest_created_at, coin_game_users.user_id, MAX(users.name) as user_name, SUM(CASE WHEN coin_game_users.type = 1 THEN coin_game_users.coins ELSE 0 END) as total_coins_win, SUM(CASE WHEN coin_game_users.type = 0 THEN coin_game_users.coins ELSE 0 END) as total_coins_lose')
            ->leftJoin('users', 'coin_game_users.user_id', '=', 'users.id')
            ->groupBy('coin_game_users.user_id')->orderByDesc('earliest_created_at');


        $grid->filter(function ($filter) {
            $filter->expand();
            $filter->column(1/2, function ($filter) {
                $filter->where(function ($query) {
                    $query->where('users.uuid', $this->input);
                }, __('Uid'), 'Uid');
            });
            $filter->column(1/2, function ($filter) {
                $filter->where(function ($query) {
                    $datt = \App\Helpers\UserCommon::arabicToEnglishNumbers($this->input);
                    $query->whereDate('coin_game_users.created_at', '>=', $datt);
                }, __('from_date'), 'from_date')->date();
            });
            $filter->column(1/2, function ($filter) {
                $filter->where(function ($query) {
                    $datt=\App\Helpers\UserCommon::arabicToEnglishNumbers($this->input);

                    $query->whereDate('coin_game_users.created_at', '<=',$datt);

                }, __('to_date'), 'to_date')->date();
            });
//            $filter->column(1/2, function ($filter) {
//                $filter->equal('game_id', __('Game Type'))
//                    ->select([
//                        1 => 'Game Type 1',
//                        2 => 'Game Type 2',
//                        3 => 'Game Type 3',
//                    ]);
//            });
        });

        $grid->column('user.uuid', __('user id'));
        $grid->column('user_name', __('user name'));
        $grid->column('game_name', __('game name'));
        $grid->column('total_coins_lose', __('loser'));
        $grid->column('total_coins_win', __('win'));
        $grid->column('earliest_created_at', __('created at'));

        return $grid;
    }

    protected function shipping_host()
    {
        $grid = new Grid(new Charge());
        $grid->model()->selectRaw('MIN(charges.created_at) as earliest_created_at,charges.user_id, MAX(users.name) as user_name, sum(CASE WHEN charges.user_type = "app" THEN charges.amount ELSE 0 END) as total_coins_from_user, sum(CASE WHEN charges.user_type != "app" AND charges.user_type != "dash" THEN charges.amount ELSE 0 END) as total_coins_shipping')
            ->leftJoin('users', 'charges.user_id', '=', 'users.id')
            ->groupBy( 'charges.user_id')->orderByDesc('earliest_created_at');

        $grid->filter(function ($filter) {
            $filter->expand();
            $filter->column(1/2, function ($filter) {
                $filter->where(function ($query) {
                    $query->where('users.uuid', $this->input);
                }, __('Uid'), 'Uid');
            });
            $filter->column(1/2, function ($filter) {
                $filter->where(function ($query) {
                    $datt = \App\Helpers\UserCommon::arabicToEnglishNumbers($this->input);
                    $query->whereDate('charges.created_at', '>=', $datt);
                }, __('from_date'), 'from_date')->date();
            });
            $filter->column(1/2, function ($filter) {
                $filter->where(function ($query) {
                    $datt=\App\Helpers\UserCommon::arabicToEnglishNumbers($this->input);

                    $query->whereDate('charges.created_at', '<=',$datt);

                }, __('to_date'), 'to_date')->date();
            });
        });

        $grid->column('user.uuid', __('user id'));
        $grid->column('user_name', __('user name'));
        $grid->column('total_coins_shipping', __('from shipping'));
        $grid->column('total_coins_from_user', __('from user'));
        $grid->column('earliest_created_at', __('created at'));
        return $grid;
    }

}
