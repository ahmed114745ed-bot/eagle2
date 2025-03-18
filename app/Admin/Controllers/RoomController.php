<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\Room;
use App\Http\Controllers\Controller;
use App\Models\RoomCategory;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;

use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class RoomController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'rooms';
    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Rooms'))
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
            ->title(trans('Rooms'))
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
            ->title(trans('Rooms'))
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
            ->title(trans('Rooms'))
            ->body($this->form()));
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Room);
        $grid->model()->with('owner.profile')
            ->orderByDesc('rooms.pin')->whereHas('owner')
            ->orderByDesc('rooms.top_room')
            ->orderByDesc('session')
            ->orderByDesc('count_room_socket');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                //  $filter->equal('uid',__ ('owner id'));
                $filter->where(function ($query) {
                    $input = $this->input;

                    $query->whereHas('owner', function ($query) use ($input) {
                        $query->where('name', 'like', "%$input%")
                            ->orWhere('uuid', 'like', "%$input%");
                    });
                }, __('User'))->placeholder(__('Search by name or numId'));
            });
        });

        $grid->id(__('ID'));
        $grid->column('room_name', __('room'))->display(function ($name) {

            $path = @$this->room_cover;
            $defaultImage = asset("images/room.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                        <span  cursor: pointer;'>$name</span>
                        </a>
                    </div>

                </div>
            ";
        });
        $grid->column('owner.name', __('room owner'))->display(function ($name) {
            $uid = @$this->owner->uuid;
            $path = @$this->owner?->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
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


        $grid->column(__('status'))->display(function () {
            return (new \App\Admin\Actions\RoomAction(
                $this->id,
                $this->room_status,
                $this->top_room,
                $this->is_afk,
                $this->pin
            ))->render();
        });

        $grid->column('max_admin', __('Max Admin'))->display(function ($maxAdmin) {
            $maxRoomAdmin = Common::getConfig('max_room_admin');
            return count($this->admins) . '/' . ($maxAdmin ?? $maxRoomAdmin);
        });
        $grid->column('count_room_socket', __('Number of users'));

        $grid->column(__('microphone'))
            ->display(function () {
                $ids = explode(',', $this->microphone);
                $cachedUsers = \App\Models\User::whereIn('id', $ids)->take(5)->get();

                // Check if there are no users
                if ($cachedUsers->isEmpty()) {
                    return '';
                }

                $html = '<div style="display: flex; gap: 10px; align-items: center;">';

                foreach ($cachedUsers as $user) {
                    $path = @$user->profile?->avatar;
                    $defaultImage = asset("images/businessman-icon.jpg");
                    $url = $path ? getImagePath($path) : $defaultImage;

                    if (!isImageExists($url)) {
                        $url = $defaultImage;
                    }

                    $html .= '
            <div style="position: relative; margin-left: -20px;">
                <img src="' . $url . '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid white;"/>
            </div>';
                }

                // Add Font Awesome icon for "more info"
                $url1 = url('admin/room-mic/' . $this->id);
                $arrowIcon = asset('images/add.png');

                if (count($ids) > 5) {
                    $html .= '
        <div>
            <a href="' . $url1 . '" 
               style="text-decoration: none; color: black; font-size: 20px; cursor: pointer;">
                <img src="' . $arrowIcon . '" style="width: 30px; height: 30px;">
            
            </a>
        </div>';

                    $html .= '</div>';
                    return $html;
                }
            });




        $grid->actions(function ($action) {
            $action->disableView();
        });
        $grid->disableCreateButton();
        $grid->disableExport();
        $this->extendGrid($grid);
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
        $show = new Show(Room::findOrFail($id));
        //$show->id('ID');
        //$show->numid('numid');
        //$show->uid('uid');
        //$show->room_status('room_status');
        //$show->room_name('room_name');
        //$show->room_cover('room_cover');
        //$show->room_intro('room_intro');
        //$show->room_pass('room_pass');
        //$show->room_class('room_class');
        //$show->room_type('room_type');
        //$show->room_welcome('room_welcome');
        //$show->room_admin('room_admin');
        //$show->room_visitor('room_visitor');
        //$show->room_speak('room_speak');
        //$show->room_sound('room_sound');
        //$show->room_black('room_black');
        //$show->week_star('week_star');
        //$show->ranking('ranking');
        //$show->is_popular('is_popular');
        //$show->secret_chat('secret_chat');
        //$show->is_top('is_top');
        //$show->sort('sort');
        //$show->room_background('room_background');
        //$show->microphone('microphone');
        //$show->super_uid('super_uid');
        //$show->is_afk('is_afk');
        //$show->hot('hot');
        //$show->room_judge('room_judge');
        //$show->is_prohibit_sound('is_prohibit_sound');
        //$show->openid('openid');
        //$show->commission_proportion('commission_proportion');
        //$show->fresh_time('fresh_time');
        //$show->start_hour('start_hour');
        //$show->end_hour('end_hour');
        //$show->is_recommended('is_recommended');
        //$show->play_num('play_num');
        //$show->free_mic('free_mic');
        //$show->created_at(__('admin.created_at'));
        //$show->updated_at(__('admin.updated_at'));
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
        $form = new Form(new Room);

        $form->display(__('ID'));
        $form->text('numid', __('numid'));
        $form->switch('room_status', __('room status'))->options(Common::getSwitchStates());
        $form->switch('top_room', __('top room'))->options(Common::getSwitchStates());
        $form->switch('pin', __('pin'))->options(Common::getSwitchStates());
        $form->text('max_admin', __('max admin'));
        $form->text('room_name', __('room name'));
        $form->image('room_cover', __('room cover'));
        $form->text('room_intro', __('room intro'));
        $form->text('room_pass', __('room pass'));
        $form->hidden('is_afk', __('owner in'));
        $form->select('room_class')->options(function () {
            $options = [];
            $cats = RoomCategory::query()->where('enable', 1)->where('parent_id', 0)->get();
            foreach ($cats as $cat) {
                $options[$cat->id] = $cat->name;
            }
            return $options;
        });
        $form->select('room_type', __('room type'))->options(function () {
            $options = [];
            $cats = RoomCategory::query()->where('enable', 1)->where('parent_id', $this->room_class)->get();
            foreach ($cats as $cat) {
                $options[$cat->id] = $cat->name;
            }
            return $options;
        });
        $form->text('room_welcome', __('room welcome'));
        $form->number('sort_num', __('Sort Num'));


        return $form;
    }
}
