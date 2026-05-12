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

        // Import JSON button
        $importUrl = url(config('admin.route.prefix') . '/all-games/import-json');
        $csrf = csrf_token();
        $grid->tools(function ($tools) use ($importUrl, $csrf) {
            $tools->append(
                '<div class="btn-group pull-right" style="margin-right: 10px;">'
                . '<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#importJsonModal">'
                . '<i class="fa fa-upload"></i>&nbsp; Import JSON'
                . '</button></div>'
                . '<div class="modal fade" id="importJsonModal" tabindex="-1" role="dialog">'
                . '<div class="modal-dialog modal-lg" role="document"><div class="modal-content">'
                . '<form id="importJsonForm" method="POST" action="' . $importUrl . '">'
                . '<input type="hidden" name="_token" value="' . $csrf . '">'
                . '<div class="modal-header" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;">'
                . '<button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>'
                . '<h4 class="modal-title"><i class="fa fa-upload"></i> Import Games from JSON</h4></div>'
                . '<div class="modal-body">'
                . '<div class="form-group"><label>JSON URL</label>'
                . '<input type="url" name="json_url" class="form-control" value="https://sas2suc3.leadercc.com/games/prod_game_list.json" placeholder="https://..." style="border-radius:8px;">'
                . '<small class="text-muted">Enter the URL that returns a JSON array of games</small></div>'
                . '<div class="form-group"><label>Or Paste JSON Data</label>'
                . '<textarea name="json_data" id="jsonDataInput" class="form-control" rows="10" placeholder=\'[{"gameId":"101","name":"Game","title":"Title","full_url":"...","hd_url":"...","half_url":"..."}]\' style="font-family:monospace;font-size:13px;border-radius:8px;"></textarea>'
                . '<div id="jsonValidationMsg" style="margin-top:8px;display:none;"></div></div>'
                . '<div class="form-group"><label>Game Provider Type</label>'
                . '<select name="game_type" class="form-control" style="border-radius:8px;">'
                . '<option value="0">Joyplay</option><option value="1">OX</option><option value="2">Bytesun</option>'
                . '<option value="3">Quantum Nexus</option><option value="4">Zero Games</option></select></div></div>'
                . '<div class="modal-footer">'
                . '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>'
                . '<button type="submit" id="importJsonSubmitBtn" class="btn btn-success"><i class="fa fa-upload"></i> Import</button>'
                . '</div></form></div></div></div>'
                . '<script>document.addEventListener("DOMContentLoaded",function(){'
                . 'var ta=document.getElementById("jsonDataInput"),msg=document.getElementById("jsonValidationMsg");'
                . 'if(ta){ta.addEventListener("input",function(){var v=this.value.trim();if(!v){msg.style.display="none";return;}'
                . 'try{var p=JSON.parse(v);if(!Array.isArray(p)){msg.innerHTML="<span style=color:#dc2626><i class=fa\\ fa-times-circle></i> Must be array</span>";msg.style.display="block";return;}'
                . 'msg.innerHTML="<span style=color:#059669><i class=fa\\ fa-check-circle></i> Valid - "+p.length+" games</span>";msg.style.display="block";}'
                . 'catch(e){msg.innerHTML="<span style=color:#dc2626><i class=fa\\ fa-times-circle></i> "+e.message+"</span>";msg.style.display="block";}});}'
                . 'var f=document.getElementById("importJsonForm");if(f){f.addEventListener("submit",function(){'
                . 'var b=document.getElementById("importJsonSubmitBtn");b.disabled=true;b.innerHTML="<i class=fa\\ fa-spinner\\ fa-spin></i> Importing...";});}'
                . '});</script>'
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
