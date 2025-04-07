<?php

namespace App\Admin\Controllers\AgencyControllers;


use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Layout\Content;
use App\Admin\Actions\ChangeAgencyAction;
use App\Admin\Controllers\MainController;
use Modules\SwitchAccount\Entities\UserAccount;
use Modules\Achievement\Http\Services\UserAchievementService;


class UserController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;
    public $permission_name = 'agent-user';



    public function __construct()
    {
        $this->title = 'Hosts';
    }


    public function index(Content $content)
    {
        return parent::index($content
            ->title(__($this->title))
            ->row(function ($row) {
                $row->column(12, $this->grid());
                //$row->column(2, view('admin.grid.users.actions'));
            }));
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
            ->title(__($this->title))
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
            ->title(__($this->title))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(__($this->title))
            ->body($this->form()));
    }






    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $haveCoins = (request()->have_coins == 1);
        $grid->model()->ofAgency()->with("ownerRoom")->where('is_host',1);
        $grid->quickSearch();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('agency_id', __('agency'))->select(Common::by_agency_filter());

                $filter->column(1/2, function ($filter) {
                    $filter->where(function ($query) {
                        $input = $this->input;
                        $query->where('name', 'like', "%$input%")
                            ->orWhere('uuid', 'like', "%$input%")->orWhere('special_id', 'like', "%$input%")->orWhere('nickname', 'like', "%$input%")->orWhere('email', 'like', "%$input%");
                    }, __('User'))->placeholder(__('Search by name , UUID , nickname and email'));
                });
            });
        });
        $grid->column('id', __('Id'));
        if ($haveCoins) {
            $grid->column('di', __('coins'))->display(function ($value) {
                return number_format($value);
            });
        }

        $grid->column('uuid', __('uuid'))->display(function () {
            return $this->uuid == $this->original_uuid
                ? __("uuid") . ' : ' . $this->uuid
                : __("uuid") . ' : ' . $this->uuid . '<br>' . __("special uuid") . ' : ' . $this->original_uuid;
        });
        $grid->column('name', __('Name'))
        ->display(function ($name) {
            $uid = @$this->uuid;
            $path = @$this->profile?->avatar;
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
        $grid->column('return', __('status user'))->display(function () {
            $userSetting = $this->userSetting ?? (object) ['show_invite_code' => 0, 'hide_chat' => 0];
            return (new \App\Admin\Actions\UserAction(
                $this->id,
                $this->charge_status,
                $this->transfer_salary,
                $userSetting->show_invite_code,
                $userSetting->hide_chat,
                $this->can_play
            ))->render();
        });


        $grid->column('reals.user_id', __('user Active'))->modal(__('user Active'), function ($model) {

            $results = [
                __('reel count') => $this->reals()->count() ?? 0,
                __('moment_count') => $this->moments()->count() ?? 0,
                __('total_days') => $this->total_days ?? 0,
                __('total_hours') => $this->liveTime->sum("hours") ?? 0,
            ];

            return new Table([__('Field Name'), __('Value')], $results);
        });



        $grid->column('profile.avatar', __('image'))->display(function ($path) {
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $grid->column('phone', __('Phone'));

        $grid->column('agency_id', __('agency id'))->modal('admin info', function () {
            $agency =  Agency::query()->find(@$this->agency_id);
            $path = @$agency?->img;
                $defaultImage = asset("images/icon-agency.jpg");
                $url = getImagePath($path) ?? $defaultImage;

                 // Check if the image exists
                 if (!isImageExists($url)) {
                     $url = $defaultImage;
                 }
            $showUrl = $agency ? url("admin/agencies/profile/{$agency->id}") : 0;
                 $agencyName = $agency->name ?? '';
            $results = [
             __('name') => "  <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$agencyName</span>
                        </a>",
             __('img') => "<img src='" . $url ."' style='width:100px;height:100px' class='img img-thumbnail'$ />" ,

         ];

         return new Table([__('Field Name'), __('Value')], $results);
         });

        $grid->column('target', __('target'))->expand(function ($model) {

            $targets = $model->targets()->orderBy('created_at', 'desc')->get()->map(function ($target) {
                $target = $target->only(
                    [
                        'id',
                        'add_month',
                        'add_year',
                        'target_usd',
                        'target_agency_share',
                        'user_diamonds',
                        'user_hours',
                        'user_days',
                        'user_obtain',
                        'updated_at'
                    ]
                );


                return $target;
            });

            return new \App\Admin\Widgets\Table(
                [
                    'ID',
                    __('month') .'/'.__('year') ,
                    __('usd') . ' ' . __('deserved'),
                    __('agency share') . '(%)',
                    __('user diamonds'),
                    __('user hours'),
                    __('user days'),
                    __('user obtain'),
                    __('at time'),

                ],
                $targets->toArray()
            );
        });
        Admin::style('.btn-circle {width: 30px; height: 30px; font-size:15px; border-radius: 50%; text-align: center; }');
        $grid->column('custom_button2', __('عدد الحسابات'))->display(function () {
            $id           = $this->id;
            $device_token = $this->device_token;
            $count        = User::where('device_token', $device_token)->where('device_token', '!=', null)->count();
            $class        = 1 == 0 ? 'btn-danger' : 'btn-success';
            return $count;
        })->modal('حسابات اخري علي نفس الجهاز', function ($model) {
            $device_token  = $this->device_token;
            $users         =
                User::select(['id', 'name', 'uuid', 'phone'])->where('device_token', $device_token)->where('device_token', '!=', null)->get();

            $rows = $users->map(function ($user) {
                $path = $user->profile?->avatar;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;

                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }

                $image = handleShowImageWithTypes($user->id, $url, 40, 40);
                $showUrl = $user ? url("admin/users/{$user->id}") : 0;

                $nameColumn = "
                    <div style='display: flex; align-items: center; gap: 10px;'>
                        $image
                        <div>
                            <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                                <span style='text-decoration: underline; cursor: pointer;'>$user->name</span>
                            </a>
                            <span style='color: #aaa; font-size: smaller;'>UUID: $user->uuid</span>
                        </div>
                    </div>
                ";

                return [
                    'name' => $nameColumn,
                    'phone' => $user->phone,
                ];
            });

            return new Table([__('Name'), __('phone')], $rows->toArray());
        });

        $grid->column('achievements', __('achievements'))->modal(__('achievements'), function ($model) {
            $achivement      = new UserAchievementService();
            $data_achivement = $achivement->getUserAchievement($model);

            $filtered = $data_achivement->map(function ($user) {

                $img = $user["valid_image"] ?? $user["custom_image"];
                $img = getDriverUrl() . '/' . $img;
                $img = "<img src='" . $img . "' style='width:50px;height:50px' class='img img-thumbnail'$ />";
                //                $user->only(["user_achievement_levels.id","achievement_levels.valid_image"]);
                return [
                    'id'     => $user['id'],
                    'target' => $user['target'],
                    'image'  => $img,
                ];
            });
            return (new Table([__('Id'), __('target'), __('image')], $filtered->toArray()));
        });

        $grid->column('custom_button3', __('تبديل الحساب'))->modal('حسابات اخري علي نفس الجهاز', function ($model) {
            $device_token  = $this->device_token;
            $users = UserAccount::where('device_token', $device_token)->get();
            $parentUserIds = $users->pluck('parent_user_id');
            $childUserIds = $users->pluck('child_user_id');

            $allIds = $parentUserIds->merge($childUserIds)->unique()->values()->all();
            $userId = $this->id;
            $filteredIds = array_filter($allIds, function ($id) use ($userId) {
                return $id != $userId;
            });
            $filteredIds = array_values($filteredIds);
            $users = User::query()->whereIn('id', $filteredIds)->select("name", 'uuid', 'phone')->get();
            $filteredUsers = $users->map(function ($user) {
                return $user->only(["name", "uuid", "phone"]);
            });
            return new Table([__('Name'), __('uuid'), __('phone')], $filteredUsers->toArray());
        });


        $grid->actions(function ($actions) {
            $model = $actions->row;

            if ($model->agency_id >= 1) {
                $actions->add(new ChangeAgencyAction($model->id));
            }
        });


       // $grid->disableActions();
        $grid->disableCreateButton();

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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uuid', __('uuid'));
        $show->field('avatar', __('avatar'))->image('', 200);
        $show->field('name', __('Name'));
        $show->field('nickname', __('NickName'));
        $show->field('flag', __('country'))->image('', 50);
        $show->field('email', __('Email'));





        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
}
