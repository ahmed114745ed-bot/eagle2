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
class CoinGameUserAllController extends AdminController
{
    use HasResourceActions;

    protected $permission_name = 'coin-game-users';

    public function index(Content $content)
    {
        $filters = $this->normalizeFilters(request()->all());
    
        $query = $this->applyFilters(CoinGameUserAll::query(), $filters);
    
        $totals = $this->calculateTotals($query);
    
        return $content
            ->title(__('coin_game_users'))
            ->description(__('coin_game_users_description'))
            ->row(fn(Row $row) => $this->renderInfoBoxes($row, $totals))
            ->row(fn($row) => $row->column(12, $this->grid()));
    }


    public function showAll(Content $content, Request $request)
    {
        $userId = $request->get('user_id');
        $gameId = $request->get('game_id');
    
        $grid = $this->buildShowAllGrid($userId, $gameId);
    
        return $content
            ->title(__('round_details'))
            ->description(__('round_details') . " | User: {$userId} | Game: {$gameId}")
            ->body($grid);
    }









    
    /**
     * Map request filters to normalized keys.
     */
    protected function normalizeFilters(array $filters): array
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
    protected function applyFilters($query, array $filters)
    {
        if (!empty($filters['user'])) {
            $user = $filters['user'];
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('name', 'like', "%{$user}%")
                  ->orWhere('uuid', 'like', "%{$user}%");
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
    protected function calculateTotals($query)
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
    protected function renderInfoBoxes(Row $row, $totals): void
    {
        $row->column(3, new InfoBox(__('Total Played'), 'gamepad', 'blue', '', truncateAndTrim($totals->total_played ?? 0, 2) . ' 🎮'));
        $row->column(3, new InfoBox(__('Total Loss'), 'times-circle', 'red', '', truncateAndTrim($totals->total_loss ?? 0, 2) . ' ❌'));
        $row->column(3, new InfoBox(__('Total Win'), 'trophy', 'orange', '', truncateAndTrim($totals->total_win ?? 0, 2) . ' 🏆'));
        $row->column(3, new InfoBox(__('App Profit'), 'dollar', 'green', '', truncateAndTrim($totals->app_profit ?? 0, 2) . ' 💰'));
    }
    


    
    private function buildShowAllGrid($userId, $gameId): Grid
    {
        $grid = new Grid(new CoinGameUserAll());
    
        $grid->model()
            ->where('user_id', $userId)
            ->where('game_id', $gameId)
            ->orderByDesc('id');
    
        $this->configureGridColumns($grid);
        $this->configureGridOptions($grid);
    
        return $grid;
    }
    
    private function configureGridColumns(Grid $grid): void
    {
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
    }
    
    private function configureGridOptions(Grid $grid): void
    {
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableActions();
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
                      ->orWhere('uuid', 'like', "%{$this->input}%")
                      ->orWhere('id', 'like', "%{$this->input}%");
                });
            }, __('user'))->placeholder(__('UUID'));

            $filter->where(function ($query) {
                $query->whereHas('game', function ($q) {
                    $q->where('name', 'like', "%{$this->input}%")
                    ->orWhere('id', 'like', "%{$this->input}%");
                });
            }, __('game'))->placeholder(__('name').__('---').__('id'));

            $filter->between('created_at', __('Created At'))->datetime([
                'format' => 'YYYY-MM-DD HH:mm:ss', 
                'locale' => 'en' 
            ]);
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
