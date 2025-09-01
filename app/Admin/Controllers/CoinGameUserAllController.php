<?php


namespace App\Admin\Controllers;

use App\Models\CoinGameUserAll;
use App\Models\User;
use App\Models\AllGame;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\HasResourceActions;
use Illuminate\Http\Request;
class CoinGameUserAllController extends AdminController
{
    use HasResourceActions;

    protected $permission_name = 'coin-game-users';

    public function index(Content $content)
    {
        return $content
            ->title(__('coin_game_users'))
            ->description(__('coin_game_users_description'))
            ->body($this->grid());
    }

    public function showAll(Content $content, Request $request)
    {
        $userId = $request->get('user_id');
        $gameId = $request->get('game_id');
    
        $grid = new Grid(new CoinGameUserAll());
    
        $grid->model()
            ->where('user_id', $userId)
            ->where('game_id', $gameId)
            ->orderByDesc('id');
    
        $grid->column('id', __('Round ID'))->sortable();
    
        $grid->column('type', __('Result'))->display(function ($value) {
            if ($value == 1) {
                return "<span class='label label-success'>".__('Win')."</span>";
            }
            return "<span class='label label-danger'>".__('Lose')."</span>";
        });
    
        $grid->column('coins', __('Bet Amount'))->display(function ($coins) {
            return "<b style='color:#222751'>{$coins}</b>";
        });
    
        $grid->column('created_at', __('Created at'))->sortable();
    
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableActions();
    
        return $content
            ->title(__('round_details'))
            ->description(__('round_details') . " | User: {$userId} | Game: {$gameId}")
            ->body($grid);
    }
    
    
    
        protected function grid()
    {
        $grid = new Grid(new CoinGameUserAll());

        $grid->model()
        ->selectRaw("
            user_id,
            game_id,
            SUM(coins) as total_played,
            SUM(CASE WHEN type = 0 THEN coins ELSE 0 END) as total_loss,
            SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) as total_win,
            (SUM(coins) - SUM(CASE WHEN type = 1 THEN coins ELSE 0 END)) as app_profit
        ")
        ->groupBy('user_id', 'game_id')
        ->orderByDesc('total_played');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();

            $filter->where(function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', "%{$this->input}%")
                      ->orWhere('uuid', 'like', "%{$this->input}%");
                });
            }, __('user'))->placeholder(__('search user by name or UUID'));

            $filter->where(function ($query) {
                $query->whereHas('game', function ($q) {
                    $q->where('name', 'like', "%{$this->input}%");
                });
            }, __('game'))->placeholder(__('search game by name'));

            $filter->between('created_at', __('created_at'))->datetime();
        });

        $grid->column('user_id', __('user'))->display(function ($userId) {
            $user = User::find($userId);
            if (!$user) return '-';
            $name = $user->name;
            $uuid = $user->uuid;
            $path = $user->image;
            $url = $path ?? asset("images/businessman-icon.jpg");
            $image = "<img src='{$url}' style='width:40px;height:40px;border-radius:50%;'>";
            return "<div style='display:flex;align-items:center;gap:10px;'>$image<div><strong>$name</strong><br><small style='color:#aaa;'>UID: $uuid</small></div></div>";
        });

        $grid->column('game_id', __('game'))->display(function ($gameId) {
            $game = AllGame::find($gameId);
            return $game ? "<strong>{$game->name}</strong>" : '-';
        });

        $grid->column('total_played', __('Total Played'));
        $grid->column('total_loss', __('Total Loss'));
        $grid->column('total_win', __('Total Win'));
        $grid->column('app_profit', __('App Profit'));

        $grid->column('details', __('Details'))->display(function () {
            $url = admin_url("coin-game-users/show?user_id={$this->user_id}&game_id={$this->game_id}");
            return "<a href='{$url}' class='btn btn-sm btn-primary'>
                        <i class='fa fa-eye'></i> ".__('round_details')."
                    </a>";
        });
    

        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableExport();

        return $grid;
    }
}
