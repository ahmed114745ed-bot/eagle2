<?php

namespace App\Admin\Controllers;

use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\AllGame;
use App\Models\CoinGameUser;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\InfoBox;
use App\Services\AppFeatureService;
use App\Admin\Controllers\MainController;

class AllGameController extends MainController
{
    protected $title = 'games';
    public $permission_name = 'games';

    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("game");
    }
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

    //  public function show($id, Content $content)
    // {
    //     return parent::show($id, $content
    //         ->title(trans('Games'))
    //         ->body($this->detail($id)));
    // }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    // public function edit($id, Content $content)
    // {
    //     return parent::edit($id, $content
    //         ->title(trans('Games'))
    //         ->body($this->form()->edit($id)));
    // }

    // public function create(Content $content)
    // {
    //     return parent::create($content
    //         ->title(trans('Games'))
    //         ->body($this->form()));
    // }

    protected function grid()
    {
        $grid = new Grid(new AllGame());

        $grid->column('id', __('Id'));
        $grid->column('is_enable', __('enable'))->switch();
        $grid->column('custom_id', __('custom_id'));
        $grid->column('name', __('name_ar'));
        $grid->column('name_en', __('name_en'));
        $grid->column('type', __('type'))->using([0 => __('joy'), 1 => __('OX'), 2 => __('Baishun')]);
        $grid->column('url', __('Full Url'));
        $grid->column('mini_url', __('Mini Url'));
        $grid->column('image', __('Image'))->image('', 50);
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

        $form->text('custom_id', __('custom_id'));
        $form->textarea('name', __('name_ar'))->required();
        $form->textarea('name_en', __('name_en'))->required();
        $form->url('url', __('Full Url'));
        $form->select('type', __('type'))->options(
            [
                0 => __('joy'),
                1 => __('OX'),
                2 => __('Baishun')
            ]
        );
        $form->url('mini_url', __('Mini Url'));
        $form->image('image', __('Image'));
        $form->text('hight_image', __('hight_image'));
        $form->switch('is_enable', __('enable'));
        $form->select('in_room', __('in_room'))->options(
            [
                0 => __('mini'),
                1 => __('full'),

            ]
        )->default(0);
        $form->text('hight', __('hight'));

        return $form;
    }
}
