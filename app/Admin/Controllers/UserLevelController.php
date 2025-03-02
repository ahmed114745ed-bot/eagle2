<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use App\Models\ChangeLevelHistory;
use Illuminate\Support\Facades\Auth;
use App\Admin\Controllers\MainController;

class UserLevelController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User Levels';

    public $permission_name = 'user-levels';


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
        $grid->quickSearch();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('uuid', __('uuid'));
            });
        });

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'))->display(function ($name) {
            $path = @$this->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = url("admin/users/{$this->id}");

            return "
        <div style='display: flex; align-items: center; gap: 10px;'>
            <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                $image
                <span style='text-decoration: underline; cursor: pointer;'>$name</span>
            </a>
        </div>
    ";
        });

        $grid->column('uuid', __('uuid'));
        $grid->column('total_sender_level', __('Sender Level'));
        $grid->column('total_received_level', __('Received Level'));
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->disableCreateButton();

        $grid->tools(function (Grid\Tools $tools){
            $url = '/admin/change-level-histories';
            $button = '<a href="'.$url.'" class="btn btn-sm btn-success"><i class="fa fa-go"></i>&nbsp;&nbsp;'.__("admin.history").'</a>';
            $tools->append($button);
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
        //        $show = new Show(User::findOrFail($id));

        return null;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

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
