<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Layout\Content;
use App\Facades\CustomNotification;
use App\Models\RequestBackgroundImage;
use App\Admin\Controllers\MainController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\HasResourceActions;

class RequestBackgroundImageController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'background-image-request';

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title(trans('request-background-image'))
            ->body($this->grid());
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
        return $content
            ->title(trans('request-background-image'))
            ->body($this->detail($id));
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
        return $content
            ->title(trans('request-background-image'))
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->title(trans('request-background-image'))
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RequestBackgroundImage);
        $grid->model()->orderByDesc('id');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('status', __('status'))->select([
                    0 => __('pending'),
                    1 => __('accepted'),
                    2 => __('denied'),

                ]);
            });
        });
        $grid->id(__('admin.ID'));
        $grid->owner_room_id(__('owner room id'));

        $grid->column('owner.name', __('owner'))
            ->display(function ($name) {
                $uid = @$this->owner->uuid;
                $path = @$this->owner?->profile?->avatar;
                $url = getImagePath($path) ?? asset("images/businessman-icon.jpg");
                $image = handleShowImageWithTypes($this->id, $url, 40, 40);

                return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                        <strong>$name</strong><br>
                        <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                    </div>
                </div>
            ";
            });
            $grid->img(__('image'))->display(function ($img) {
                $image = getImagePath($img);
                $url = url("/$image");
            
                return "<a href='$url' target='_blank'>
                            <img src='$image' style='width: 30px; height: 30px; border-radius: 5px;' />
                        </a>";
            });
        $grid->status(__('status'))->using(
            [
                0 => __('pending'),
                1 => __('accepted'),
                2 => __('denied')
            ]
        );
        $grid->column('expair', __('expire'));

        $grid->updated_at(trans('admin.updated_at'))->diffForHumans();
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
        $show = new Show(RequestBackgroundImage::findOrFail($id));

        $show->id('ID');
        $show->owner_room_id('owner_room_id');
        $show->img('img')->image('', 30);
        $show->status(__('status'))->using(
            [
                0 => __('pending'),
                1 => __('accepted'),
                2 => __('denied')
            ]
        );
        $show->created_at(trans('admin.created_at'));
        $show->updated_at(trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RequestBackgroundImage);
        $form->display('ID');
        // $form->display('owner_room_id', 'owner_room_id');
        $form->select('owner_room_id', __('admin.owner_room_id'))->options(function () {
            $options = [];
            $users = User::query()->where('id', $this->owner_room_id)->get();
            foreach ($users as $cat) {
                $options[$cat->id] = $cat->uuid . '-' . $cat->name;
            }
            return $options;
        })->ajax('/api/search/users2', 'id', 'name')->default(2)->creationRules('required');
        // $form->select('owner_id', __('owner'))->options('/api/search/users2')->ajax('/api/search/users2', 'id', 'name');

        $form->image('img', 'img')->creationRules('required');
        $form->select('status', 'status')->options(
            [
                0 => __('pending'),
                1 => __('accepted'),
                2 => __('denied')
            ]
        )->default(1);
        $form->number("expair")->default(30);
        $form->hidden("type")->default("admin");
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));

        $form->saving(function (Form $form) {

            $model = $form->model();
            $status = $model->status;
            $user = User::find($model->owner_room_id);
            if (($status == 2) && $user) {
                $costRequestBackGround = Common::getConfig('cost_request_background') ?: 2000;
                $user->di += $costRequestBackGround;
                $user->save();
                CustomNotification::BackgroudRequest($user, 1);
            } elseif (($status == 1) && $user) {
                CustomNotification::BackgroudRequest($user, 0);
            }
        });

        return $form;
    }
}
