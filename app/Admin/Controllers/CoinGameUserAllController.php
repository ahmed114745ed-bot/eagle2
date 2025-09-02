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
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Layout\Row;
use Illuminate\Support\Facades\DB;
use App\Admin\Services\UserService;

use App\Services\Admin\CoinGameUserService;

class CoinGameUserAllController extends AdminController
{
    protected $permission_name = 'coin-game-users-report';
    protected $service;

    public function __construct(CoinGameUserService $service)
    {
        $this->service = $service;
    }

    /**
     * Main index page with totals and grid.
     */
    public function index(Content $content)
    {
        $filters = $this->service->normalizeFilters(request()->all());
        $query = $this->service->applyFilters(CoinGameUserAll::query(), $filters);
        $totals = $this->service->calculateTotals($query);

        return $content
            ->title(__('coin_game_users'))
            ->description(__('coin_game_users_description'))
            ->row(fn(Row $row) => $this->service->renderInfoBoxes($row, $totals))
            ->row(fn($row) => $row->column(12, $this->service->buildGrid()));
    }

    /**
     * Show all rounds for a specific user and game.
     */
    public function showAll(Content $content, Request $request)
    {
        $userId = $request->get('user_id');
        $gameId = $request->get('game_id');

        $grid = $this->service->buildShowAllGrid($userId, $gameId);

        return $content
            ->title(__('round_details'))
            ->description(__('round_details') . " | User: {$userId} | Game: {$gameId}")
            ->body($grid);
    }
}
