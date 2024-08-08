<?php

namespace Modules\Events\Http\Controllers\web;

use App\Admin\Controllers\MainController;
use App\Models\Gift;
use App\Models\OVip;
use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

;
use App\Selectables\Gifts;
use App\Helpers\UserCommon;
use App\Services\AppFeatureService;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Layout\Content;
use Modules\Events\Entities\PkEvent;
use Modules\Events\Entities\PkReward;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\HasResourceActions;

class PkEventController extends MainController
{

    use HasResourceActions;
   
    protected $title = 'PkEvent';

    public $permission_name = 'pk-event';
    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("pk_event");
    }

    public function index ( Content $content )
    {
        return $content
            ->title(__($this->title))
            ->row(function (Row $row) {
                $row->column(12, $this->grid2());
            })
            ->row(function (Row $row) {
                $row->column(12, $this->grid());
            });
    }

    protected function grid2()
    {
        $form = new Box();
        $form->view('admin.grid.users.PkEventRoleView');

        return $form;
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PkEvent());

        $grid->column('id', __('Id'));
        $grid->column('start_date_local', __('Start Date'));
        $grid->column('end_date_local', __('End Date'));
        $grid->column('created_at', __('Created at'));
        $grid->column('الاجرائات')->display(function () {
            // توليد الروابط
            $url1 = url('admin/pk-events-gift/pk-star/' . $this->id);
            $url2 = url('admin/pk-events-gift/pk-king/' . $this->id);
            $url3 = url('admin/pk-events-gift/pk-room/' . $this->id);

            // إنشاء أزرار HTML
            $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>  النجم PK  هداية </a>";
            $button2 = "<a href='{$url2}' class='btn btn-sm btn-danger'> الملك PK  هداية  </a>";
            $button3 = "<a href='{$url3}' class='btn btn-sm btn-primary'> الغرفة pk هداية  </a>";

            // دمج الأزرار في سلسلة واحدة وإرجاعها
            return $button1 . ' ' . $button2 . ' ' . $button3;
        });

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
        $show = new Show(PkEvent::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('start_date', __('Start Date'));
        $show->field('end_date', __('End Date'));
        $this->extendShow($show);
        return $show;
    }


    public function store()
    {
        $data = request()->all();
        $data['start_date'] = UserCommon::convertArabicNumbers(request()['start_date']);
        request()->merge($data);
        return parent::store();
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PkEvent());
        $form->display(__('admin.ID'));
        $form = new Form(new PkEvent());
        $lastStartDate = \Modules\Events\Entities\PkEvent::max('start_date');

        $minStartDate = $lastStartDate ? \Carbon\Carbon::parse($lastStartDate)->addDay(8)->toDateString() : null;
        $form->date('start_date', __('Start Date'))->default($minStartDate ?? date("Y-m-d"))
            ->rules(function ($form) {

                $lastStartDate = \Modules\Events\Entities\PkEvent::max('start_date');

                $minStartDate = $lastStartDate ? \Carbon\Carbon::parse($lastStartDate)->addWeek()->toDateString() : null;
                if ($minStartDate) {
                    if (!$id = $form->model()->id) {
                        return 'required|after:' . $minStartDate;
                    } else {
                        return 'required';
                    }
                } else {
                    return 'required|date';
                }
            });

        // $form->belongsToMany('gifts', Gifts::class)
        //     ->rules('required|array|size:3', [
        //         'size' => __('choose only 3 gifts.'),
        //     ]);

        return $form;
    }


    public function show($id, Content $content)
    {
        return $content
            ->row("<h3>" . __('PK Event') . "</h3>")->row(function ($row) use ($id) {
                $row->column(12, $this->PkEvent($id));
            })
            ->row("<h3>" . __('Rewards') . "</h3>")->row(function ($row) use ($id) {
                $row->column(12, $this->rewardList($id));
            });
    }


    protected function PkEvent($id)
    {

        $grid = new Grid(new PkEvent());
        $grid->model()->where('id', $id);

        $grid->column('start_date', __('Start Date'));
        $grid->column('end_date', __('End Date'));
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableExport();

        return $grid;
    }
    protected function rewardList($id)
    {

        $grid = new Grid(new PkReward);
        $grid->model()->where('pk_event_id', $id);

        $grid->column('level', trans('level'));
        $grid->column('type', trans('type'))->display(function ($type) {

            return   $type == "coins" ? "coins" : ($type == "ware" ? "ware" : ($type == "vip" ? "vip" : 'achievement'));
        });
        $grid->column('target', trans('target'))->display(function ($target) {

            if ($this->type == "coins") {
                return $target;
            } elseif ($this->type == "ware") {
                $ware = Ware::find($target);
                return $ware->name;
            } elseif ($this->type == "vip") {
                $vip = OVip::find($target);
                return $vip->name;
            } else {
                $value = getDriverUrl() . $target;
                return "<img src='$value' width='80' height='80'>";
            }
        });

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableExport();

        return $grid;
    }
}
