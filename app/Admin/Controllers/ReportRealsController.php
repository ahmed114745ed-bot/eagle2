<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\Reals\Entities\ReportReals;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Content;
use Modules\Reals\Entities\Real;

class ReportRealsController extends AdminController
{
    use HasResourceActions;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'ReportReals';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */

     public function index(Content $content)
     {
        return $content
        ->title(trans('Report Reel'))
        ->body($this->grid());
     }


     protected function grid()
{
    $grid = new Grid(new ReportReals());
    $grid->model()->whereHas('reel')->orderByDesc('id');

    $grid->column('id', __('ID'));

    // 🔹 **عرض بيانات المراسل (Reporter)**
    $grid->column('reporter.name', __('Reporter'))->display(function () {
        $reporter = $this->reporter;
        if (!$reporter) return '-';
        
        $name = $reporter->name;
        $uuid = $reporter->uuid;
        $avatar = $reporter->avatar ? getImagePath($reporter->avatar) : asset("images/default-avatar.png");

        return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                <img src='$avatar' alt='Reporter Avatar' style='width: 40px; height: 40px; border-radius: 50%;'>
                <div>
                    <span style='color: #3498db; font-weight: bold;'>$name</span><br>
                    <span style='color: #aaa; font-size: smaller;'>UUID: $uuid</span>
                </div>
            </div>";
    });

    // 🔹 **عرض بيانات المستخدم الذي تم الإبلاغ عنه (Reported User)**
    $grid->column('reportedUser.name', __('Reported User'))->display(function () {
        $reportedUser = $this->reportedUser;
        if (!$reportedUser) return '-';

        $name = $reportedUser->name;
        $uuid = $reportedUser->uuid;
        $avatar = $reportedUser->avatar ? getImagePath($reportedUser->avatar) : asset("images/default-avatar.png");

        return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                <img src='$avatar' alt='Reported User Avatar' style='width: 40px; height: 40px; border-radius: 50%;'>
                <div>
                    <span style='color: #e74c3c; font-weight: bold;'>$name</span><br>
                    <span style='color: #aaa; font-size: smaller;'>UUID: $uuid</span>
                </div>
            </div>";
    });

    // 🔹 **عرض الوصف**
    $grid->column('description', __('Description'))->display(function ($description) {
        $limitedDescription = mb_substr($description, 0, 40) . (strlen($description) > 40 ? '...' : '');
        return "<a href='#' class='view-description' data-description=\"" . htmlentities($description) . "\">$limitedDescription</a>";
    });

    // 🔹 **زر حذف الفيديو**
    $grid->column(__('redirect_button'))->display(function () {
        $redirectRoute = 'delete-reel';
        return '<a href="'.route($redirectRoute, ['real_id' => $this->real_id, 'id' => $this->id]).'" class="btn btn-xs btn-danger">'.__('admin.delete_video').'</a>';
    });

    // 🔹 **عرض الفيديو في مودال**
    $grid->column('real_id', __('View Reel'))->modal('Video Preview', function ($model) {
        return self::getRoomsShow($model->reel);
    });

    $grid->disableCreateButton();
    $grid->disableExport();
    $grid->actions(function ($actions) {
        $actions->disableEdit();
    });

    return $grid;
}

public static function getRoomsShow(Real $reel)
{
    $show = new Show($reel);
    // $show->field('id', 'ID');

    // 🔹 **عرض الفيديو**
    $show->field('url', __('Video'))->unescape()->as(function ($path) {
        $url = getImagePath($path);
        return "<video width='100%' controls>
                    <source src='$url' type='video/mp4'>
                    Your browser does not support the video tag.
                </video>";
    });

    // 🔹 **عرض الوصف**
    // $show->field('description', __('Description'));

    $show->panel()->tools(function ($tools) {
        $tools->disableEdit();
        $tools->disableList();
        $tools->disableDelete();
    });

    Admin::script("
        if (window.innerWidth >= 1024) { 
            $('.table-responsive').removeClass('table-responsive');
        }
    ");

    return $show;
}


    // protected function grid()
    // {
    //     $grid = new Grid(new ReportReals());
    //     $grid->model()->whereHas('reel')->orderByDesc('id');

    //     $grid->column('id', __('Id'));
    //     $grid->column('Reporter_id', __('Reporter id'));
    //     $grid->column('Reported_id', __('Reported id'));
    //     $grid->column('description', __('Description'));
    //     $grid->column(__('redirect_button'))->display(function ($_) {
    //         $redirectRoute = 'delete-reel';
    //         $button = '<a href="'.route($redirectRoute, ['real_id' => $this->real_id, 'id' => $this->id]).'" class="btn btn-xs btn-primary">'.__('admin.delete_video').'</a>';
    //         return $button;
    //     });

    //     $grid->column('real_id', __('View Reel'))->modal('test', function ($model){
    //         return self::getRoomsShow($model->reel);
    //     });
        
    //     $grid->disableCreateButton();
    //     $grid->disableExport();
    //     $grid->actions(function ($actions) {
    //         $actions->disableEdit();
    //     });
    //             return $grid;
    // }

    // public static function getRoomsShow(Real $reel){

    //     $show = new Show($reel);
    //     $show->field('id', 'ID');
    //     $show->field('url', __('Video'))->display(function ($path) {
    //         /** @var Ware $this */
    //         $url = getImagePath($path);
    //         return handleShowImageWithTypes($this->id, $url, 50, 50);
    //     });
    //     $show->field('description', __('description'));
       

    //     $show->panel()
    //          ->tools(function ($tools) {
    //              $tools->disableEdit();
    //              $tools->disableList();
    //              $tools->disableDelete();
    //          });
    //          Admin::script("
    //          if (window.innerWidth >= 1024) { // Example threshold for desktop screens
    //              $('.table-responsive').removeClass('table-responsive');
    //              }
    //          ");
    //     return $show;
    // }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ReportReals::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('real_id', __('Real id'));
        $show->field('Reporter_id', __('Reporter id'));
        $show->field('Reported_id', __('Reported id'));
        $show->field('description', __('Description'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ReportReals());

        $form->number('real_id', __('Real id'));
        $form->number('Reporter_id', __('Reporter id'));
        $form->number('Reported_id', __('Reported id'));
        $form->text('description', __('Description'));

        return $form;
    }
}
