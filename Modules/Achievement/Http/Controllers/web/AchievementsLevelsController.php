<?php

namespace Modules\Achievement\Http\Controllers\web;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use Modules\Achievement\Enums\TargetType;
use Modules\Achievement\Entities\AchievementLevel;

class AchievementsLevelsController extends MainController
{
    public $permission_name = 'achievement_level';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */

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

        $grid->model()->where('achievement_id', $achievement_id);

        $grid->column('id', __('Id'));
        $grid->column('achievement.type', __('Achievement'));
        $grid->column('target', __('Target'));
        $grid->column('target_type', __('Target type'));

        $grid->column('valid_image', __('Valid image'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('invalid_image', __('Invalid image'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('ar_description', __('ar_description'));
        $grid->column('en_description', __('en_description'));

        $grid->disableExport();
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
        $form->file('valid_image', trans('Valid image'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        })->rules('required');
        $form->file('invalid_image', trans('Invalid image'))->name(function () {
            return 'svga_' . Str::random(6);
        })->rules('required');
        $form->textarea('ar_description', __('ar_description'));
        $form->textarea('en_description', __('en_description'));
        return $form;
    }
}
