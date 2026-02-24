<?php

namespace Utd\Vip\Http\Controllers\Web;

use App\Admin\Controllers\MainController;
use App\Helpers\Common;
use App\Services\AppFeatureService;
use App\Support\PackageHelper;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Utd\Vip\Entities\VipAuth;

class VipAuthController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'vip-privilege';

    public $hiddenColumns = [];

    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable('vips');
    }

    public function index(Content $content)
    {
        return parent::index(
            $content->title(trans('vip_prev'))->body($this->grid())
        );
    }

    public function show($id, Content $content)
    {
        return parent::show(
            $id,
            $content->title(trans('vip_prev'))->body($this->detail($id))
        );
    }

    public function edit($id, Content $content)
    {
        return parent::edit(
            $id,
            $content->title(trans('vip_prev'))->body($this->form()->edit($id))
        );
    }

    public function create(Content $content)
    {
        return parent::create(
            $content->title(trans('vip_prev'))->body($this->form())
        );
    }

    protected function grid()
    {
        $grid = new Grid(new VipAuth);

        $grid->id('ID');

        $typeOptions = [
            3 => trans('vip'),
        ];
        if (PackageHelper::isInstalled('cp')) {
            $typeOptions[5] = trans('guardian cp');
        }

        $grid->column('type', trans('type'))->select($typeOptions);
        $grid->column('level', trans('level'));
        $grid->column('enable', trans('enable'))->switch(Common::getSwitchStates());
        $grid->column('name', trans('name'));
        $grid->column('title', trans('title'));
        $grid->column('img_0', trans('img_0'))->image('', 30);
        $grid->column('img_1', trans('img_1'))->image('', 30);

        $this->extendGrid($grid);

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(VipAuth::findOrFail($id));

        $show->id('ID');
        $show->type('type');
        $show->level('level');
        $show->enable('enable');
        $show->name('name');
        $show->title('title');
        $show->img_0('img_0');
        $show->img_1('img_1');
        $show->created_at(trans('admin.created_at'));
        $show->updated_at(trans('admin.updated_at'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new VipAuth);

        $form->display('ID');

        $typeOptions = [
            3 => trans('vip'),
        ];
        if (PackageHelper::isInstalled('cp')) {
            $typeOptions[5] = trans('guardian cp');
        }

        $form->select('type', trans('type'))->options($typeOptions);
        $form->text('level', trans('level'));
        $form->switch('enable', trans('enable'))->states(Common::getSwitchStates());
        $form->text('name', trans('name'));
        $form->text('title', trans('title'));
        $form->image('img_0', trans('img_0'));
        $form->image('img_1', trans('img_1'));

        return $form;
    }
}
