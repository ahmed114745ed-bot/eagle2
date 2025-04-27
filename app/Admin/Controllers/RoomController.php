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
        $grid->model()->with('owner.profile','owner:uuid,id,name',)
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

        
        


        // $grid->column(__('microphone'))->display(function () {
        //     $ids = explode(',', $this->microphone);
        //     $cachedUsers = \App\Models\User::whereIn('id', $ids)
        //         ->with(['profile:user_id,avatar'])
        //         ->get(['id', 'name']);
            
        //     if ($cachedUsers->isEmpty()) {
        //         return '';
        //     }
            
        //     $html = '
        //     <div class="avatar-container" style="
        //         display: flex;
        //         overflow-x: auto;
        //         overflow-y: hidden; /* Hides vertical scroll */
        //         white-space: nowrap;
        //         padding: 8px 0;
        //         scrollbar-width: thin;
        //         scrollbar-color: #cccrgb(43, 183, 36);
        //         max-width: 100%;
        //     ">';
            
        //     foreach ($cachedUsers as $user) {
        //         $path = $user->profile?->avatar;
        //         $defaultImage = asset("images/businessman-icon.jpg");
        //         $url = isImageExists(getImagePath($path)) ? getImagePath($path) : $defaultImage;
        //         $username = htmlspecialchars($user->name ?? 'Unknown');
                
        //         $html .= '
        //             <div style="flex: 0 0 auto; text-align: center; position: relative; margin-right: -8px;">
        //                 <img src="'.$url.'" 
        //                      title="'.$username.'" 
        //                      style="width: 36px; height: 36px; border-radius: 50%; 
        //                             object-fit: cover; border: 2px solid white;
        //                             box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        //                             transition: transform 0.2s ease;
        //                             margin-right: 0;"/>
        //                 <span style="position: absolute; bottom: -18px; left: 50%; 
        //                             transform: translateX(-50%); font-size: 10px; 
        //                             white-space: nowrap; display: none;
        //                             background: rgba(0,0,0,0.7); color: white;
        //                             padding: 2px 5px; border-radius: 3px;">
        //                     '.substr($username, 0, 12).'
        //                 </span>
        //             </div>';
        //     }
            
        //     $html .= '</div>';
            
        //     // Hover effect
        //     $html .= '
        //     <script>
        //     document.addEventListener("DOMContentLoaded", function() {
        //         document.querySelectorAll(".avatar-container img").forEach(img => {
        //             img.addEventListener("mouseenter", function() {
        //                 this.style.transform = "scale(1.3)";
        //                 this.style.zIndex = "10";
        //                 this.nextElementSibling.style.display = "block";
        //             });
        //             img.addEventListener("mouseleave", function() {
        //                 this.style.transform = "scale(1)";
        //                 this.style.zIndex = "1";
        //                 this.nextElementSibling.style.display = "none";
        //             });
        //         });
        //     });
        //     </script>';
            
        //     return $html;
        // });

        // $grid->column(__('microphone'))->display(function () {
        //     $ids = explode(',', $this->microphone);
        //     $cachedUsers = \App\Models\User::whereIn('id', $ids)
        //         ->with(['profile:user_id,avatar'])
        //         ->get(['id', 'name']);
            
        //     if ($cachedUsers->isEmpty()) {
        //         return '';
        //     }
            
        //     $html = '
        //     <div class="scroll" style="
        //         overflow: auto;
        //         white-space: nowrap;
        //         padding: 8px 0;
        //         max-width: 100%;
        //     ">';
            
        //     foreach ($cachedUsers as $user) {
        //         $path = $user->profile?->avatar;
        //         $defaultImage = asset("images/businessman-icon.jpg");
        //         $url = isImageExists(getImagePath($path)) ? getImagePath($path) : $defaultImage;
        //         $username = htmlspecialchars($user->name ?? 'Unknown');
                
        //         $html .= '
        //             <div style="display: inline-block; text-align: center; margin-right: 12px;">
        //                 <img src="'.$url.'" 
        //                      title="'.$username.'" 
        //                      style="width: 60px; height: 60px; border-radius: 50%; 
        //                             object-fit: cover; border: 2px solid white;
        //                             box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        //                             transition: transform 0.2s ease;"/>
        //                 <span style="font-size: 10px; white-space: nowrap; display: block; margin-top: -10px;">
        //                     '.substr($username, 0, 12).'
        //                 </span>
        //             </div>';
        //     }
            
        //     $html .= '</div>';
            
        //     // Hover effect
        //     $html .= '
        //     <script>
        //     document.addEventListener("DOMContentLoaded", function() {
        //         document.querySelectorAll(".scroll img").forEach(img => {
        //             img.addEventListener("mouseenter", function() {
        //                 this.style.transform = "scale(1.3)";
        //                 this.style.zIndex = "10";
        //             });
        //             img.addEventListener("mouseleave", function() {
        //                 this.style.transform = "scale(1)";
        //                 this.style.zIndex = "1";
        //             });
        //         });
        //     });
        //     </script>';
            
        //     return $html;
        // });
        

        $grid->column(__('microphone'))->display(function () {
            $ids = explode(',', $this->microphone);
            $cachedUsers = \App\Models\User::whereIn('id', $ids)
                ->with(['profile:user_id,avatar'])
                ->get(['id', 'name']);
            
            if ($cachedUsers->isEmpty()) {
                return '';
            }
            
            $html = '
            <div class="scroll" style="
                overflow-x: auto;
                white-space: nowrap;
                padding: 8px 0;
                max-width: calc(4 * 72px); /* 4 images with 12px margin each */
            ">';
            
            foreach ($cachedUsers as $user) {
                $path = $user->profile?->avatar;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = isImageExists(getImagePath($path)) ? getImagePath($path) : $defaultImage;
                $username = htmlspecialchars($user->name ?? 'Unknown');
                
                $html .= '
                    <div style="display: inline-block; text-align: center; margin-right: 12px;">
                        <img src="'.$url.'" 
                             title="'.$username.'" 
                             style="width: 50px; height: 50px; border-radius: 50%; 
                                    object-fit: cover; border: 2px solid white;
                                    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
                                    transition: transform 0.2s ease;"/>
                        
                    </div>';
            }
            
            $html .= '</div>';
            
            // Hover effect
            $html .= '
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll(".scroll img").forEach(img => {
                    img.addEventListener("mouseenter", function() {
                        this.style.transform = "scale(1.3)";
                        this.style.zIndex = "10";
                    });
                    img.addEventListener("mouseleave", function() {
                        this.style.transform = "scale(1)";
                        this.style.zIndex = "1";
                    });
                });
            });
            </script>';
            
            return $html;
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
