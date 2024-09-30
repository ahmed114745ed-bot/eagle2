<?php

namespace Modules\CP\Http\Controllers\web;

use App\Models\Gift;
use App\Models\Config;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Admin\Actions\cpAction;
use Modules\CP\Http\Services\CpServices;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\AdminController;

class CPGiftController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Gift';
    public $permission_name = 'level-cp';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Gift());

        $grid->id(__ ('ID'));
        $grid->name(__('name'));
        $grid->e_name(__('e_name'));
        $grid->column("use_count",__('use count'));
        $grid->vip_level(__('vip_level'));
        $grid->column('hot',trans ('hot'));
        $grid->column('is_play',trans ('is_play'))->switch (Common::getSwitchStates ());
        $grid->price(__ ('price'));
        $grid->column('target', trans('target'))->display(function () {
            $target = Config::where('value',$this->id)->first(); // استخدم الشهر والسنة كمعاملات إذا لزم الأمر
            return $target ? "<span class='label-success' " . 'style="width: 8px;height: 8px;padding: 0;border-radius: 50%;display: inline-block;"' .
                "></span>" : "";
        });
        $grid->column('img',trans ('image'))->image ('','30');
        $grid->column('show_img',trans ('show_img'))->image ('','30');
        $grid->column('enable',trans ('enable'))->switch (Common::getSwitchStates ());
        $grid->column('music_gift',trans ('music_gift'))->switch (Common::getSwitchStatesGiftMucic ());
        $grid->sort(__ ('sort'))->editable();
        $grid->model()->where('type',8)->orderByRaw('ISNULL(`sort`), `sort`')->orderBy('price');
        $this->extendGrid ($grid);
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $model = $actions->row; 
            $actions->add(new cpAction($model->id));
            
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
        $show = new Show(Gift::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('e_name', __('E name'));
        $show->field('type', __('Type'));
        $show->field('vip_level', __('Vip level'));
        $show->field('hot', __('Hot'));
        $show->field('is_play', __('Is play'));
        $show->field('price', __('Price'));
        $show->field('img', __('Img'));
        $show->field('show_img', __('Show img'));
        $show->field('show_img2', __('Show img2'));
        $show->field('sort', __('Sort'));
        $show->field('enable', __('Enable'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('music_gift', __('Music gift'));
        $show->field('international_gift', __('International gift'));
        $show->field('use_count', __('Use count'));
        $show->field('image_type', __('Image type'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Gift());

        $form->text('name', __('name'));
        $form->text('e_name', __('e_name'));
        $form->hidden('type')->value(8);
        $form->number('vip_level', __('vip_level'))->min (0)->placeholder (__ ('less than 256'));
        $form->currency('price', __('price'))->symbol ('💎');
        $form->file('img', __('img'));
        $form->file('show_img', __('show_img'))->required();
        $form->select('image_type', __('image_type'))->options (
            [
                'svga'=>__ ('svga'),
                'alpha'=>__ ('alpha'),
                'mp4'=>__ ('mp4'),
            ]
        )->required();
        $form->file('show_img2', __('show_img2'));
        $form->number('sort', __('sort'));
        $form->switch('enable', __('enable'))->states (Common::getSwitchStates ());
        $form->switch('music_gift',trans ('music_gift'))->states (Common::getSwitchStatesGiftMucic ());

        return $form;
    }
}
