<?php

namespace App\Services\Admin;

use App\Admin\Widgets\CustomInfoBox;
use App\Models\CoinGameUserAll;
use App\Models\User;
use App\Models\AllGame;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\InfoBox;
use App\Admin\Services\UserService;
use Encore\Admin\Facades\Admin;


class CoinGameUserService
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Normalize request filters.
     */
    public function normalizeFilters(array $filters): array
    {
        $mapping = [
            '696042db2ff29ffcb1c5eee90445cad6' => 'user',
            '91de9d78d4edb6f3e2bdd00e5db2e8a3' => 'game',
            'created_at' => 'created_at',
        ];

        $normalized = [];
        foreach ($filters as $key => $value) {
            if (isset($mapping[$key])) {
                $normalized[$mapping[$key]] = $value;
            }
        }

        return $normalized;
    }

    /**
     * Apply filters to query.
     */
    public function applyFilters($query, array $filters)
    {
        if (!empty($filters['user'])) {
            $user = $filters['user'];
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('name', 'like', "%{$user}%")
                    ->orWhere('uuid', 'like', "%{$user}%")
                    ->orWhere('id', 'like', "%{$user}%");
            });
        }

        if (!empty($filters['game'])) {
            $game = $filters['game'];
            $query->whereHas('game', fn($q) => $q->where('name', 'like', "%{$game}%"));
        }

        if (!empty($filters['created_at']['start']) && !empty($filters['created_at']['end'])) {
            $query->whereBetween('created_at', [
                $filters['created_at']['start'],
                $filters['created_at']['end']
            ]);
        }

        return $query;
    }

    /**
     * Calculate totals.
     */
    public function calculateTotals($query)
    {
        return $query->selectRaw("
            SUM(coins) as total_played,
            SUM(CASE WHEN type = 0 THEN coins ELSE 0 END) as total_loss,
            SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) as total_win,
            (SUM(coins) - SUM(CASE WHEN type = 1 THEN coins ELSE 0 END)) as app_profit
        ")->first();
    }

    /**
     * Render InfoBoxes row.
     */
    public function renderInfoBoxes(Row $row, $totals): void
    {
        //     $row->column(6, new InfoBox(__('Total Played'), 'gamepad', 'blue', '', number_format($totals->total_played ?? 0, 2) ) );
        //     $row->column(6, new InfoBox(__('Total Loss'), 'times-circle', 'red', '', number_format($totals->total_loss ?? 0, 2)));
        //     $row->column(6, new InfoBox(__('Total Win'), 'trophy', 'orange', '', number_format($totals->total_win ?? 0, 2)));
        //     $row->column(6, new InfoBox(__('App Profit'), 'dollar', 'green', '', number_format($totals->app_profit ?? 0, 2)));
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

            $filter->where(function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', "%{$this->input}%")
                        ->orWhere('uuid', 'like', "%{$this->input}%")
                        ->orWhere('id', 'like', "%{$this->input}%");
                });
            }, __('user'))->placeholder(__('UUID'));

            $filter->where(function ($query) {
                $query->whereHas('game', function ($q) {
                    $q->where('name', 'like', "%{$this->input}%")
                        ->orWhere('id', 'like', "%{$this->input}%");
                });
            }, __('game'))->placeholder(__('name') . __('---') . __('id'));

            $filter->between('created_at', __('Created At'))->datetime([
                'format' => 'YYYY-MM-DD HH:mm:ss',
                'locale' => 'en'
            ]);
        });
    }

    /**
     * Build main grid.
     */
    public function buildGrid(): Grid
    {
        $grid = new Grid(new CoinGameUserAll());

        $grid->model()
            ->with([
                'user:id,name,uuid',
                'user.profile:id,user_id,avatar',
                'user.packs',
                'game'
            ])
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

        $this->applyGridFilters($grid);

        $grid->column('user_id', __('user'))->display(function () {
            $user = $this->user;
            if (!$user) return __('No User');
            return app(UserService::class)->adminUserAvatar($user, withoutLevels: true);
        });

        $grid->column('game_id', __('Game'))->display(function () {
            $game = $this->game; // لم يعد يحتاج find()
            if (!$game) return '-';

            $defaultImage = asset('images/businessman-icon.jpg');
            $url = getImagePath($game->image) ?? $defaultImage;
            if (!isImageExists($url)) $url = $defaultImage;

            $imageTag = handleShowImageWithTypes($game->id, $url, 50, 50, 0);
            $gameIdHtml = "game-{$game->id}";
            $urlLink = admin_url("all-games/{$game->id}");

            return <<<HTML
            <a href="{$urlLink}" style="display:flex;align-items:center;gap:10px;padding:10px;text-decoration:none;color:inherit;transition:background-color 0.2s;">
                $imageTag
                <div>
                    <strong style="font-size:16px;">{$game->name}</strong><br>
                    <span style="font-size:13px;">
                        ID: <span id="{$gameIdHtml}">{$game->id}</span>
                        <button onclick="event.preventDefault();event.stopPropagation();copyToClipboard('{$gameIdHtml}')" style="background:none;border:none;cursor:pointer;margin-left:5px;font-size:13px;color:#007bff;" title="Copy ID">📝</button>
                    </span>
                </div>
            </a>
        HTML;
        });
        $grid->column('total_loss', __('Total Loss'))->display(fn($v) => number_format($v));
        $grid->column('total_win', __('Total Win'))->display(fn($v) => number_format($v));
        $grid->column('app_profit', __('App Profit'))->display(fn($v) => number_format($v));
        if ( Admin::user()->can('details-switch-coin-game-users-report') || Admin::user()->can('*')) {

            $grid->column('details', __('Details'))->display(function () {

                $filters = request()->only(['created_at', 'user_id', 'game_id']);
                $queryString = http_build_query($filters);
                $url = admin_url("coin-game-users/show?user_id={$this->user_id}&game_id={$this->game_id}&{$queryString}");
                return "<a href='{$url}' class='btn btn-sm btn-primary'>
                        <i class='fa fa-eye'></i> " . __('round_details') . "
                    </a>";
            });
        }


        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableExport();

        return $grid;
    }

    /**
     * Build detailed grid for a user/game.
     */
    public function buildShowAllGrid($userId, $gameId): Grid
    {
        $grid = new Grid(new CoinGameUserAll());

        $createdAt = request('created_at', []);
        if (!empty($createdAt['start']) && !empty($createdAt['end'])) {
            $grid->model()->whereBetween('created_at', [$createdAt['start'], $createdAt['end']]);
        }
        $grid->model()
            ->selectRaw("
                round_id,
                SUM(CASE WHEN type = 0 THEN coins ELSE 0 END) as total_loss,
                SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) as total_win,
                MIN(created_at) as first_played,   -- يمكن أخذ أول وقت للروند للعرض
                MAX(created_at) as last_played     -- أو آخر وقت للروند
            ")
            ->where('user_id', $userId)
            ->where('game_id', $gameId)
            ->groupBy('round_id')
            ->orderByDesc('round_id');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->where(function ($q) {
                $input = $this->input;
                $q->where('round_id', 'like', "%{$input}%");
            }, __('Round ID'))->placeholder(__('Round ID'));

            $filter->between('created_at', __('Created At'))->datetime([
                'format' => 'YYYY-MM-DD HH:mm:ss',
                'locale' => 'en'
            ]);
        });

        $grid->column('round_id', __('Round ID'))->sortable();
        $grid->column('total_loss', __('Total Loss'))->display(function ($v) {
            return "<span style='color:red; font-weight:bold;'>" . number_format($v) . "</span>";
        });

        $grid->column('total_win', __('Total Win'))->display(function ($v) {
            return "<span style='color:green; font-weight:bold;'>" . number_format($v) . "</span>";
        });
        $grid->column('first_played', __('Start Date'))->display(fn($v) => $v);
        $grid->column('last_played', __('End Date'))->display(fn($v) => $v);

        $grid->tools(function ($tools) {
            $tools->append('<a href="' . admin_url('coin-game-users-reports') . '" class="btn btn-sm btn-default">
                <i class="fa fa-arrow-left"></i> ' . __('Back') . '</a>');
        });
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableActions();

        return $grid;
    }
}
