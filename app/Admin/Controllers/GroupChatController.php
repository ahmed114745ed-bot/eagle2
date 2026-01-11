<?php

namespace App\Admin\Controllers;

use App\Jobs\SendNotificationsToAllUsers;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\GroupChat;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\HasResourceActions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Public\Http\Services\UpgradeLevelServices;

class GroupChatController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'group-chat';
    public $permission_setting = "chat-setting";
    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return parent::index($content
            ->title(__("group Chat"))
            /* ->row(function (Row $row) {
                $row->column(12, $this->grid2());
            }) */
            ->row(function ($row) {
                $row->column(12, $this->grid());
            }));
    }

    public function chat_settings(Content $content)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('browse-' . $this->permission_setting);
        }
        return $content
            ->view('chat_settings');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid2()
    {
        $form = new Box();
        $form->view('admin.grid.common.group-chat');

        return $form;
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
            ->title(__("group Chat"))
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
            ->title(__("group Chat"))
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
            ->title(__("group Chat"))
            ->body($this->form()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GroupChat());
        $countryID =session('filter_country_id');
        $grid->model()->when($countryID, function ($query) use ($countryID) {
            $query->where(function ($q) use ($countryID) {
                $q->whereHas('user', function ($subQuery) use ($countryID) {
                    $subQuery->where('country_id', $countryID);
                });
            });
        })->orderByDesc('id');
        $grid->quickSearch();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column('1/2', function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('user', function ($query) use ($input) {
                        $query->where('name', 'like', "%$input%")
                            ->orWhere('uuid', 'like', "%$input%");
                    });
                }, __('User'))->placeholder(__('Search by name or UUID '));
            });
        });

        $grid->tools(function ($tools) {
            $tools->append('<a href="'.route('admin.chat.view').'" class="btn btn-sm btn-success" style="margin-left: 10px;">
                <i class="fa fa-comments"></i> '.__('View Chat Interface').'
            </a>');
        });

        $grid->column('id', __('ID'))->sortable();
        $grid->column('text', __('Message'))->limit(50);
        $grid->column('user_id', __('User ID'));
        $grid->column('image', __('Image'))->image('', 50, 50);
        $grid->column('parent_id', __('Parent ID'));
        $grid->column('created_at', __('Created at'))->sortable();
        $grid->column('updated_at', __('Updated at'))->sortable();

        $grid->filter(function($filter){
            $filter->like('text', 'Message');
            $filter->equal('user_id', 'User ID');
            $filter->between('created_at', 'Created Date')->datetime();
        });

        return $grid;
//        $grid = new Grid(new GroupChat);
//        $countryID =session('filter_country_id');
//        $grid->model()->when($countryID, function ($query) use ($countryID) {
//            $query->where(function ($q) use ($countryID) {
//                $q->whereHas('user', function ($subQuery) use ($countryID) {
//                    $subQuery->where('country_id', $countryID);
//                });
//            });
//        })->orderByDesc('id');
//        $grid->quickSearch();
//        $grid->filter(function (Grid\Filter $filter) {
//            $filter->expand();
//            $filter->disableIdFilter();
//            $filter->column('1/2', function ($filter) {
//                $filter->where(function ($query) {
//                    $input = $this->input;
//                    $query->whereHas('user', function ($query) use ($input) {
//                        $query->where('name', 'like', "%$input%")
//                            ->orWhere('uuid', 'like', "%$input%");
//                    });
//                }, __('User'))->placeholder(__('Search by name or UUID '));
//            });
//        });
//        $grid->id(__('ID'));
//        $grid->column('user.name', __('name'))->display(function ($recever) {
//            $name =  $this->user?->name ?? '';
//            $uid = @$this->user?->uuid ?? 0;
//            $path = @$this->user?->profile?->avatar;
//            $defaultImage = asset("images/businessman-icon.jpg");
//            $url = getImagePath($path) ?? $defaultImage;
//
//            // Check if the image exists
//            if (!isImageExists($url)) {
//                $url = $defaultImage;
//            }
//            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
//
//            return "
//             <div style='display: flex; align-items: center; gap: 10px;'>
//                 $image
//                 <div>
//                     <strong>$name</strong><br>
//                     <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
//                 </div>
//             </div>
//         ";
//        });
//        $grid->text(__('text'));
//
//        $grid->column('created_at', __('Created at'))->sortable()->diffForHumans();
//        $grid->disableExport();
//        $this->extendGrid($grid);
//        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(GroupChat::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('text', __('Message'));
        $show->field('user_id', __('User ID'));
        $show->field('image', __('Image'))->image();
        $show->field('parent_id', __('Parent ID'));
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
        $form = new Form(new GroupChat);
        $this->disableFormTools($form);

        $form->textarea('text', __('Message'))->required();
        $form->number('user_id', __('User ID'))->required();
        $form->image('image', __('Image'));
        $form->number('parent_id', __('Parent ID'));

        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));

        return $form;
    }

    private function canAdminSendMessages(): bool
    {
        $admin = Admin::user();

        if (!$admin || empty($admin->app_id) || $admin->app_id <= 0) {
            return false;
        }

        return $admin->user()->exists();
    }

    private function getAdminAppId(): ?int
    {
        $admin = Admin::user();

        if (!$admin || empty($admin->app_id) || $admin->app_id <= 0) {
            return null;
        }

        if (!$admin->user()->exists()) {
            return null;
        }

        return (int) $admin->app_id;
    }

    public function chatView(Content $content)
    {
        return $content
            ->title(__('Group Chat'))
            ->description(__('Manage Messages'))
            ->body(view('admin.chat.interface', [
                'canSendMessages' => $this->canAdminSendMessages(),
                'adminAppId' => $this->getAdminAppId(),
            ]));
    }

    public function updateMessage(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer',
            'text' => 'required|string|max:1000'
        ]);

        $message = GroupChat::findOrFail($request->id);
        $message->update([
            'text' => $request->text,
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Message updated successfully!')
        ]);
    }

    public function deleteMessage($id): JsonResponse
    {
        $message = GroupChat::findOrFail($id);
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => __('Message deleted successfully!')
        ]);
    }

    public function getMessages(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $perPage = 10;

        $messages = GroupChat::with(['user.profile', 'parent.user']) // Add parent relationship
        ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Transform messages to include avatar URL and parent data
        $transformedMessages = $messages->getCollection()->map(function ($message) {
            $avatarPath = $message->user->profile->avatar ?? null;
            $defaultAvatar = asset('images/default-avatar.png');
            $avatarUrl = $avatarPath ? (getImagePath($avatarPath) ?? $defaultAvatar) : $defaultAvatar;

            // Parent message data
            $parentData = null;
            if ($message->parent) {
                $parentData = [
                    'id' => $message->parent->id,
                    'text' => $message->parent->text,
                    'user_name' => $message->parent->user->name ?? __('Unknown User'),
                ];
            }

            return [
                'id' => $message->id,
                'text' => $message->text,
                'user_id' => $message->user_id,
                'image' => $message->image,
                'parent_id' => $message->parent_id,
                'parent' => $parentData,
                'created_at' => $message->created_at,
                'updated_at' => $message->updated_at,
                'user_name' => $message->user->name ?? __('Unknown User'),
                'user_avatar' => $avatarUrl,
                'user_uuid' => $message->user->uuid ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'messages' => array_reverse($transformedMessages->toArray()),
            'current_page' => $messages->currentPage(),
            'last_page' => $messages->lastPage(),
            'total' => $messages->total(),
            'has_more' => $messages->hasMorePages()
        ]);
    }

    public function storeMessage(Request $request): JsonResponse
    {
        if (!$this->canAdminSendMessages()) {
            return response()->json([
                'success' => false,
                'error' => __('You cannot send messages. Your admin account is not connected to an app user account.')
            ], 403);
        }

        $request->validate([
            'text' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:group_chat,id'
        ]);

        $appUserId = $this->getAdminAppId();

        $user = User::find($appUserId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => __('User account not found.')
            ], 404);
        }

        $message = GroupChat::create([
            'text' => $request->text,
            'user_id' => $appUserId,
            'parent_id' => $request->parent_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $message->load(['user.profile', 'parent.user']);

        $avatarPath = $message->user->profile->avatar ?? null;
        $defaultAvatar = asset('images/default-avatar.png');
        $avatarUrl = $avatarPath ? (getImagePath($avatarPath) ?? $defaultAvatar) : $defaultAvatar;

        // Parent message data
        $parentData = null;
        if ($message->parent) {
            $parentData = [
                'id' => $message->parent->id,
                'text' => $message->parent->text,
                'user_name' => $message->parent->user->name ?? __('Unknown User'),
            ];
        }

        $responseData = [
            'id' => $message->id,
            'text' => $message->text,
            'user_id' => $message->user_id,
            'image' => $message->image,
            'parent_id' => $message->parent_id,
            'parent' => $parentData,
            'created_at' => $message->created_at,
            'updated_at' => $message->updated_at,
            'user_name' => $message->user->name ?? __('Admin'),
            'user_avatar' => $avatarUrl,
            'user_uuid' => $message->user->uuid ?? null,
        ];

        (new UpgradeLevelServices())->sendWorldChat($user);

        try {
            event(new \Modules\Chat\Events\GroupChat($responseData));
        } catch (\Throwable $th) {
            // Log error if needed
            // \Log::error('Pusher error: ' . $th->getMessage());
        }

        dispatchJobToQueue(
            new SendNotificationsToAllUsers($user, $request->text, $responseData),
            queueName: 'heavyProcessing'
        );

        return response()->json([
            'success' => true,
            'message' => $responseData
        ]);
    }
}
