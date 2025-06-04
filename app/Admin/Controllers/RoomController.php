<?php

namespace App\Admin\Controllers;

use App\Models\Country;
use App\Models\Room;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use App\Admin\Actions\DenyDeleteAction;
use App\Helpers\Common;
use App\Models\EnteredRoom;
use App\Models\RoomCategory;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Carbon;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Layout\Content;
use App\Admin\Actions\RoomPinAction;
use App\Http\Controllers\Controller;
use App\Admin\Actions\CloseRoomAction;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Controllers\HasResourceActions;

class RoomController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'rooms';

    public function index(Content $content)
    {
        $content = $content->title(trans('Rooms'));

        if (Admin::user()->can('actions-switch' . $this->permission_name) || Admin::user()->can('*')) {
            $content = $content->row(function (Row $row) {
                $row->column(12, $this->grid2());
            });
        }

        $content = $content->body($this->grid());

        return parent::index($content);
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

    protected function grid2()
    {
        $make_rooms_top = settings()->get('make_rooms_top');


        return (new Box(
            title: __('admin.Actions'),
            content: view('admin.grid.users.RoomsChange', compact(['make_rooms_top'])),
        ));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Room);

        $filterType = request('filter', 'all'); // Fetch from query string

        $user = auth()->user();
        $grid->header(function () use ($filterType) {
            $tabs = [
                'all'       => __('All'),
                'trend'     => __('Trend'),
                'popular'   => __('Popular'),
                'boss'      => __('Boss'),
                //'friends'   => 'Friends',
                //'following' => 'Following',
                'recently'  => __('Recently'),
                'interested' => __('Interested'),
                'nearby'    => __('Nearby'),
                'last_create' => __('New'),
                'pk'        => __('PK'),
                'party'     => __('Party'),
                'festival'  => __('Festival'),
                'top_gift'  => __('Top Gift'),
            ];

            $html = '<div class="nav-tabs-custom"><ul class="nav nav-tabs">';
            foreach ($tabs as $key => $label) {
                $active = $filterType === $key ? 'active' : '';
                $url = request()->fullUrlWithQuery(['filter' => $key]);
                $html .= "<li class='{$active}'><a href='{$url}' class='tab-link'>{$label}</a></li>";
            }
            $html .= '</ul></div>';

            $html .= <<<HTML
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const tabLinks = document.querySelectorAll('.tab-link');
                                const loader = document.getElementById('tab-loading');

                                tabLinks.forEach(function (tab) {
                                    tab.addEventListener('click', function (e) {
                                        e.preventDefault();
                                        loader.style.display = 'block';
                                        tabLinks.forEach(t => t.style.pointerEvents = 'none');
                                        setTimeout(() => {
                                            window.location.href = tab.getAttribute('href');
                                        }, 300);
                                    });
                                });
                            });
                        </script>
                        HTML;

            return $html;
        });
        $grid->model()
            ->select('*', \DB::raw("CASE room_status
                WHEN 1 THEN 100
                WHEN 2 THEN 10
                ELSE 80
            END AS status_priority"))
            ->with('owner.profile', 'owner:uuid,id,name')
            ->withCount('roomVisitors')
            ->whereHas('owner')
            ->orderByDesc('status_priority')
        ;

        $topRooms = (settings()->get('make_rooms_top') == 1) ?? false;
        if ($topRooms) {
            $grid->model()->orderByDesc('room_visitors_count');
        }

        switch ($filterType) {
            case 'boss':
                $roomIds = EnteredRoom::query()
                    ->where('uid', $user->id)
                    ->orderByDesc('entered_at')
                    ->pluck('rid')
                    ->toArray();
                $grid->model()->orderByDesc('pin')->whereIn('id', $roomIds);
                break;

            case 'trend':
                $grid->model()->orderByDesc('top_room')
                    ->orderByDesc('pin')
                    ->orderByDesc('room_visitors_count')
                    ->orderByDesc('session');
                break;

            case 'popular':
                $grid->model()->orderByDesc('top_room')
                    ->orderByDesc('pin')
                    ->orderByDesc('room_visitors_count');
                break;

            case 'last_create':
                $grid->model()->whereDate('created_at', '>=', Carbon::now()->subDays(3))
                    ->orderByDesc('pin')
                    ->orderByDesc('id');
                break;

            case 'pk':
                $grid->model()->has('lastPk')->orderByDesc('pin');
                break;

            case 'party':
                $grid->model()->whereHas('roomCategory', function ($query) {
                    $query->where('type', 'party');
                })->orderByDesc('pin');
                break;

            case 'festival':
            case 'recently':
                $grid->model()
                    ->orderByDesc('pin')
                    ->orderByDesc('top_room')
                    ->orderByDesc('room_visitors_count')
                    ->orderByDesc('session');
                break;

            case 'interested':
                $roomTypes = EnteredRoom::query()
                    ->where('uid', $user->id)
                    ->where('entered_at', '>=', Carbon::now()->subDay())
                    ->with('room')
                    ->get()
                    ->pluck('room.room_type')
                    ->unique();
                $grid->model()->whereIn('room_type', $roomTypes)
                    ->orderByDesc('pin')
                    ->orderByDesc('top_room')
                    ->orderByDesc('session');
                break;

            /* case 'following':
                    $grid->model()->whereIn('uid', $user->followeds_ids())
                        ->orderByDesc('top_room')
                        ->orderByDesc('room_visitors_count')
                        ->orderByDesc('session');
                    break;

                case 'friends':
                    $grid->model()->whereIn('uid', $user->friends_ids())
                        ->orderByDesc('top_room')
                        ->orderByDesc('room_visitors_count')
                        ->orderByDesc('session');
                    break; */

            case 'nearby':
                $userLat  = $user->lat;
                $userLong = $user->long;
                $grid->model()->selectRaw(
                    'rooms.*,
                    (6371 * acos(cos(radians(?)) * cos(radians(owner.lat))
                    * cos(radians(owner.long) - radians(?)) + sin(radians(?))
                    * sin(radians(owner.lat)))) AS distance',
                    [$userLat, $userLong, $userLat]
                )
                    ->join('users as owner', 'rooms.uid', '=', 'owner.id')
                    ->orderByDesc('pin')
                    ->orderBy('distance');
                break;

            case 'top_gift':
                $grid->model()
                    ->withSum('gifts as total_gift_exp', 'giftPrice')
                    ->orderByDesc('total_gift_exp');
                break;

            default:
                $grid->model()
                    ->orderByDesc('pin')
                    ->orderByDesc('hour_hot');
                //                $grid->model()->orderByDesc('rooms.pin')
                //                    ->orderByDesc('rooms.top_room')
                //                    ->orderByDesc('session')
                //                    ->orderByDesc('count_room_socket');
                break;
        }
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('owner', function ($query) use ($input) {
                        $query->where('name', 'like', "%$input%")
                            ->orWhere('uuid', 'like', "%$input%");
                    });
                }, __('User'))->placeholder(__('Search by name or numId'));

                $filter->where(function ($query) {
                    if ($this->input) {
                        $query->whereHas('owner', function ($query) {
                            $query->where('country_id', $this->input);
                        });
                    }
                }, __('Country'))->select(
                    Country::query()->pluck('name', 'id')
                );

