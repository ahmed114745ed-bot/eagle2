<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\MainController;
use App\Admin\Services\UserService;
use App\Models\ChangeLevelHistory;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Modules\Vip\Entities\Vip;

class UserLevelController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User Levels';

    public $permission_name = 'edit-level';


    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans(__('Edit Level')))
            ->body($this->grid()));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans(__($this->title)))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans(__($this->title)))
            ->body($this->form()));
    }
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans(__($this->title)))
            ->body($this->detail($id)));
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $countryID = session('filter_country_id');
        $grid->model()
            ->with([
                'profile',
                'country',
                'senderLevel',
                'receiverLevel',
                'packs' => fn($q) => $q->whereIn('type', [25])->where('is_used', true)->with('ware:id,value'),
            ])
            ->when($countryID, fn($q) => $q->where('country_id', $countryID));
        $grid->quickSearch();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('uuid', __('uuid'));
            });
        });

        $grid->column('id', __('Id'));
        $grid->column('name', __('user'))->display(function () {
            return app(UserService::class)->adminUserCard($this);
        });
        Admin::style(UserService::adminUserCardStyles() . gridStyles());


        $arrowIcon = asset('images/arrows.png'); // Path to the arrows.png image

        $grid->column('total_sender_level', __('Sender Level'))
            ->display(function ($value) use ($arrowIcon) {

                $defaultImage = asset("images/level0.png"); // الصورة الافتراضية
                $vip = $this->totalSenderLevels;
                $avatar = $vip && @$vip?->img ? getImagePath($vip?->img) : $defaultImage;

                return "<div style='display: flex; align-items: center; gap: 5px;'>
                        <img src='$avatar' alt='User Avatar' style='width: 64px; height: 16px;'>
                            <span>$value</span>
                            <img src='$arrowIcon' style='width: 16px; height: 16px;'>
                        </div>";
            });

        $grid->column('total_received_level', __('Received Level'))
            ->display(function ($value) use ($arrowIcon) {

                $defaultImage = asset("images/level0.png"); // الصورة الافتراضية
                $vip = $this->totalReceiverLevels;
                $avatar = $vip && $vip?->img ? getImagePath($vip?->img) : $defaultImage;

                return "<div style='display: flex; align-items: center; gap: 5px;'>
                        <img src='$avatar' alt='User Avatar' style='width: 64px; height: 16px;'>
                            <span>$value</span>
                            <img src='$arrowIcon' style='width: 16px; height: 16px;'>
                        </div>";
            });
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->disableCreateButton();
        $grid->disableExport();

        $grid->tools(function (Grid\Tools $tools) {
            $url = '/admin/change-level-histories';
            $button = '<a href="' . $url . '" class="btn btn-sm btn-success"><i class="fa fa-go"></i>&nbsp;&nbsp;' . __("admin.history") . '</a>';
            $tools->append($button);
        });
        return $grid;
    }



    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());
        $this->disableFormTools($form);

        $form->text('uuid', __('uuid'))->updateRules('unique:users,uuid,{{id}}')->creationRules('unique:users,uuid')->required();
        $form->number('total_sender_level', __('Sender Level'))->default(0);
        $form->number('total_received_level', __('Received Level'))->default(0);
        $form->saving(function (Form $form) {

            $new_total_sender_level = $form->input('total_sender_level');
            $old_total_sender_level = $form->model()->getOriginal('total_sender_level');
            $new_total_received_level = $form->input('total_received_level');
            $old_total_received_level = $form->model()->getOriginal('total_received_level');
            $userId = $form->model()->id;

            ChangeLevelHistory::create([
                'user_id' =>  $userId,
                'admin_id' => Auth::id(),
                'old_total_sender_level' => $old_total_sender_level,
                'new_total_sender_level' => $new_total_sender_level,
                'old_total_received_level' => $old_total_received_level,
                'new_total_received_level' => $new_total_received_level,

            ]);
        });
        return $form;
    }
}
