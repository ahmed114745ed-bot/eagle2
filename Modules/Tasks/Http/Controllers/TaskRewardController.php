<?php

namespace Modules\Tasks\Http\Controllers;

use App\Models\OVip;
use App\Models\Ware;
use Modules\Tasks\Entities\TaskReward;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\Events\Entities\Reward;
use Modules\Tasks\Entities\Day;
use Encore\Admin\Form\Request;
use Modules\CP\Entities\CpLevelGift;
use Modules\DailyPrize\Entities\DailyGift;

class TaskRewardController extends AdminController
{
    
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'TaskReward';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TaskReward());

        // Define columns to be displayed
        $grid->column('id', 'ID');
        $grid->column('day_id', 'Day ID');
        $grid->column('type', 'Type');
        $grid->column('target', 'Target');
        $grid->column('expire', 'Expire');//->display(function ($expire) {
        //    return $expire ? $expire->format('Y-m-d H:i:s') : 'N/A';
        //});
        $grid->column('created_at', 'Created At');
        $grid->column('updated_at', 'Updated At');

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
        $show = new Show(TaskReward::findOrFail($id));

        // Display each attribute of the TaskReward model
        $show->field('id', 'ID');
        $show->field('day_id', 'Day ID');
        $show->field('type', 'Type');
        $show->field('target', 'Target');
        $show->field('expire', 'Expire Date');
        $show->field('created_at', 'Created At');
        $show->field('updated_at', 'Updated At');

        return $show;
    }



    protected function form()
{
    $form = new Form(new TaskReward());

    $form->select('day_id', 'Day ID')
        ->options(Day::pluck('title', 'id'))
        ->required();

    $form->select('type', trans('type'))->options([
        "ware" => __('ware'),
        "vip" => __('vip'),
        "coins" => __('coins'),
        "achievement" => __('achievement')
    ])
    ->when("ware", function () use ($form) {
        $form->select('target', trans('wares'))->options(function () {
            $ops = [0 => ''];
            $wares = Ware::query()->select(['id', 'name', 'type'])->whereIn('type', [4, 5, 6])->get();
            foreach ($wares as $ware) {
                $ops[$ware->id] = $ware->name . '_' . $ware->id;
                if ($ware->type == 4) {
                    $ops[$ware->id] .= '_bubble';
                } elseif ($ware->type == 5) {
                    $ops[$ware->id] .= '_intro';
                } elseif ($ware->type == 6) {
                    $ops[$ware->id] .= '_frame';
                }
            }
            return $ops;
        });
    })
    ->when("vip", function () use ($form) {
        $form->select('target', trans('vips'))->options(function () {
            $vips = OVip::query()->select('id', 'name')->get();
            $ops = [];
            foreach ($vips as $vip) {
                $ops[$vip->id] = $vip->name;
            }
            return $ops;
        });
    })
    ->when("coins", function () use ($form) {
        $form->number("coins", __("coins"));
    })
    ->when("achievement", function () use ($form) {
        $form->image("achievement", __('image'))->name(function ($file) {
            return now()->timestamp . '.' . $file->guessExtension();
        })->disk('gcs');
    });

    $form->number('expire', __('expire'));

    $form->saving(function (Form $form) {

        if ($form->type === 'ware' || $form->type === 'vip') {
            $form->model()->target = $form->target ?? null;
        } elseif ($form->type === 'coins') {
            $form->model()->target = $form->coins ?? 0; 
        } elseif ($form->type === 'achievement') {
            $form->model()->target = $form->achievement ?? ''; 
        }

        if (is_null($form->model()->target)) {
            throw new \Exception("Target must be selected or set for the chosen type.");
        }
    });

    return $form;
}



    

}
