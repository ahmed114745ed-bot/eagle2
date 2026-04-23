<?php

namespace App\Services\Admin;

use App\Models\User;
use Encore\Admin\Grid;
use App\Models\AllGame;
use Encore\Admin\Layout\Row;
use Illuminate\Support\Carbon;
use App\Models\CoinGameUserAll;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Facades\DB;
use App\Admin\Widgets\CustomInfoBox;
use App\Models\CoinGameUserAggregated;
use App\Admin\Services\UserGameService;
use App\Models\CoinGameUserDailyAggregated;


class CoinGameUserService
{
    protected $userService;

    public function __construct(UserGameService $userService)
    {
        $this->userService = $userService;
    }


    public function applyFilters($query,  array $filters)
    {
        if (!empty($filters['user']['uuid'])) {
            $userUuid = $filters['user']['uuid'];
            $query->whereHas('user', function ($q) use ($userUuid) {
                $q->where('uuid', $userUuid);
            });
        }

        if (!empty($filters['game_id'])) {
            $gameId = $filters['game_id'];
            $query->Where('game_id', $gameId);
        }

        if (
            isset($filters['date']['start'], $filters['date']['end']) &&
            $filters['date']['start'] && $filters['date']['end']
        ) {

            $startInput = $filters['date']['start'];
            $endInput   = $filters['date']['end'];

            $start = Carbon::parse($startInput);
            $end   = Carbon::parse($endInput);



            $query->whereBetween('date', [$start, $end]);
        }

        return $query;
    }


    public function calculateTotals($query, $filters): object
    {

        return $query->selectRaw("
            SUM(total_played) as total_played,
            SUM(total_loss) as total_loss,
            SUM(total_win) as total_win,
            SUM(total_loss - total_win) as app_profit
        ")->first();
    }


    public function renderInfoBoxes(Row $row, $totals): void
    {
        $row->column(3, new CustomInfoBox(__('Total Played'), 'gamepad', 'blue',  number_format($totals->total_played ?? 0, 2), '50px'));
        $row->column(3, new CustomInfoBox(__('Total Loss'), 'times-circle', 'red',  number_format($totals->total_loss ?? 0, 2), '50px'));
        $row->column(3, new CustomInfoBox(__('Total Win'), 'trophy', 'orange',  number_format($totals->total_win ?? 0, 2), '50px'));
        $row->column(3, new CustomInfoBox(__('App Profit'), 'dollar', 'green',  number_format($totals->app_profit ?? 0, 2), '50px'));
    }

    /**
     * Apply grid filters.
     */
    public function applyGridFilters(Grid $grid)
    {
        $grid->filter(function (Grid\Filter $filter) {

            $filter->expand();
            $filter->disableIdFilter();

            $filter->like('user_uuid', 'User')->placeholder('UUID');
            $filter->like('game_id', 'Game')->placeholder(' ID');
            $filter->between('date', __('Created At'))->datetime([
                'format' => 'YYYY-MM-DD HH:mm:ss',
                'locale' => 'en'
            ]);
        });
    }



    public function buildGrid(): Grid
    {
        $grid = new Grid(new CoinGameUserDailyAggregated());
        $grid->model()
            ->select([
                'coin_game_users_daily_aggregated.user_id',
                'u.uuid as user_uuid',
                'u.name as user_name',
                'up.avatar as user_avatar',
                DB::raw('SUM(coin_game_users_daily_aggregated.total_played) as total_played'),
                DB::raw('SUM(coin_game_users_daily_aggregated.total_loss) as total_loss'),
                DB::raw('SUM(coin_game_users_daily_aggregated.total_win) as total_win'),
                DB::raw('SUM(coin_game_users_daily_aggregated.app_profit) as app_profit'),
            ])
            ->from('coin_game_users_daily_aggregated')
            ->leftJoin('users as u', 'u.id', '=', 'coin_game_users_daily_aggregated.user_id')
            ->leftJoin('profiles as up', 'up.user_id', '=', 'u.id', function($join) {
                $join->whereRaw('up.id = (SELECT id FROM profiles WHERE user_id = u.id LIMIT 1)');
            })
            ->groupBy('coin_game_users_daily_aggregated.user_id', 'u.uuid', 'u.name', 'up.avatar')
            ->orderByDesc(DB::raw('SUM(coin_game_users_daily_aggregated.total_played)'));


        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();

            $filter->like('user.uuid', 'User UUID')->placeholder('UUID');
            $filter->where(function ($query) {
                if (!empty($this->input)) {
                    $query->where('coin_game_users_daily_aggregated.game_id', $this->input);
                }
            }, 'Game');

           $filter->between('date', __('Created At'))
                     ->date();
        });

