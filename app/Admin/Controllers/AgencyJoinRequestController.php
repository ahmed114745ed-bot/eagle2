<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use Encore\Admin\Layout\Content;
use App\Models\AgencyJoinRequest;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Actions\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use App\Services\AppFeatureService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Encore\Admin\Controllers\HasResourceActions;

class AgencyJoinRequestController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'agency-join-requests';


    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("agencies");
    }



    public function update($id)
    {

        if (request('_edit_inline') == "true") {
            if (request('status')) {
                request()->request->add(['change_status_admin_id' => Auth::id()]);
            }
        }
        return $this->form()->update($id);
    }

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Join To Agency Requests'))
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
            ->title(trans('Join To Agency Requests'))
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
            ->title(trans('Join To Agency Requests'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Join To Agency Requests'))
            ->body($this->form()));
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AgencyJoinRequest);
        $grid->model()->orderByDesc('id');
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('status', __('status'))->select([0 => 'pending', 1 => 'accepted', 2 => 'denied']);
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('agency.id', __('agency id'));
            });
        });

        $grid->id(__('ID'));
        // $grid->column('user_id', __('user id'))->modal('user info', function ($model) {
        //     if ($model->user_id) {
        //         return Common::getUserShow($model->user_id);
        //     }
        //     return null;
        // });
        // $grid->column('agency_id', __('agency id'))->modal('agency info', function ($model) {
        //     if ($model->agency_id) {
        //         return Common::getAgencyShow($model->agency_id);
        //     }
        //     return null;
        // });
        $grid->column('user.name', __('User'))
            ->display(function ($name) {
                $uid = @$this->user->uuid;
                $path = @$this->user?->profile?->avatar ?? asset("images/businessman-icon.jpg");
                $url = getImagePath($path);
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
        $grid->column('agency.name', __('Agency'))
            ->display(function ($name) {
                $path = @$this->agency->img;
                $url = getImagePath($path);
                $image = handleShowImageWithTypes($this->id, $url, 40, 40);

                return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <span>$name</span>
                </div>
            ";
            });
        $grid->column('whatsapp', __('whatsapp'));
        $grid->column('status', __('status'))->using(
            [
                0 => __('pending'),
                1 => __('accepted'),
                2 => __('denied')
            ]
        );
        $grid->column('change_status_admin_id', __('change status admin id'))->modal('admin info', function ($model) {
            if ($model->change_status_admin_id) {
                return Common::getAdminShow($model->change_status_admin_id);
            }
            return null;
        });
        $grid->column('created_at', trans('time'))->diffForHumans();
        $this->extendGrid($grid);


        $grid->disableCreateButton();
        $grid->disableExport();
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
        $show = new Show(AgencyJoinRequest::findOrFail($id));

        //        $show->id('ID');
        //        $show->user_id('user_id');
        //        $show->agency_id('agency_id');
        //        $show->status('status');
        //        $show->change_status_admin_id('change_status_admin_id');
        //        $show->created_at(trans('admin.created_at'));
        //        $show->updated_at(trans('admin.updated_at'));

        $this->extendShow($show);

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new AgencyJoinRequest);
        $form->display(__('ID'));
        $form->text('user_id', __('user id'));
        $form->text('agency_id', __('agency id'));
        $form->select('status', __('status'))->options(
            [
                0 => __('pending'),
                1 => __('accepted'),
                2 => __('denied')
            ]
        );;
        $form->hidden('change_status_admin_id', 'change_status_admin_id');
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));
        $form->saving(function (Form $form) {
            $user = User::query()->where('id', $form->model()->user_id)->first();
            if (($user->agency_id)) {
                $error = new MessageBag(
                    [
                        'title'   => 'forbidden',
                        'message' => 'user already in agency',
                    ]
                );
                return back()->with(compact('error'));
            }

            if ($form->status == 1) {
                UserCommon::userVip($user);

                $user_id = $form->model()->user_id;

                $update = DB::table('users')
                    ->where('id', $user_id)
                    ->update(['type_user' => 1]);

                if (!$update) {
                    $error = new MessageBag([
                        'title' => 'Error',
                        'message' => 'Failed to update user',
                    ]);
                }
            }
        });


        return $form;
    }
}
