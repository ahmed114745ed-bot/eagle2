<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\MainController;
use App\Models\RewardWinnerGame;
use Encore\Admin\Layout\Content;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RewardWinnerGameController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'RewardWinnerGame';


    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Reward Winner Game'))
            ->body($this->grid()));
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('Reward Winner Game'))
            ->body($this->detail($id)));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('Reward Winner Game'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Reward Winner Game'))
            ->body($this->form()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RewardWinnerGame());

        $grid->column('id', __('Id'));
        $grid->column('rank', __('Rank'));
        $grid->column('reward_coins', __('Reward coins'));
    

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
        $show = new Show(RewardWinnerGame::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('rank', __('Rank'));
        $show->field('reward_coins', __('Reward coins'));
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
        $form = new Form(new RewardWinnerGame());

        $form->number('rank', __('Rank'))
            ->required()
            ->creationRules('unique:reward_winner_games,rank')
            ->updateRules('unique:reward_winner_games,rank,{{id}}');
        $form->number('reward_coins', __('Reward coins'))->required();

        return $form;
    }
}
