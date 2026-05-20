<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\MainController;
use App\Models\AllGame;
use App\Models\CoinGameUser;
use App\Models\GameProviderSetting;
use App\Services\AppFeatureService;
use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AllGameController extends MainController
{
    protected $title = 'games';
    public $permission_name = 'games';

    
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('Games'))
            ->body($this->detail($id)));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('Games'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Games'))
            ->body($this->form()));
    }
    public function index(Content $content)
    {
        if (request("from_date") != null && request("to_date") != null) {
            $from_date = request("from_date");
            $to_date = request("to_date");
        } else {
            $from_date  = now()->startOfMonth();
            $to_date    = now()->endOfMonth();
        }
        $results = CoinGameUser::select(DB::raw("
            SUM(CASE WHEN type = 0 THEN coins ELSE 0 END) AS total_lose,
            SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) AS total_earn
        "))
            ->whereDate('created_at', '>=', $from_date)
            ->whereDate('created_at', '<=', $to_date)
            ->first();


        $total_lose = $results->total_lose;
        $total_earn = $results->total_earn;
        $result = $total_lose - $total_earn;
        return parent::index($content
            ->title('Dashboard')
            ->description('Description')
            ->row(function (Row $row) use ($result) {
                $row->column(6, $this->grid2());
                $row->column(6, new InfoBox(__('Game profits'), 'gamepad', 'primary', null, $result));
            })

            ->row($this->grid()));
    }
    protected function grid2()
    {
        $form = new Box();
        $form->view('admin.grid.Form.allGameForm');

        return $form;
    }

   

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    
    protected function grid()
    {
        $grid = new Grid(new AllGame());

        $grid->column('id', __('Id'));
        $grid->column('is_enable', __('enable'))->switch();
        $grid->column('custom_id', __('custom_id'));
        $grid->column('name', __('name_ar'));
        $grid->column('name_en', __('name_en'));
        $grid->column('type', __('type'))->using([
            0 => __('Joyplay'),
            1 => __('OX'),
            2 => __('Bytesun'),
            3 => __('Quantum Nexus'),
            4 => __('Zero Games'),
        ]);
        $grid->column('url', __('Full Url'));
        $grid->column('mini_url', __('Mini Url'));
        $grid->column('image', __('Image'))->image('', 50);

        // Import JSON button + modal (rendered from Blade view)
        $importUrl = url(config('admin.route.prefix') . '/all-games/import-json');
        $grid->tools(function ($tools) use ($importUrl) {
            $modalHtml = view('admin.grid.Form.importGamesModal', compact('importUrl'))->render();
            $tools->append(
                '<div class="btn-group pull-right" style="margin-right: 10px;">'
                . '<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#importJsonModal" style="border-radius:8px;font-weight:600;padding:7px 16px;box-shadow:0 2px 8px rgba(16,185,129,0.25);">'
                . '<i class="fa fa-cloud-download"></i>&nbsp; Import JSON'
                . '</button></div>'
                . $modalHtml
            );
        });

        $this->extendGrid($grid);
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(AllGame::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('custom_id', __('custom_id'));
        $show->field('name', __('name_ar'));
        $show->field('name_en', __('name_en'));
        $show->field('url', __('Url'));
        $show->field('image', __('Image'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AllGame());
        $this->disableFormTools($form);
        $form->text('custom_id', __('custom_id'));
        $form->textarea('name', __('name_ar'))->required();
        $form->textarea('name_en', __('name_en'))->required();
        $form->url('url', __('Full Screen Link'));
        $form->select('type', __('type'))->options(
            [
                0 => __('Joyplay'),
                1 => __('OX'),
                2 => __('Bytesun'),
                3 => __('Quantum Nexus'),
                4 => __('Zero Games'),
            ]
        );
        $form->url('mini_url', __('Half Screen Link'));
        $form->url('hd_url', __('HD Half Screen Link'));
        $form->image('image', __('Image'));
        $form->text('hight_image', __('hight_image'));
        $form->switch('is_enable', __('enable'));
        $form->select('in_room', __('in_room'))->options(
            [
                0 => __('Half Screen'),
                1 => __('Full Screen'),
                2 => __('HD Half Screen'),
            ]
        )->default(0);
        $form->text('hight', __('hight'));

        return $form;
    }


    /**
     * Import games from JSON URL or pasted JSON data.
     */
    public function importJson(Request $request)
    {
        $gameType = (int) $request->input('game_type', 2);
        $jsonUrl = $request->input('json_url');
        $jsonData = $request->input('json_data');
        $games = null;

        // Priority: pasted JSON > URL
        if (!empty(trim($jsonData ?? ''))) {
            $games = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                admin_toastr('Invalid JSON format: ' . json_last_error_msg(), 'error');
                return back();
            }
        } elseif (!empty(trim($jsonUrl ?? ''))) {
            try {
                $response = Http::withoutVerifying()->timeout(15)->get($jsonUrl);
                if (!$response->successful()) {
                    admin_toastr('Failed to fetch JSON from URL. HTTP Status: ' . $response->status(), 'error');
                    return back();
                }
                $games = $response->json();
            } catch (\Exception $e) {
                Log::error('[AllGameController] importJson fetch error: ' . $e->getMessage());
                admin_toastr('Error fetching URL: ' . $e->getMessage(), 'error');
                return back();
            }
        } else {
            admin_toastr('Please provide a JSON URL or paste JSON data', 'error');
            return back();
        }

        if (!is_array($games) || empty($games)) {
            admin_toastr('JSON must be a non-empty array of game objects', 'error');
            return back();
        }

        // Validate required fields
        foreach ($games as $index => $game) {
            if (!isset($game['gameId']) || !isset($game['name']) || !isset($game['full_url'])) {
                admin_toastr("Game at index {$index} is missing required fields (gameId, name, full_url)", 'error');
                return back();
            }
        }

        // Import games using updateOrCreate
        $imported = 0;
        $updated = 0;

        foreach ($games as $game) {
            $data = [
                'custom_id' => $game['gameId'],
                'name'      => $game['title'] ?? $game['name'],
                'name_en'   => $game['name'],
                'url'       => $game['full_url'] ?? null,
                'hd_url'    => $game['hd_url'] ?? null,
                'mini_url'  => $game['half_url'] ?? null,
                'type'      => $gameType ?? 2,
            ];

            $existing = AllGame::where('custom_id', $game['gameId'])->first();

            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                $data['is_enable'] = 1;
                AllGame::create($data);
                $imported++;
            }
        }

        admin_toastr("Import completed! {$imported} new games added, {$updated} games updated.", 'success');
        return redirect(url(config('admin.route.prefix') . '/all-games'));
    }

    public function gameSettings(Request $request)
    {
        //  dd($request->all());
        $gameSetting = GameProviderSetting::updateOrCreate(
            ['provider_code' => $request->provider_code],
            [
                'provider_name' => $request->provider_name,
                'app_key'       => $request->app_key,
                'app_id'        => $request->app_id,
                'channel'       => $request->channel,
                'gsp'           => $request->gsp,
                'is_active'     => $request->active,

            ]
        );

        // 🔥 Clear old cache
        Cache::forget('game_provider_' . $request->provider_code);

        // 🔥 Store fresh data in cache
        Cache::put(
            'game_provider_' . $request->provider_code,
            $gameSetting,
        );

        $redirectUrl = url(config('admin.route.prefix') . '/settings');

        if ($request->has('current_tab')) {
            $redirectUrl .= '?tab=' . $request->current_tab;
            if ($request->has('inner_tab_type')) {
                $redirectUrl .= '&type=' . $request->inner_tab_type;
            }
        } elseif ($request->has('redirect_to')) {
            return Redirect::to($request->redirect_to);
        }

        return redirect($redirectUrl);
    }
}
