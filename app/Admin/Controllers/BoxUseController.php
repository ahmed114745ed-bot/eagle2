<?php

namespace App\Admin\Controllers;

use App\Models\BoxUse;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class BoxUseController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'box-use';
    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return parent::index($content
            ->header(trans('Dumped boxes'))
            ->description(trans('admin.description'))
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
        return parent::show($id,$content
            ->header(trans('admin.detail'))
            ->description(trans('admin.description'))
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
        return parent::edit($id,$content
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
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
    // protected function grid()
    // {
    //     $grid = new Grid(new BoxUse);

    //     $grid->id( __ ('ID'));
    //     $grid->box_id( __ ('box_id'));
    //     $grid->user_id(__ ('user_id'));
    //     $grid->coins(__ ('coins'));
    //     $grid->end_at(__ ('end_at'));
    //     $grid->room_uid(__ ('room_uid'));
    //     $grid->room_id(__ ('room_id'));
    //     $grid->users_num(__ ('users_num'));
    //     $grid->type(__ ('type'));
    //     $grid->label(__ ('label'));
    //     $grid->used_num(__ ('used_num'));
    //     $grid->not_used_num( __ ('not_used_num'));
    //     $grid->disableCreateButton ();
    //     $grid->disableExport();
    //     return $grid;
    // }


    protected function grid()
    {
        $grid = new Grid(new BoxUse);
    
        $grid->id(__('ID'));
        
        $grid->column('user_id', __('User'))->display(function () {
            $user = $this->user;
            if (!$user) return '-';
        
            $name = $user->name;
            $uuid = $user->uuid;
            $phone = $user->phone ?: '-';
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
        
        $grid->column('box_id', __('Box'))->display(function () {
            $box = $this->box;
            if (!$box) return '-';
        
            $name = $box->name ?? 'Unknown Box';
            $boxUrl = admin_url('boxes/' . $box->id);
        
            $defaultImage = asset("images/box-icon.jpg");
            $avatarPath = $box->image ?? null;
            $avatar = $avatarPath ? getImagePath($avatarPath) : $defaultImage;
        
            if (!isImageExists($avatar)) {
                $avatar = $defaultImage;
            }
        
            return "<a href='$boxUrl' style='text-decoration: none;'>
                        <div style='display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; background: var(--bg-color);'>
                            <img src='$avatar' alt='Box Image' style='width: 40px; height: 40px; border-radius: 50%;'>
                            <span style='color: var(--primary-color); font-weight: bold;'>$name</span>
                        </div>
                    </a>";
        });
        
        $grid->column('room_id', __('Room'))->display(function () {
            $room = $this->room;
            if (!$room) return '-';
        
            $name = $room->room_name ?? 'Unknown Room';
            $roomUrl = admin_url('rooms/' . $room->id);
        
            $defaultImage = asset("images/default-room.jpg");
            $roomImagePath = $room->room_cover ?? null;
            $roomImage = $roomImagePath ? getImagePath($roomImagePath) : $defaultImage;
        
            if (!isImageExists($roomImage)) {
                $roomImage = $defaultImage;
            }
        
            return "<a href='$roomUrl' style='text-decoration: none;'>
                        <div style='display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; background: var(--bg-color);'>
                            <img src='$roomImage' alt='Room Image' style='width: 40px; height: 40px; border-radius: 8px;'>
                            <span style='color: var(--primary-color); font-weight: bold;'>$name</span>
                        </div>
                    </a>";
        });
        
        
        
        $grid->coins(__('coins'));
        $grid->end_at(__('end_at'));
        // $grid->room_uid(__('room_uid'));
        $grid->users_num(__('users_num'));
        $grid->type(__('type'));
        $grid->label(__('label'));
        $grid->used_num(__('used_num'));
        $grid->not_used_num(__('not_used_num'));
        
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
        $show = new Show(BoxUse::findOrFail($id));

        $show->id('ID');
        $show->box_id('box_id');
        $show->user_id('user_id');
        $show->coins('coins');
        $show->end_at('end_at');
        $show->room_uid('room_uid');
        $show->room_id('room_id');
        $show->users_num('users_num');
        $show->type('type');
        $show->label('label');
        $show->used_num('used_num');
        $show->not_used_num('not_used_num');
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
        $form = new Form(new BoxUse);

        $form->display('ID');
        $form->text('box_id', 'box_id');
        $form->text('user_id', 'user_id');
        $form->text('coins', 'coins');
        $form->text('end_at', 'end_at');
        $form->text('room_uid', 'room_uid');
        $form->text('room_id', 'room_id');
        $form->text('users_num', 'users_num');
        $form->text('type', 'type');
        $form->text('label', 'label');
        $form->text('used_num', 'used_num');
        $form->text('not_used_num', 'not_used_num');
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));

        return $form;
    }
}
