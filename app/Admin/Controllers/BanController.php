<?php

namespace App\Admin\Controllers;

use App\Models\Ban;
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


class BanController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'bans';

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title(trans('bans'))
            ->body($this->grid());
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
        return $content
            ->title(trans('bans'))
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
            ->title(trans('bans'))
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
            ->title(trans('bans'))
            ->body($this->form());
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $now = now();
        $grid = new Grid(new Ban);
        $grid->model()->whereHas('user')
            ->whereRaw("DATE_ADD(created_at, INTERVAL duration HOUR) > '$now'")
            ->select('uid', 'duration', 'type', 'device_number', 'staff_id',   DB::raw('(SELECT created_at FROM bans AS b WHERE b.uid = bans.uid AND b.type = bans.type ORDER BY b.id DESC LIMIT 1) AS created_at'), 'ban_type_id')
            ->groupBy(['uid', 'type', 'duration', 'device_number',  'staff_id',  'ban_type_id'])->orderByDesc('created_at');
        //    $grid->id(__ ('ID'));
        // $grid->uid(__('uuid'));
        //        $grid->user_type(__('user_type'));
        $grid->column('user_id', __('User'))->display(function () {
            $user = $this->user; // العلاقة مع المستخدم
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

        $grid->duration(__('duration'));
        $grid->column('type', __('Type'))->display(function ($type) {
            $types = [
                'normal' => __('normal'),
                'ip' => __('ip'),
                'device' => __('device'),
            ];

            return $types[$type] ?? '-';
        });

        // $grid->column('description_ar', __('reason'));
        $grid->column('description_ar', __('reason'))->display(function ($description) {
            $limitedDescription = mb_substr($description, 0, 40) . (mb_strlen($description) > 40 ? '...' : '');
            return "<a href='#' class='view-description' data-description=\"" . htmlentities($description) . "\">$limitedDescription</a>";
        });

        // إضافة سكريبت JavaScript لمعالجة النوافذ المنبثقة
        Admin::script("
            $(document).ready(function () {
                $('.view-description').click(function (e) {
                    e.preventDefault();
        
                    var description = $(this).data('description');
        
                    $('#modalDescriptionTitle').text('" . __('Full Description') . "');
                    $('#modalDescriptionContent').text(description);
        
                    $('#descriptionModal').modal('show');
                });
        
                $('.view-image').click(function (e) {
                    e.preventDefault();
                    var imgSrc = $(this).data('img');
                    $('#modalImageContent').attr('src', imgSrc);
                    $('#imageModal').modal('show');
                });
            });
        ");

        $grid->column('ban_type_id', __('ban_type'))->display(function ($row) {

            $banType = BanType::find($this->ban_type_id);
            $name_ar = $banType->name_ar ?? '';
            $name_en = $banType->name_en ?? '';

            return "$name_ar <br>
         <span style=\"color: #aaa; font-size: smaller;\">  $name_en</span>" ?? "";
        });
        $grid->column('img', trans('image'))->image('', 30);

        $grid->device_number(__('device_number'));
        // $grid->staff_id(__('staff_id'));
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

        $grid->column('created_at', __('expire'))->display(function () {
            // \Carbon\Carbon::createFromTimestamp(strtotime($this->created_at))
            //     ->timezone(auth()->user()->time_zone)->format("Y-m-d h:i A");
            $banExpiration = \Carbon\Carbon::parse($this->created_at)->addHours($this->duration);
            return now()->diffForHumans($banExpiration, true);
        });

        $grid->column('return', __('delete'))->display(function () {
            return (new \App\Admin\Actions\DeleteBans($this->uid, $this->type, $this->ban_type_id))->render();
        });

        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableActions();
        $grid->disableCreateButton();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('uid', __('uuid'));
            });
        });
        
        // $grid->disableTools(); // Disable default tools
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append((new BanUser())->render());
        });
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
