<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Facades\CustomNotification;
use App\Models\RequestBackgroundImage;

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
        return parent::index($content
            ->title(trans('request-background-image'))
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
        $grid->id(__('ID'));
        $grid->owner_room_id(__('owner room id'))->display(function () {
            $name = @$this->owner->name ?? '';
            $uuid = @$this->owner->uuid ?? '';
            $path = @$this->owner?->ownerRoom->room_cover;
            $defaultImage = asset("images/room.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = url("admin/rooms/{$this->owner?->ownerRoom?->id}");

            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                        <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                        <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='color: #aaa; font-size: smaller;'>UID: $uuid</span>
                    </div>

                </div>
            ";
        });

        $grid->column('owner.name', __('owner'))
            ->display(function ($name) {
                $uid = @$this->owner->uuid;
                $path = @$this->owner?->profile?->avatar;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;

                // Check if the image exists
                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }

                $image = handleShowImageWithTypes($this->id, $url, 40, 40);
                $showUrl = url("admin/users/{$this->owner->id}");
                return "
                    <div style='display: flex; align-items: center; gap: 10px;'>
                        $image
                        <div>
                           <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                             <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                            </a>
                            <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                        </div>
                    </div>
                ";
            });

        $grid->img(__('image'))->display(function ($img) {
            $defaultImage = asset("images/background_room.jpg");
            $path = getImagePath($img);
            if (!isImageExists($path)) {
                $path = $defaultImage;
            }
            $parsedUrl = parse_url($path);
            $correctUrl = isset($parsedUrl['host']) ? $path : url("/$path");

            return "
                    <img src='$correctUrl' style='width: 50px; height: 50px; border-radius: 5px; cursor: pointer;' onclick='openModal(\"$correctUrl\")' />

                    <div id='imageModal' class='modal' style='display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.7); text-align:center;'>
                        <span onclick='closeModal()' style='position:absolute; top:10px; right:20px; font-size:30px; color:white; cursor:pointer;'>&times;</span>
                        <img id='modalImage' style='display:block; margin:auto; max-width:90%; max-height:90%; margin-top:50px; border-radius:5px;' />
                    </div>

                    <script>
                        function openModal(src) {
                            let modal = document.getElementById('imageModal');
                            let modalImage = document.getElementById('modalImage');
                            modal.style.display = 'block';
                            modalImage.src = src;
                        }

                        function closeModal() {
                            document.getElementById('imageModal').style.display = 'none';
                        }

                        // Close modal when clicking outside the image
                        document.getElementById('imageModal').addEventListener('click', function(event) {
                            if (event.target === this) {
                                closeModal();
                            }
                        });
                    </script>
                ";
        });

        $grid->column('status', __('status'))->display(function ($status) {
            $statuses = [
                0 => ['label' => __('pending'), 'color' => 'orange'],
                1 => ['label' => __('accepted'), 'color' => 'green'],
                2 => ['label' => __('denied'), 'color' => 'red'],
            ];

            $badgeColor = $statuses[$status]['color'] ?? 'orange';
            $statusLabel = $statuses[$status]['label'] ?? __('pending');

            return "<span style='display: inline-block; padding: 5px 10px; color: white; background-color: $badgeColor; border-radius: 5px;'>
                        $statusLabel
                    </span>";
        });

        $grid->column('expair', __('expire'));
        $grid->column('updated_at', __('admin.updated_at'))->display(function ($date) {
            return Carbon::parse($date)->format('Y-m-d H:i:s');
        });

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
