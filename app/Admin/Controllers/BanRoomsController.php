<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BanRoomAction;
use App\Models\Ban;
use App\Models\BanRoom;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\BanType;
use App\Admin\Actions\BanUser;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Admin\Actions\DeleteBans;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;


class BanRoomsController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'ban-rooms';

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Close room'))
            ->body($this->grid()));
        // ->row(function ($row) {
        //     $row->column(10, $this->grid());
        //     $row->column(2, view('admin.grid.users.ban'));
        // });
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
            ->title(trans('bans'))
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
            ->title(trans('bans'))
            ->body($this->form()->edit($id)));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('bans'))
            ->body($this->form()));
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BanRoom());

        $grid->model()->whereHas('room')
            ->whereRaw("DATE_ADD(created_at, INTERVAL duration HOUR) > ?", [now()])
            // ->select('id','room_id', 'duration', 'staff_id',  
            //     DB::raw('(SELECT MAX(created_at) FROM bans_rooms WHERE bans_rooms.room_id = bans_rooms.room_id) AS created_at')
            // )
            // ->groupBy(['room_id', 'duration', 'staff_id'])
            ->orderByDesc('created_at');
        $grid->column('id', __('Id'));

        $grid->column('room_id', __('Room'))->display(function () {
            $room = $this->room;

            if (!$room) return '-';
            $name = $room->room_name;
            $room_id = $room->id;
            $defaultImage = asset("images/businessman-icon.jpg");
            $avatarPath = @$room->room_cover;
            $avatar = getImagePath($avatarPath) ?? $defaultImage;

            if (!isImageExists($avatar)) {
                $avatar = $defaultImage;
            }

            $userUrl = admin_url('rooms/' . $room->id);

            return "<div style='display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; background: var(--bg-color);'>
                        <img src='$avatar' alt='User Avatar' style='width: 40px; height: 40px; border-radius: 50%;'>
                        <div>
                            <a href='$userUrl' style='color: var(--primary-color); font-weight: bold; text-decoration: none;'>$name</a><br>
                            <span style='color: var(--uuid-color); font-size: smaller;'>Room Id: $room_id</span><br>
                           
                        </div>
                    </div>";
        });

        $grid->column('user_id', __('owner'))->display(function () {
            $user = $this->room->owner; // العلاقة مع المستخدم
            if (!$user) return '-';

            $name = $user->name;
            $uuid = $user->uuid;
            $phone = $user->phone ?: '-'; // عرض "-" إذا لم يكن هناك رقم
            $defaultImage = asset("images/businessman-icon.jpg");
            $avatarPath = @$user->avatar;
            $avatar = getImagePath($avatarPath) ?? $defaultImage;

            if (!isImageExists($avatar)) {
                $avatar = $defaultImage;
            }

            $userUrl = admin_url('users/' . $user->id);

            return "<div style='display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; background: var(--bg-color);'>
                        <img src='$avatar' alt='User Avatar' style='width: 40px; height: 40px; border-radius: 50%;'>
                        <div>
                            <a href='$userUrl' style='color: var(--primary-color); font-weight: bold; text-decoration: none;'>$name</a><br>
                            <span style='color: var(--uuid-color); font-size: smaller;'>UUID: $uuid</span><br>
                            <span style='color: var(--phone-color); font-size: smaller;'>📞 $phone</span>
                        </div>
                    </div>";
        });

        $grid->column('staff_id', __('staff'))->display(function () {
            if (!$this->staff) return '-';

            $name = $this->staff->name ?? '-';
            $email = $this->staff->email ?? '-';
            $defaultImage = asset("images/admin-icon.png");
            $avatarPath = $this->staff->avatar ?? null;
            $avatar = $avatarPath ? asset($avatarPath) : $defaultImage;

            $adminUrl = admin_url('admin/auth/users/' . $this->staff->id); // تعديل الرابط حسب صفحة الأدمن لديك

            return "<div style='display: flex; align-items: center; gap: 10px;'>
                        <img src='$avatar' alt='Admin Avatar' style='width: 40px; height: 40px; border-radius: 50%;'>
                        <div>
                            <a href='$adminUrl' style='color: #3498db; font-weight: bold; text-decoration: none;'>$name</a><br>
                            <span style='color: #aaa; font-size: smaller;'>$email</span>
                        </div>
                    </div>";
        });

        $grid->duration(__('Duration'));

        $grid->column('created_at', __('Expire'))->display(function () {
            $banExpiration = \Carbon\Carbon::parse($this->created_at)->addHours($this->duration);
            return now()->diffForHumans($banExpiration, true);
        });
        if (Admin::user()->can('delete-' . $this->permission_name)) {
            $grid->column('return', __('Delete'))->display(function () {
                return (new \App\Admin\Actions\DeleteBansRoom($this->id))->render();
            });
        }


        $grid->disableExport();
        $grid->disableRowSelector();
        // $grid->disableActions();
        $grid->disableCreateButton();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('room_id', __('Room Id'));
            });
        });


        $grid->tools(function (Grid\Tools $tools) {
            $tools->append((new BanRoomAction())->render());
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
        //        $show = new Show(Ban::findOrFail($id));
        //
        //        $show->id('ID');
        //        $show->uid('uid');
        //        $show->user_type('user_type');
        //        $show->duration('duration');
        //        $show->type('type');
        //        $show->ip('ip');
        //        $show->device_number('device_number');
        //        $show->staff_id('staff_id');
        //        $show->created_at(trans('admin.created_at'));
        //        $show->updated_at(trans('admin.updated_at'));
        //
        //        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        //        $form = new Form(new Ban);
        //
        //        $form->display('ID');
        //        $form->text('uid', __('uid'));
        ////        $form->text('user_type', __('user_type'));
        //        $form->number('duration', __('duration(hours)'));
        ////        $form->text('type', __('type'));
        //        $form->switch('ban_ip', 'ip_ban')->states ([0=>'off',1=>'on']);
        //        $form->switch('device_ban', __('device_ban'))->states ([0=>'off',1=>'on']);
        //        $form->display(trans('admin.created_at'));
        //
        //
        //        return $form;
    }
}