//                $filter->equal('room_status', __('Room Status'))->select([
//                    1 => __('Active'),
//                    0 => __('Inactive'),
//                    2 => __('Closed'),
//                    3 => __('Banned'),
//                    4 => __('Closed'),
//                ]);
            });
        });

        $grid->disableRowSelector();

        $grid->column('pin', __('Pin Status'))->display(function ($pin) {
            return $pin == 1
                ? '<span class="text-success"> <i class="fa fa-thumb-tack"></i></span>'
                : '<span class="text-muted"> </span>';
        });

        $grid->id(__('ID'));

        $grid->column('room_name', __('room'))->display(function ($name) {

            $path = @$this->room_cover;
            $id = @$this->id;
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
                        <span  cursor: pointer;'>$name</span><br>
                        <span  cursor: pointer;'>ID: $id</span>
                        </a>
                    </div>

                </div>
            ";
        });
        $grid->column('owner.name', __('room owner'))->display(function ($name) {
            $uid = @$this->owner->uuid;
            $id = @$this->owner->id;
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
                    <span style='color: #aaa; font-size: smaller;'>ID: $id</span><br>
                    <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                </div>
            </div>
        ";
        });


        //     $grid->column('pin', __('Pin'))->display(function ($pin) {
        //         $roomId = $this->id;
        //         $isPinned = $pin ? 'true' : 'false';
        //         $pinIcon = $pin ? 'fa-check-circle' : 'fa-thumb-tack';
        //         $pinColor = $pin ? 'text-success' : 'text-muted';

        //         return <<<HTML
        // <button class="btn btn-sm {$pinColor} pin-room-btn"
        //         data-room="{$roomId}"
        //         data-pinned="{$isPinned}">
        //     <i class="fa {$pinIcon}"></i>
        // </button>
        // HTML;
        //     });
        /*         $grid->column(__('status'))->display(function () {
            return (new \App\Admin\Actions\RoomAction(
                $this->id,
                $this->room_status,
                $this->top_room,
                $this->is_afk,
                $this->pin
            ))->render();
        }); */

        $grid->column('max_admin', __('Max Admin'))->display(function ($maxAdmin) {
            $maxRoomAdmin = Common::getConfig('max_room_admin');
            return count($this->admins) . '/' . ($maxAdmin ?? $maxRoomAdmin);
        });
        $grid->column('count_room_socket', __('Number of users'));

        $grid->column(__('microphone'))->display(function () {
            $ids = explode(',', $this->microphone);
            $cachedUsers = \App\Models\User::whereIn('id', $ids)
                ->with(['profile:user_id,avatar'])
                ->get(['id', 'name']);

            if ($cachedUsers->isEmpty()) {
                return '';
            }

            $html = '<div class="image-container">';

            foreach ($cachedUsers as $user) {
                $path = $user->profile?->avatar;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = isImageExists(getImagePath($path)) ? getImagePath($path) : $defaultImage;
                $username = htmlspecialchars($user->name ?? 'Unknown');
                $userUrl = route('admin.users.show', $user->id); // Assuming you have a route like this

                $html .= '
                    <div class="image-wrapper" onclick="window.location.href=\'' . $userUrl . '\'">
                        <img src="' . $url . '"
                             title="' . $username . '"
                             style="width: 40px; height: 40px; border-radius: 50%;
                                    object-fit: cover; border: 2px solid white;
                                    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
                                    transition: transform 0.3s ease;"/>
                    </div>';
            }

            $html .= '</div>';

            // CSS for styling
            $html .= '
            <style>
                .image-container {
                    display: flex;
                    justify-content: start;
                    align-items: center;
                    gap: -10px; /* Overlap the images slightly */
                    padding: 8px 0;
                    overflow-y: overlay;
                    width: 218px;
                    padding-right: 16px;
                }

                .image-wrapper {
                    display: inline-block;
                    position: relative;
                        margin-right: -12px;
                }

                .image-wrapper img {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 2px solid #fff; /* White border for better contrast */
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    cursor: pointer;
                }

                .image-wrapper img:hover {
                    transform: scale(1.2); /* Slightly enlarge image on hover */
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3); /* More pronounced shadow on hover */
                }

                /* Optional: If you want to add a tooltip style for the images */
                .image-wrapper img[title] {
                    cursor: pointer; /* Change cursor to indicate interactivity */
                }

                .image-wrapper img[title]:hover {
                    opacity: 0.8; /* Slight opacity change on hover */
                }
            </style>';

            return $html;
        });

        $permissionName = $this->permission_name;

        $grid->actions(function ($action) use ($permissionName){
            $action->disableView();
            $pin = $action->row->pin;
            $model = $action->row;
            // إضافة الفعل مع تمرير الـ pin
            if (Admin::user()->can('pin-switch-' . $permissionName) || Admin::user()->can('*')) {

                $action->add(new RoomPinAction($action->row->id, $pin));
            }
            if (Admin::user()->can('close-switch-' . $permissionName) || Admin::user()->can('*')) {

                $action->add(new CloseRoomAction($model->id));
            }
        });

        $grid->disableCreateButton();
        $grid->disableExport();
        $this->extendGrid($grid);

        $this->setupPinModalScript();

        return $grid;
    }
    public function updatePinStatus($id, Request $request)
    {
        try {
            $room = Room::findOrFail($id);
            $room->pin = request('pin');
            $room->save();

            return response()->json([
                'success' => true,
                'message' => request('pin')
                    ? 'Room pinned successfully'
                    : 'Room unpinned successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating pin status: ' . $e->getMessage()
            ]);
        }
    }
    protected function setupPinModalScript()
    {
        $token = csrf_token();

        $confirm = __('Confirm Pin Room');
        $doyouwant = __('Do you want to pin this room to the top?');
        $confirm = __('Confirm');
        $cancel  = __('admin.cancel');
        Admin::html(<<<HTML
<div class="modal fade" id="pinRoomModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{$confirm}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{$doyouwant}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{$cancel}</button>
                <button type="button" class="btn btn-primary confirm-pin">{$confirm}</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var currentRoomId = null;
    var currentBtn = null;

    $('.pin-room-btn').click(function() {
        currentRoomId = $(this).data('room');
        currentBtn = $(this);
        var isPinned = $(this).data('pinned') === 'true';

        if (isPinned) {
            // If already pinned, unpin immediately without confirmation
            updatePinStatus(currentRoomId, false);
        } else {
            // Show confirmation modal for pinning
            $('#pinRoomModal').modal('show');
        }
    });

    $('.confirm-pin').click(function() {
        $('#pinRoomModal').modal('hide');
        updatePinStatus(currentRoomId, true);
    });

    function updatePinStatus(roomId, pin) {
        $.ajax({
            url: '/admin/rooms/' + roomId + '/update-pin-status',
            type: 'POST',
            data: {
                pin: pin ? 1 : 0,
                _token: '{$token}',
                _method: 'PUT'
            },
            success: function(response) {
                if (response.success) {
                    // Update button appearance without reloading
                    currentBtn.data('pinned', pin ? 'true' : 'false');
                    currentBtn.find('i')
                        .toggleClass('fa-thumb-tack', !pin)
                        .toggleClass('fa-check-circle', pin)
                        .parent()
                        .toggleClass('text-muted', !pin)
                        .toggleClass('text-success', pin);

                    // Show success message
                    toastr.success(response.message);

                    // If you want to refresh the grid instead of updating just the button:
                    // $.admin.reload();
                } else {
                    toastr.error(response.message || 'Operation failed');
                }
            },
            error: function() {
                toastr.error('Request failed');
            }
        });
    }
});
</script>
HTML);
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
