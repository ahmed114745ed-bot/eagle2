<?php

namespace Modules\Events\Http\Controllers\web;

use App\Admin\Controllers\MainController;
use App\Models\OVip;
use App\Models\Ware;
use App\Services\AppFeatureService;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;

use Encore\Admin\Layout\Content;
use Modules\Events\Entities\PkReward;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\HasResourceActions;

class PkEventGiftController extends MainController
{

    use HasResourceActions;
    public $permission_name = 'pk-event-rewards';
    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("pk_event");
    }

    public function index(Content $content)
{
    $url = url('/admin/pk-events'); // Define your button URL

    $buttonHTML = <<<HTML
    <a href="{$url}" class="btn btn-sm btn-success" style="margin-bottom: 20px;">
        <i class="fa fa-arrow-left"></i> رجوع
    </a>
    HTML;

    return $content
        ->header(trans('admin.index'))
        ->description(trans('admin.description'))
        ->breadcrumb(
            ['text' => trans('admin.eventGift')]
        )
        ->row($buttonHTML) // Add the button row
        ->row($this->grid1()) // First grid
        ->row($this->grid2()) // Second grid
        ->row($this->grid3()); // Third grid
}
    public function create(Content $content)
    {
        return $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body($this->form());
    }

    public function update($id)
    {
        $id = request()->route('id');
        return $this->form()->update($id);
    }

    public function edit($id, Content $content)
    {
        $id = request()->route('id');
        return $content
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
            ->body($this->form()->edit($id));
    }

    public function show($id, Content $content)
    {
        return $content
            ->header(trans('admin.detail'))
            ->description(trans('admin.description'))
            ->body($this->detail($id));
    }


    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'PkEvent';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid1()
    {
        $pkType = request('pk_type');
        $pkEventId = request('pk_event_id');
        $grid = new Grid(new PkReward());
        $grid->model()->orderBy('level');
        $grid->column('created_at')->hide();
        $grid->model()->where("pk_event_id",$pkEventId)->where("pk_type",$pkType)->where("level",1);
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'));
        $grid->column('gift_id', __('Gifts'))->display(function (){
            if ($this->type == "ware"){
                return @$this->ware->name;
            }elseif ($this->type == "vip"){
                return @$this->vip->name;
            }elseif ($this->type == "coins"){
                return @$this->target;
            }elseif ($this->type == "achievement"){
               
                return "achievement";
            }

        });

        $grid->column('image', __('image'))->display(function ($path) {
            if ($this->type == 'ware') {
                $ware = Ware::find($this->target);
                $path = $ware->img2 ?? $ware->show_img ;
            } elseif ($this->type == 'vip') {
                $vips = OVip::find($this->target);
                $path = $vips->img;
            } elseif ($this->type == 'achievement') {
                $path = $this->target;
            } else {
                $path = 'cion.png';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('expire', __('expire'));
        $grid->column('created_at', __('Created at'));

        $grid->actions (function ($actions){
            $actions->disableView();
        });
        $grid->disableCreateButton();
        $grid->tools(function (Grid\Tools $tools) {
            $url = request()->route('pk_event_id')."/1/create";
            $customButtonHTML = <<<HTML
            
                <a href="{$url}" class="btn btn-sm btn-success" style="margin-right: 10px;">
                    <i class="fa fa-plus"></i> ضيف
                </a>
                <h3 style="margin-right: 10px;">جوائز للفائز الأول</h3>
        
            HTML;
            $tools->append($customButtonHTML);
        });
        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");
        return $grid;
    }
    protected function grid2()
    {
        $pkType = request('pk_type');
        $pkEventId = request('pk_event_id');
        $grid = new Grid(new PkReward());
        $grid->column('created_at')->hide();
        $grid->model()->where("pk_event_id",$pkEventId)->where("pk_type",$pkType)->where("level",2);
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'));
        $grid->column('gift_id', __('Gifts'))->display(function (){
            if ($this->type == "ware"){
                return @$this->ware->name;
            }elseif ($this->type == "vip"){
                return @$this->vip->name;
            }elseif ($this->type == "coins"){
                return @$this->target;
            }elseif ($this->type == "achievement"){
               
                return "achievement";
            }

        });
        $grid->column('image', __('image'))->display(function ($path) {
            if ($this->type == 'ware') {
                $ware = Ware::find($this->target);
                $path = $ware->img2 ?? $ware->show_img ;
            } elseif ($this->type == 'vip') {
                $vips = OVip::find($this->target);
                $path = $vips->img;
            } elseif ($this->type == 'achievement') {
                $path = $this->target;
            } else {
                $path = 'cion.png';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('expire', __('expire'));
        $grid->column('created_at', __('Created at'));

        $grid->actions (function ($actions){
            $actions->disableView();
        });
        $grid->disableCreateButton();
        $grid->tools(function (Grid\Tools $tools) {
            $url = request()->route('pk_event_id')."/2/create";
            $customButtonHTML = <<<HTML
            <a href="{$url}" class="btn btn-sm btn-success" style="margin-right: 10px;">
                <i class="fa fa-plus"></i>ضيف
            </a>
            <h3 style="margin-right: 10px;">جوائز للفائز الثاني</h3>
            HTML;
            $tools->append($customButtonHTML);
        });
        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");
        return $grid;
    }

    protected function grid3()
    {
        $pkType = request('pk_type');
        $pkEventId = request('pk_event_id');
        $grid = new Grid(new PkReward());
        $grid->model()->orderBy('level');
        $grid->column('created_at')->hide();
        $grid->model()->where("pk_event_id",$pkEventId)->where("pk_type",$pkType)->where("level",3);
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'));
        $grid->column('gift_id', __('Gifts'))->display(function (){
            if ($this->type == "ware"){
                return @$this->ware->name;
            }elseif ($this->type == "vip"){
                return @$this->vip->name;
            }elseif ($this->type == "coins"){
                return @$this->target;
            }elseif ($this->type == "achievement"){
                
                return "achievement";
            }

        });

        $grid->column('image', __('image'))->display(function ($path) {
            if ($this->type == 'ware') {
                $ware = Ware::find($this->target);
                $path = $ware->img2 ?? $ware->show_img ;
            } elseif ($this->type == 'vip') {
                $vips = OVip::find($this->target);
                $path = $vips->img;
            } elseif ($this->type == 'achievement') {
                $path = $this->target;
            } else {
                $path = 'cion.png';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('expire', __('expire'));
        $grid->column('created_at', __('Created at'));

        $grid->actions (function ($actions){
            $actions->disableView();
        });
        $grid->disableCreateButton();
        $grid->tools(function (Grid\Tools $tools) {
            $url = request()->route('pk_event_id')."/3/create";
            $customButtonHTML = <<<HTML
            <a href="{$url}" class="btn btn-sm btn-success" style="margin-right: 10px;">
                <i class="fa fa-plus"></i>ضيف
            </a>
            <h3 style="margin-right: 10px;"> جوائز للفائز الثالث </h3>
            HTML;
            $tools->append($customButtonHTML);
        });
        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");

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
//        $show = new Show(PkEvent::findOrFail($id));

//        $show->field('id', __('Id'));
//        $show->field('admin_id', __('Admin id'));
//        $show->field('start_date', __('Start date'));
//        $show->field('end_date', __('End date'));
//        $show->field('editor_id', __('Editor id'));
//        $show->field('description_en', __('Description en'));
//        $show->field('description_ar', __('Description ar'));
//        $show->field('deleted_at', __('Deleted at'));
//        $show->field('created_at', __('Created at'));
//        $show->field('updated_at', __('Updated at'));

//        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PkReward());
        $form->hidden('pk_event_id')->value(request('pk_event_id'));
        $form->hidden('pk_type')->value(request('pk_type'));

        $form->hidden('level')->value(request()->route('level'));
        $form->select('type', trans('type'))->options(["ware" => __('ware'),"vip" => __('vip'), "coins" => __('coins'),"achievement" => __('achievement')])
            ->when("ware" ,function () use ($form){
                $form->select('target1', trans('wares'))->options(function (){
                    $ops = [0=>''];
                    $wares = Ware::query()->select(['id','name', 'type'])->whereIn('type',[4,5,6])->get();
                    foreach ($wares as  $ware){
                        $ops[$ware->id]=$ware->name.'_'.$ware->id;

                        if ($ware->type == 4) {
                            $ops[$ware->id] .='_' .'bubble';
                        } elseif ($ware->type == 5) {
                            $ops[$ware->id] .= '_' .'intro';
                        } elseif ($ware->type == 6) {
                            $ops[$ware->id] .= '_' .'frame';
                        }
                    }
                    return $ops;
                });
            })
        ->when("vip",function () use ($form){
            $form->select('target2', trans('vips'))->options(function (){
                $vips = OVip::query()->select('id','name')->get();
                foreach ($vips as  $vip){
                    $ops[$vip->id]=$vip->name;
                }
                return $ops;
            });
        })
         ->when("coins",function () use ($form){
            $form->number("target3",__("coins"));
        })->when("achievement",function () use ($form){
            $form->image("target4", __('image'))->name(function ($file) {
                return now()->timestamp.'.'.$file->guessExtension();
            })->disk('gcs');
        });
        $form->number('expire', __('expire'));
        $form->saved(function (Form $form) {
            $route = url('admin/pk-events-gift/pk-star/'.request('pk_event_id'));
            return redirect($route);
        });
        return $form;
    }
}