        $userService = $this->userService;

        $grid->column('user_uuid', __('User'))->display(function () use ($userService) {
            return $userService->adminUserAvatar((object)[
                'id'     => $this->user_id,
                'uuid'   => $this->user_uuid,
                'name'   => $this->user_name,
                'avatar' => $this->user_avatar,
            ], withoutLevels: true);
        });


        foreach (['total_loss', 'total_win', 'app_profit'] as $field) {
            $grid->column($field, __(ucwords(str_replace('_', ' ', $field))))
                ->display(fn($v) => number_format($v))->sortable();
        }

        $grid->column('details', __('Details'))->display(function () {
            $filters = request()->only(['date', 'user_id']);
            $queryString = http_build_query($filters);

            $url = admin_url("coin-game-users/details?user_id={$this->user_id}&{$queryString}");
            return "<a href='{$url}' class='btn btn-sm btn-primary'>
                <i class='fa fa-eye'></i> " . __('Details') . "
            </a>";
        });

        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableExport();

        return $grid;
    }



    public function buildGrid_details($user_id): Grid
    {
        $grid = new Grid(new CoinGameUserAggregated());

        $grid->model()
            ->where('user_id', $user_id)
            ->with(['user', 'game', 'customGame'])
            ->select([
                'game_id',
                'game_name',
                'game_image',
                DB::raw('SUM(total_played) as total_played'),
                DB::raw('SUM(total_loss) as total_loss'),
                DB::raw('SUM(total_win) as total_win'),
                DB::raw('SUM(app_profit) as app_profit'),
                'user_id',
                'user_uuid',
                'user_name',
                'user_avatar',
            ])
            ->groupBy('game_id', 'game_name', 'game_image', 'user_id', 'user_uuid', 'user_name', 'user_avatar')
            ->orderByDesc('total_played');
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();

            $filter->like('user_uuid', 'User UUID')->placeholder('UUID');
            $filter->like('game_id', 'Game')->placeholder('ID');
            $filter->between('date', __('Created At'))->datetime([
                'format' => 'YYYY-MM-DD HH:mm:ss',
                'locale' => 'en'
            ]);
        });

        $userService = $this->userService;

        $grid->column('user_uuid', __('User'))->display(function () use ($userService) {
            return $userService->adminUserAvatar((object)[
                'id'     => $this->user_id,
                'uuid'   => $this->user_uuid,
                'name'   => $this->user_name,
                'avatar' => $this->user_avatar,
            ], withoutLevels: true);
        });

        $grid->column('game_id', __('Game'))->display(function () {
            $defaultImage = asset('images/businessman-icon.jpg');
            $url = getImagePath($this->game_image ?? @$this->customGame?->image ?? $defaultImage) ?? $defaultImage;
            if (!isImageExists($url)) $url = $defaultImage;

            $uniqueId = $this->game_id ?? 'game-unknown';
            $imageTag = handleShowImageWithTypes((string) $uniqueId, $url, 50, 50, 0);
            $id = $this->customGame->id ?? $this->game_id;
            $gameIdHtml = "game-{$this->game_id}";
            $urlLink = admin_url("all-games/{$id}");
            $name =  app()->getLocale() === 'ar' ? ($this->game_name ?? @$this->customGame?->name ?? @$this->customGame?->name_en) : ($this->game_name ?? @$this->customGame?->name_en ?? @$this->customGame?->name);

            return <<<HTML
            <a href="{$urlLink}" style="display:flex;align-items:center;gap:10px;padding:10px;text-decoration:none;color:inherit;transition:background-color 0.2s;">
                $imageTag
                <div>
                    <strong style="font-size:16px;">{$name}</strong><br>
                    <span style="font-size:13px;">
                        ID: <span id="{$gameIdHtml}">{$this->game_id}</span>
                        <button onclick="event.preventDefault();event.stopPropagation();copyToClipboard('{$gameIdHtml}')" style="background:none;border:none;cursor:pointer;margin-left:5px;font-size:13px;color:#007bff;" title="Copy ID">📝</button>
                    </span>
                </div>
            </a>
        HTML;
        });

        $grid->column('total_loss', __('Total Loss'))->display(fn($v) => number_format($v))->sortable();
        $grid->column('total_win', __('Total Win'))->display(fn($v) => number_format($v))->sortable();
        $grid->column('app_profit', __('App Profit'))->display(fn($v) => number_format($v))->sortable();

        $grid->column('details', __('Details'))->display(function () {

            $filters = request()->only(['date', 'user_id', 'game_id']);
            $queryString = http_build_query($filters);
            $url = admin_url("coin-game-users/show?user_id={$this->user_id}&game_id={$this->game_id}&{$queryString}");
            return "<a href='{$url}' class='btn btn-sm btn-primary'>
                    <i class='fa fa-eye'></i> " . __('round_details') . "
                </a>";
        });
        $grid->tools(function ($tools) {
            $tools->append('<a href="' . admin_url('coin-game-users-reports') . '" class="btn btn-sm btn-default">
                <i class="fa fa-arrow-left"></i> ' . __('Back') . '</a>');
        });
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableExport();

        return $grid;
    }



    public function buildShowAllGrid($userId, $gameId): Grid
    {
        $grid = new Grid(new CoinGameUserAll());

        $createdAt = request('date', []);
        $game_id = request('game_id', []);
        if (!empty($createdAt['start']) && !empty($createdAt['end'])) {
            $grid->model()->whereBetween('created_at', [$createdAt['start'], $createdAt['end']]);
        }

        $grid->model()
            ->with(['user', 'game', 'customGame'])
            ->selectRaw("
                game_name,
                game_image,
                game_id,
                round_id,
                SUM(CASE WHEN type = 0 THEN coins ELSE 0 END) as total_loss,
                SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) as total_win,
                MIN(created_at) as first_played,  
                MAX(created_at) as last_played    
            ")
            ->where('user_id', $userId)
            ->where('game_id', $gameId)
            ->groupBy('game_name', 'round_id', 'game_image', 'game_id')
            ->orderByDesc('last_played');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->where(function ($q) {
                $input = $this->input;
                $q->where('round_id', 'like', "%{$input}%");
            }, __('Round ID'))->placeholder(__('Round ID'));

            $filter->where(function ($q) {
                $input = $this->input;
                $q->where('game_id', 'like', "%{$input}%");
            }, __('game id'))->placeholder(__('Game ID'));

            $filter->between('created_at', __('Created At'))->datetime([
                'format' => 'YYYY-MM-DD HH:mm:ss',
                'locale' => 'en'
            ]);
        });

        $grid->column('game_name', __('Game'))->display(function () {
            $defaultImage = asset('images/businessman-icon.jpg');
            $url = getImagePath($this->game_image ?? @$this->customGame?->image) ?? $defaultImage;
            if (!isImageExists($url)) $url = $defaultImage;

            $uniqueId = $this->game_id ?? 'game-unknown';
            $imageTag = handleShowImageWithTypes((string) $uniqueId, $url, 50, 50, 0);
            $id = $this->customGame->id ?? $this->game_id;
            $gameIdHtml = "game-{$this->game_id}";
            $urlLink = admin_url("all-games/{$id}");
            $name =  app()->getLocale() === 'ar' ? ($this->game_name ?? @$this->customGame?->name ?? @$this->customGame?->name_en) : ($this->game_name ?? @$this->customGame?->name_en ?? @$this->customGame?->name);

            return <<<HTML
            <a href="{$urlLink}" style="display:flex;align-items:center;gap:10px;padding:10px;text-decoration:none;color:inherit;transition:background-color 0.2s;">
                $imageTag
                <div>
                    <strong style="font-size:16px;">{$name}</strong><br>
                    <span style="font-size:13px;">
                        ID: <span id="{$gameIdHtml}">{$this->game_id}</span>
                        <button onclick="event.preventDefault();event.stopPropagation();copyToClipboard('{$gameIdHtml}')" style="background:none;border:none;cursor:pointer;margin-left:5px;font-size:13px;color:#007bff;" title="Copy ID">📝</button>
                    </span>
                </div>
            </a>
        HTML;
        });
        $grid->column('round_id', __('Round ID'))->sortable();
        $grid->column('total_loss', __('Total Loss'))->display(function ($v) {
            return "<span style='color:red; font-weight:bold;'>" . number_format($v) . "</span>";
        })->sortable();

        $grid->column('total_win', __('Total Win'))->display(function ($v) {
            return "<span style='color:green; font-weight:bold;'>" . number_format($v) . "</span>";
        })->sortable();
        $grid->column('first_played', __('Start Date'))->display(fn($v) => $v)->sortable();
        $grid->column('last_played', __('End Date'))->display(fn($v) => $v)->sortable();

        $grid->tools(function ($tools) use ($userId) {
            $tools->append('<a href="' . admin_url("coin-game-users/details?user_id={$userId}") . '" class="btn btn-sm btn-default">
                <i class="fa fa-arrow-left"></i> ' . __('Back') . '</a>');
        });
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableActions();

        return $grid;
    }
}
