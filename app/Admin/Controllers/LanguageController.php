<?php

namespace App\Admin\Controllers;


use App\Models\Language;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;

class LanguageController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Language';
    public $permission_name = 'language';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */

     public function index(Content $content)
     {
         return $content
             ->title(__('Languages'))
             ->description(__('Manage the available languages'))
             ->body($this->grid());
     }
 
    protected function grid()
    {
        $grid = new Grid(new Language());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('code', __('Code'));
        $grid->column('direction', __('Direction'))->editable('select', [
            'LTR' => __('Left to Right (LTR)'),
            'RTL' => __('Right to Left (RTL)')
        ]);
        
        
        $grid->column('is_enabled', __('Is enabled'))->switch();

       

        $grid->disableCreateButton();  // تعطيل زر الإنشاء
        $grid->disableActions();       // تعطيل زر العرض والتعديل والحذف لكل صف
        $grid->disableRowSelector();   // تعطيل تحديد الصفوف للحذف الجماعي
        $grid->disableExport();        // تعطيل زر التصدير (اختياري)

        // $grid->column('is_enabled', __('Is enabled'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Language::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('code', __('Code'));
        $show->field('direction', __('Direction'));
        $show->field('is_enabled', __('Is enabled'));
        // $show->field('created_at', __('Created at'));
        // $show->field('updated_at', __('Updated at'));

        return $show;
    }
    
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Language());

        $form->text('name', __('Name'));
        $form->text('code', __('Code'));
        $form->select('direction', __('Direction'))
        ->options([
            'LTR' => 'Left to Right (LTR)',
            'RTL' => 'Right to Left (RTL)',
        ])
        ->default('LTR');
            $form->switch('is_enabled', __('Is enabled'))->default(1);

        return $form;
    }
}
