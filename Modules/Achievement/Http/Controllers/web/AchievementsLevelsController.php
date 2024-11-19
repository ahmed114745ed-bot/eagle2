<?php

namespace Modules\Achievement\Http\Controllers\web;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use Modules\Achievement\Enums\TargetType;
use Modules\Achievement\Entities\Achievement;
use Modules\Achievement\Entities\AchievementLevel;

class AchievementsLevelsController extends MainController
{
    public $permission_name = 'achievement_level';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */

    public function create2(Content $content, $id = null)
    {

        $id_achi = $id;
        $targetTypes = TargetType::cases();

        return $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body(view('admin/grid/users/addAchievementLevel', compact('id_achi', 'targetTypes')));
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

    public function edit1($id, Content $content)
    {
        $id_achi = $id;
        $targetTypes = TargetType::cases();

        return $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body(view('admin/grid/users/addAchievementLeveledit', compact('id_achi', 'targetTypes')));
    }
    public function index(Content $content)
    {
        return $content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
            ->body($this->grid());
    }


    protected function grid()
    {
        $grid = new Grid(new AchievementLevel());
        $achievement_id = request('achievement_id');
       // $grid->disableRowSelector();
       // $id = request()->input('achievement_id');
        // if (!$id) {
        //     return  redirect()->route(nameRoute('admin.achievements.index'));
        // }
        $grid->model()->where('achievement_id', $achievement_id);

        $grid->column('id', __('Id'));
        $grid->column('achievement.type', __('Achievement'));
        $grid->column('target', __('Target'));
        $grid->column('target_type', __('Target type'));

        $grid->column('valid_image', __('Valid image'))->display(function ($value)  {
            $value = getDriverUrl() . '/' . $value;
            return "<img src='$value' width='80' height='80'>";
        });
        $grid->column('invalid_image', __('Invalid image'))->display(function ($value)  {
            $value = getDriverUrl() . '/' . $value;
            return "<img src='$value' width='80' height='80'>";
        });

        $grid->column('ar_description', __('ar_description'));
        $grid->column('en_description', __('en_description'));



        // $grid->column('invalid_image', __('Invalid image'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));
        // $grid->column('deleted_at', __('Deleted at'));


       // $grid->disableCreateButton();
        $grid->disableExport();
        // $grid->tools(function (Grid\Tools $tools) {
        //     $tools->append('<a href="achievement-levels/create/' . request()->input('achievement_id') . '" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> New</a>');
        // });
        // $grid->actions(function ($actions) {
        //     // $actions->disableEdit();


        //     $actions->append('<a href="">gdfgdfg</a>');
        // });

        // if ($id) {
            return $grid;
        // } else {
        //     return route(nameRoute('admin.achievements'));
        // }
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(AchievementLevel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('achievement_id', __('Achievement id'));
        $show->field('target', __('Target'));
        $show->field('target_type', __('Target type'));
        $show->field('valid_image', __('Valid image'));

        $show->field('invalid_image', __('Invalid image'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('deleted_at', __('Deleted at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     *
     *
     */


    // protected function form($id = null)
    // {
    //     if (!$id) {
    //         return  redirect()->route(nameRoute('admin.achievements.index'));
    //     }
    //     $form = new Form(new AchievementLevel());

    //     if (!$form->isEditing()) {

    //         if ($id) {
    //             $ache = Achievement::find(intval($id));
    //             $form->display('valid_image', __('Valid image'))->with(function ($value) use ($ache) {
    //                 $value = getDriverUrl() . '/' . $ache->valid_image;
    //                 return "<img src='$value' width='100' height='100'>";
    //             });
    //             $form->display('invalid_image', __('Invalid image'))->with(function ($value) use ($ache) {
    //                 $value = getDriverUrl() . '/' . $ache->invalid_image;
    //                 return "<img src='$value' width='100' height='100'>";
    //             });
    //         }
    //     }
    //     if ($form->isEditing()) {
    //         $form->hidden('achievement_id', __('achievement_id'));

    //         $instances = $form->model()->all();
    //         $index = 0;

    //         if (isset($instances[$index])) {
    //             $id = $instances[$index]->achievement_id;
    //         }
    //         $ache = Achievement::find(intval($id));
    //         $form->display('valid_image', __('Valid image'))->with(function ($value) use ($ache) {
    //             return "<img src='$ache->valid_image' width='100' height='100'>";
    //         });
    //         $form->display('invalid_image', __('Invalid image'))->with(function ($value) use ($ache) {
    //             return "<img src='$ache->invalid_image' width='100' height='100'>";
    //         });
    //     }

    //     $form->number('target', __('Target'))->required();
    //     // $form->text('target_type', __('Target type'));
    //     $form->select('target_type', __('Target type'))->options(function () {
    //         $ops = [0 => ''];
    //         $typs = TargetType::cases();
    //         foreach ($typs as  $cases) {
    //             $ops[$cases->value] = __($cases->value);
    //         }
    //         return $ops;
    //     })->required();
    //     $form->image('valid_image', __('Valid image'))->required();
    //     $form->image('invalid_image', __('Invalid image'))->required();

    //     return $form;
    // }

    protected function form()
    {
        $form = new Form(new AchievementLevel());
        $form->hidden('achievement_id')->value(request('achievement_id'));
        $form->number('target', __('Target'))->rules('required');
        $form->select('target_type', __('Target type'))->options(function ($value) {
            return collect(TargetType::cases())
                ->mapWithKeys(fn($case) => [$case->value => ucfirst($case->name)])
                ->toArray();
        })->rules('required');
        $form->image('valid_image', trans('Valid image'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        })->rules('required');
        //        $form->image('img1', trans('img'));
        $form->file('invalid_image', trans('Invalid image'))->name(function () {
            return 'svga_' . Str::random(6);
        })->rules('required');
        $form->textarea('ar_description', 'Description Ar');
        $form->textarea('en_description', 'Description En');
        return $form;
    }
}
