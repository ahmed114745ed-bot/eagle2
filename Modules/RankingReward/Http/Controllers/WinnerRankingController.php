<?php

namespace Modules\RankingReward\Http\Controllers;

use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\Vip\Entities\OVip;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use Modules\Badge\Entities\Badge;
use App\Admin\Services\UserService;
use App\Admin\Controllers\MainController;
use Modules\RankingReward\Entities\WinnerRanking;

class WinnerRankingController extends  MainController

{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Winner Ranking';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans($this->title))
            ->body($this->grid()));
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WinnerRanking());

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {

                $filter->where(function ($query) {
                    $datt = \App\Helpers\UserCommon::arabicToEnglishNumbers($this->input);
                    $query->whereDate('created_at', '>=', $datt);
                }, __('from_date'), 'from_date')->date();
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    $datt = \App\Helpers\UserCommon::arabicToEnglishNumbers($this->input);

                    $query->whereDate('created_at', '<=', $datt);
                }, __('to_date'), 'to_date')->date();
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;

                    // Search on the related user table
                    $query->whereHas('user', function ($q) use ($input) {
                        $q->where('id', $input)
                            ->orWhere('uuid', $input);
                    });
                }, __('User'))->placeholder(__('Search by ID, UUID'));
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('type', __('type'))->select([
                    'sender' => __('wealth'),
                    'receiver' => __('charm'),
                    'game' => __('game'),
                    'charge' => __('charge'),
                ]);
            });
        });

        $grid->column('id', __('Id'));
        $grid->column('winner_id', __('Winner'))->display(function ($name) {

            $user = $this->user;
            if (! $user) {
                return __('No User');
            }
            return app(UserService::class)->adminUserAvatar($user);
        });

        $grid->column('type', __('type'))->display(function ($type) {

            return   $type == "sender" ? "wealth" : ($type == "receiver" ? "charm" : $type);
        });
        $grid->column('reward.target_type', trans('reward type'))->display(function ($type) {

            return   $type == "coins" ? "coins" : ($type == "ware" ? "ware" : ($type == "vip" ? "vip" : 'achievement'));
        });

        $grid->column('gift_id', __('gifts'))->display(function () {
            if ($this->reward->target_type == "ware") {
                return @$this->reward->ware->name ?? '';
            } elseif ($this->reward->target_type == "vip") {
                return @$this->reward->vip->name ?? '';
            } elseif ($this->reward->target_type == "badge") {
                return @$this->reward->badge->name ?? '';
            } elseif ($this->reward->target_type == "coins") {
                return @$this->target;
            } elseif ($this->reward->target_type == "achievement") {
                $value = getDriverUrl() . '/' . @$this->target;
                return "<img src='$value' width='80' height='80'>";
            }
        });

        $grid->column('image', __('image'))->display(function ($path) {
            if (@$this->reward->target_type == 'ware') {
                $ware = Ware::find($this->reward->target);
                $path = $ware->img2 ?? ($ware->show_img ?? "");
            } elseif (@$this->reward->target_type == 'vip') {
                $vips = OVip::find($this->reward->target);
                $path = $vips->img ?? '';
            } elseif (@$this->reward->target_type == 'badge') {
                // $vips = Badge::find($this->target);
                $path = @$this->reward->badge->image ?? '';
            } elseif (@$this->reward->target_type == 'achievement') {
                $path = @$this->reward->target ?? '';
            } else {
                $path = 'coin.png';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('created_at', __('Created at'));

        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->disableActions();
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
        $show = new Show(WinnerRanking::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('winner_id', __('Winner id'));
        $show->field('reward_id', __('Reward id'));
        $show->field('type', __('Type'));
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
        $form = new Form(new WinnerRanking());

        $form->number('winner_id', __('Winner id'));
        $form->number('reward_id', __('Reward id'));
        $form->text('type', __('Type'));

        return $form;
    }
}
