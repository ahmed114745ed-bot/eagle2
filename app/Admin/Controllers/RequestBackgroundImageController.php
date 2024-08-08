<?php

namespace App\Admin\Controllers;

use App\Models\RequestBackgroundImage;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Models\Room;
use App\Models\User;
use App\Helpers\Common;
use App\Facades\CustomNotification;

class RequestBackgroundImageController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
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
            ->header(trans('admin.detail'))
            ->description(trans('admin.description'))
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
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
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
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
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
        $grid->model ()->orderByDesc('id');
        $grid->id(__('admin.ID'));
        $grid->owner_room_id(__('admin.owner_room_id'));
        $grid->img(__('admin.img'))->image ('',30);;
        $grid->status(__('status'))->using (
            [
                0=>__('pending'),
                1=>__ ('accepted'),
                2=>__ ('denied')
            ]
        );
        $grid->created_at(trans('admin.created_at'));
        $grid->updated_at(trans('admin.updated_at'));
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
        $show->img('img')->image ('',30);
        $show->status(__('status'))->using (
            [
                0=>__('pending'),
                1=>__ ('accepted'),
                2=>__ ('denied')
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
        $form->select('owner_room_id',__('admin.owner_room_id'))->options(function () {
            $options = [];
            $users = User::query()->where('id',$this->owner_room_id)->get();
            foreach ($users as $cat) {
                $options[$cat->id] = $cat->uuid .'-'.$cat->name;
            }
            return $options;
    })->ajax('/api/search/users2', 'id', 'name')->default(2)->creationRules('required');
        // $form->select('owner_id', __('owner'))->options('/api/search/users2')->ajax('/api/search/users2', 'id', 'name');

        $form->image('img', 'img')->creationRules('required');
        $form->select('status', 'status')->options (
            [
                0=>__('pending'),
                1=>__ ('accepted'),
                2=>__ ('denied')
            ]
        )->default(1);
        $form->number("expair")->default(30);
        $form->hidden("type")->default("admin");
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));

        $form->saving(function (Form $form) {
            
            $model = $form->model();
            $status = $model->status;
            $user=User::find($model->owner_room_id);
            if (($status == 2) && $user) {
                $costRequestBackGround = Common::getConfig('cost_request_background') ?: 2000;
                $user->di += $costRequestBackGround;
                $user->save();
                CustomNotification::BackgroudRequest($user,1);
            } elseif (($status == 1) && $user) {
                CustomNotification::BackgroudRequest($user,0);
            }

        });

        return $form;
    }
}
