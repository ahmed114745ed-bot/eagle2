<?php

namespace Modules\RankingReward\Http\Controllers;

use App\Admin\Controllers\MainController;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Modules\RankingReward\Entities\RankingType;

class RankingTypeController extends MainController
{
    public $permission_name = 'ranking-types';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(__('Ranking Types'))
            ->body($this->grid()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(__('Ranking Types'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(__('Edit Ranking Types'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(__('Create Ranking Types'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new RoomBoomLevel());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('level', __('level'));
        $grid->column('min_target', __('min target'));
        $grid->column('target', __('target'));
        if (!request()->filled('_export_')) {
            $grid->column('video', __('video'))->display(function ($path) {
                $defaultImage = asset("images/image.png");

                $url = getImagePath($path) ?? $defaultImage;
                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }
                return handleShowImageWithTypes($this->id, $url, 50, 50);
            });
        }
        $grid->column('created_at', __('Created At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });
        if (Admin::user()->can('browse-room-boom-rewards') || Admin::user()->can('*')) {
            if (!request()->filled('_export_')) {
                $grid->column(__('Procedures'))->display(function () {
                    $url = url('admin/room_boom_rewards/' . $this->id);
                    $text = __('Room Boom Rewards');
                    return "<a href='{$url}' class='btn btn-sm btn-info'>{$text}</a>";
                });
            }
        }
        if (method_exists($this, 'extendGrid')) {
            $this->extendGrid($grid);
        }

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(RoomBoomLevel::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('level', __('level'));
        $show->field('min_target', __('target'));
        $show->field('target', __('target'));
        $show->column('created_at', __('Created At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });
        $show->column('updated_at', __('Updated At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });

        return $show;
    }

    protected function form()
    {
        $form = new Form(new RankingType());

        $form->number('level', __('level'))->rules('required|integer|min:1')
            ->help(__('Represents the stage or rank of the Room Boom.'));
        $form->number('min_target', __('min target'))->required()
            ->help(__('The minimum required gifts to activate the Boom Room at this level.'));
        $form->number('target', __('target'))->required()
            ->help(__('The full target that must be achieved to complete the Room Boom at this level.'));
        $form->file('video', trans('video'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        })->default('1.png')
            ->help(__('The special video for this level, displayed after completion. Each level has its own unique video.'));

        $form->saving(function (Form $form) {
            if (!$form->model()->exists) {
                $count = RoomBoomLevel::count();
                if ($count >= 5) {
                    $error = __('You can only have a maximum of 5 Room Boom Levels.');
                    admin_error($error);
                    return back();
                }
            }
        });

        return $form;
    }
}
