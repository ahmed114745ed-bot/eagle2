<?php

namespace Utd\Events\Http\Controllers\web;

use App\Admin\Controllers\MainController;
use App\Models\Gift;
use App\Models\Ware;
use App\Selectables\Gifts;
use App\Services\AppFeatureService;
use App\Support\PackageHelper;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Utd\Badge\Entities\Badge;
use Utd\Events\Entities\Reward;
use Utd\Events\Entities\WeeklyStar;
use Utd\Vip\Entities\OVip;

class EventPeriodController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'event-period';

    public $hiddenColumns = [];

    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable('period_event');
    }

    public function index(Content $content)
    {
        return parent::index(
            $content
                ->title(__('event period'))
                ->row(function (Row $row) {

                    //                $row->column(3, view('event_settings'));

                    $row->column(12, function (Column $column) {
                        $column->row(view('event_taps'));
                        $column->row($this->grid2());
                        $column->row($this->grid());
                    });
                })
        );
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('event period'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('event period'))
            ->body($this->form()));
    }

    public function store()
    {
        $data = request()->all();
        //        $data['start_date'] = UserCommon::convertArabicNumbers(request()['start_date']);
        $data['type'] = 'event_period';
        request()->merge($data);

        return parent::store();
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->row('<h3>'.__('weekly Star').'</h3>')->row(function ($row) use ($id) {
                $row->column(12, $this->weeklyStar($id));
            })
            ->row('<h3>'.__('gifts').'</h3>')->row(function ($row) use ($id) {
                $row->column(12, $this->giftList($id));
            })
            ->row('<h3>'.__('Rewards').'</h3>')->row(function ($row) use ($id) {
                $row->column(12, $this->rewardList($id));
            }));
    }

    protected function grid2()
    {
        $form = new Box();
        $form->view('events::EventPeriodView');

        return $form;
    }

    protected function grid()
    {
        $grid = new Grid(new WeeklyStar());
        $grid->model()->whereType('event_period');
        $grid->column('id', __('Id'));
        $grid->column('start_date_local', __('Start Date'));
        $grid->column('end_date_local', __('End Date'));
        $grid->column('created_at', __('Created at'));
        $grid->column(__('procedures'))->display(function () {
            // توليد الروابط
            $url1 = url('admin/weekly-events-gift/'.$this->id);
            $url2 = url('admin/weekly-events-gift/'.$this->id);
            $url3 = url('admin/weekly-events-gift/'.$this->id);

            // إنشاء أزرار HTML
            $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>".__('winner first gifts').'</a>';
            $button2 = "<a href='{$url2}' class='btn btn-sm btn-info'>".__('winner second gifts').'</a>';

            $button3 = "<a href='{$url3}' class='btn btn-sm btn-info'>".__('winner third gifts').'</a>';

            // دمج الأزرار في سلسلة واحدة وإرجاعها
            return $button1.' '.$button2.' '.$button3;
        });

        $check_event_period = WeeklyStar::where('type', 'event_period')->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
        if ($check_event_period !== null) {
            $grid->disableCreateButton();
        }
        $this->extendGrid($grid);

        return $grid;
    }

    protected function form()
    {
        $form = new Form(new WeeklyStar);
        $this->disableFormTools($form);

        $form->display(__('admin.ID'));
        $form = new Form(new WeeklyStar());
        $form->hidden('type', 'Type')->default('event_period');
        $form->date('start_date', __('Start Date'))->default(date('Y-m-d'));
        $form->date('end_date', __('End Date'))->default(date('Y-m-d'));
        $form->belongsToMany('gifts', Gifts::class)
            ->rules('required|array|size:3', [
                'size' => __('choose only 3 gifts.'),
            ]);

        return $form;
    }

    protected function detail($id)
    {
        $show = new Show(WeeklyStar::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('start_date', __('Start date'));
        $show->field('type', __('Type'))->as(function ($type) {
            return $type === 1 ? 'gifts' : ($type === 0 ? 'charges' : 'PK');
        });

        $this->extendShow($show);

        return $show;
    }

    protected function weeklyStar($id)
    {

        $grid = new Grid(new WeeklyStar());
        $grid->model()->where('id', $id);

        $grid->column('start_date', __('Start date'));
        $grid->column('end_date', __('End date'));
        $grid->column('type', __('Type'))->display(function ($type) {

            return $type === 1 ? 'gifts' : ($type === 0 ? 'charges' : 'PK');
        });

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableExport();

        return $grid;
    }

    protected function rewardList($id)
    {

        $grid = new Grid(new Reward);
        $grid->model()->where('weekly_star_id', $id);

        $grid->column('level', trans('level'));
        $grid->column('type', trans('type'))->display(function ($type) {

            return $type === 'coins' ? 'coins' : ($type === 'ware' ? 'ware' : ($type === 'vip' ? 'vip' : 'achievement'));
        });
        $grid->column('target', trans('target'))->display(function ($target) {

            if ($this->type === 'coins') {
                return $target;
            }
            if ($this->type === 'ware') {
                $ware = Ware::find($target);

                return @$ware->name ?? '';
            }
            if ($this->type === 'vip') {
                $vip = PackageHelper::isInstalled('vip') ? OVip::find($target) : null;

                return @$vip->name ?? '';
            }
            if ($this->type === 'badge') {
                $vip = PackageHelper::isInstalled('badge') ? Badge::find($target) : null;

                return $vip ? (@$vip->name ?? '') : '';
            }
            $value = getDriverUrl().$target;

            return "<img src='$value' width='80' height='80'>";

        });

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableExport();

        return $grid;
    }

    protected function giftList($id)
    {
        $weeklyEvent = WeeklyStar::find($id);
        $giftIds = $weeklyEvent->gifts->pluck('id')->toArray();
        $grid = new Grid(new Gift);
        $grid->model()->whereIn('id', $giftIds);
        $grid->name(__('name'));
        $grid->column('img', trans('image'))->image('', '30');

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableExport();

        return $grid;
    }
}
