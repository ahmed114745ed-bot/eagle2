<?php

namespace App\Services\Admin;

use App\Admin\Widgets\CustomInfoBox;
use App\Models\CoinGameUserAggregated;
use App\Models\CoinGameUserAll;
use App\Models\CoinGameUserDailyAggregated;
use App\Models\User;
use App\Models\AllGame;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\InfoBox;
use App\Admin\Services\UserGameService;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Carbon;


class CoinGameUserService
{
    protected $userService;

    public function __construct(UserGameService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Normalize request filters.
     */


    /**
     * Apply filters to aggregated query (الفيو).
     */
    public function applyFilters($query,  array $filters)
    {
        if (!empty($filters['user_uuid'])) {
            $userId = $filters['user_uuid'];
            $query->Where('user_uuid', 'like', "%{$userId}%");
 
        }
    
        if (!empty($filters['game_id'])) {
            $gameId = $filters['game_id'];
            $query->Where('game_id', $gameId);
        
        }
    
        if (!empty($filters['date']['start']) && !empty($filters['date']['end'])) {
            $start = Carbon::parse($filters['date']['start'])->startOfDay();
            $end   = Carbon::parse($filters['date']['end'])->endOfDay();
        
            $query->whereBetween('date', [$start, $end]);
        }
    
        return $query;
    }

    /**
     * Calculate totals from aggregated view.
     */
    public function calculateTotals($query ,$filters): object
    {
 
        $query = $this->applyFilters($query, $filters);

        return $query->selectRaw("
            SUM(total_played) as total_played,
            SUM(total_loss) as total_loss,
            SUM(total_win) as total_win,
            SUM(total_loss - total_win) as app_profit
        ")->first();
    }

    /**
     * Render InfoBoxes row.
     */
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
            // فلتر التاريخ
            $filter->between('date', __('Created At'))->datetime([
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
        $grid = new Grid(new CoinGameUserDailyAggregated());
        $grid->model()
        ->selectRaw("
            coin_game_users_daily_aggregated.*,
            u.uuid as user_uuid,
            u.name as user_name,
            up.avatar as user_avatar,
            g.name as game_name,
            g.image as game_image
        ")
        ->leftJoin('users as u', 'u.id', '=', 'coin_game_users_daily_aggregated.user_id')
        ->leftJoin('profiles as up', 'up.user_id', '=', 'u.id')
        ->leftJoin('all_games as g', 'g.id', '=', 'coin_game_users_daily_aggregated.game_id')
        ->orderByDesc('coin_game_users_daily_aggregated.total_played');
    
    
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
    
        // عرض المستخدم
        $grid->column('user_uuid', __('User'))->display(function () use ($userService) {
            return $userService->adminUserAvatar((object)[
                'id'     => $this->user_id,
                'uuid'   => $this->user_uuid,
                'name'   => $this->user_name,
                'avatar' => $this->user_avatar,
            ], withoutLevels: true);
        });
    
        // عرض اللعبة
        $grid->column('game_id', __('Game'))->display(function () {
            $defaultImage = asset('images/businessman-icon.jpg');
            $url = getImagePath($this->game_image) ?? $defaultImage;
            if (!isImageExists($url)) $url = $defaultImage;
    
            $uniqueId = $this->game_id ?? 'game-unknown';
            $imageTag = handleShowImageWithTypes((string) $uniqueId, $url, 50, 50, 0);
            
            $gameIdHtml = "game-{$this->game_id}";
            $urlLink = admin_url("all-games/{$this->game_id}");
    
            return <<<HTML
            <a href="{$urlLink}" style="display:flex;align-items:center;gap:10px;padding:10px;text-decoration:none;color:inherit;transition:background-color 0.2s;">
                $imageTag
                <div>
                    <strong style="font-size:16px;">{$this->game_name}</strong><br>
                    <span style="font-size:13px;">
                        ID: <span id="{$gameIdHtml}">{$this->game_id}</span>
                        <button onclick="event.preventDefault();event.stopPropagation();copyToClipboard('{$gameIdHtml}')" style="background:none;border:none;cursor:pointer;margin-left:5px;font-size:13px;color:#007bff;" title="Copy ID">📝</button>
                    </span>
                </div>
            </a>
        HTML;
        });
    
        // أعمدة الأرقام
        $grid->column('total_loss', __('Total Loss'))->display(fn($v) => number_format($v));
        $grid->column('total_win', __('Total Win'))->display(fn($v) => number_format($v));
        $grid->column('app_profit', __('App Profit'))->display(fn($v) => number_format($v));
    
        $grid->column('details', __('Details'))->display(function () {

            $filters = request()->only(['date', 'user_id', 'game_id']);
            $queryString = http_build_query($filters);
            $url = admin_url("coin-game-users/show?user_id={$this->user_id}&game_id={$this->game_id}&{$queryString}");
            return "<a href='{$url}' class='btn btn-sm btn-primary'>
                    <i class='fa fa-eye'></i> " . __('round_details') . "
                </a>";
        });
    
       
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

        $createdAt = request('date', []);
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
