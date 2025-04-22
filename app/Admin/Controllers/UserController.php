<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use App\Models\Pack;
use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\Country;
use App\Models\UserVip;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;
use App\Facades\UserHandling;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
use App\Admin\Widgets\InfoBox;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
use App\Admin\Widgets\Table as TableWidget;
use Illuminate\Validation\Rule;
use App\Admin\Forms\ProfileForm;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Admin\Selectable\ImageColors;
use App\Admin\Actions\DeletePackAction;
use Illuminate\Support\Facades\Session;
use App\Admin\Actions\ChangeAgencyAction;
use App\Admin\Actions\KickOfAgencyAction;
use App\Admin\Actions\KickOfFamilyAction;
use App\Admin\Actions\DeleteUserVipAction;
use App\Admin\Actions\EditPackExpireAction;
use Modules\SwitchAccount\Entities\UserAccount;
use Modules\Achievement\Http\Services\UserAchievementService;


class UserController extends MainController
{
    public $permission_name = 'users';
    public $hiddenColumns = [
        'is_host',
        'status',
        'is_gold_id',
        'id',
        'email',
        'phone',
        'di',
        'gold',
        'coins',
        'actions'
    ];
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'Users';
    }

    public function index0(Content $content)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('browse-users');
        }


        $forms = [
            'one' => ProfileForm::class,
            'tow' => ProfileForm::class,
        ];

        return $content
            ->title(__($this->title))
            ->body(Tab::forms($forms));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(__($this->title))
            ->body($this->form()->edit($id)));
    }

    public function close_open_gift(Request $request)
    {
        if ($request->make_rooms_top == "true") {
            settings()->set("close_open_gifts", "1");
        } else {
            settings()->set("close_open_gifts", "0");
        }
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(__($this->title))
            ->body($this->form()));
    }

    public function index(Content $content)
    {

        if (!Admin::user()->can('*')) {
            Permission::check('browse-users');
        }

        return $content
            ->title(__($this->title))
            ->row(function (Row $row) {
                $row->column(12, $this->grid2());
            })
            ->row(function ($row) {
                $row->column(12, $this->grid());
            });
    }

    protected function grid2()
    {
        $transfer_salary = settings()->get('transfer_salary');
        $stop_invite_code = settings()->get('stop_invite_code');
        $stop_charge = settings()->get('stop_charge');
        $make_rooms_top = settings()->get('make_rooms_top');


        return (new Box(
            title: __('admin.Actions'),
            content: view('admin.grid.users.userChargeViewNew', compact(['stop_charge', 'make_rooms_top', 'stop_invite_code', 'transfer_salary',])),
        ));
    }

    protected function grid()
    {
        $grid = new Grid(new User());
        $haveCoins = (request()->have_coins == 1);

        // Optimize eager loading
        $grid->model()->with([
            'ownerRoom',
            'profile',
            'userSetting',
            'agency',
            'sameDeviceUsers',
            'reals' => function ($q) {
                $q->select('id', 'user_id');
            },
            'moments' => function ($q) {
                $q->select('id', 'user_id');
            },
            'liveTime' => function ($q) {
                $q->select('id', 'uid', 'hours');
            },
            'targets' => function ($q) {
                $q->select(
                    'id',
                    'user_id',
                    'add_month',
                    'add_year',
                    'target_usd',
                    'target_agency_share',
                    'user_diamonds',
                    'user_hours',
                    'user_days',
                    'user_obtain',
                    'updated_at'
                )
                    ->orderBy('created_at', 'desc');
            }
        ]);

        if (request()->online == 1) {
            $grid->model()->where('online_time', '>=', now()->startOfDay()->timestamp)
                ->where('online_time', '<=', now()->timestamp);
        } else if ($haveCoins) {
            $grid->model()->where('di', '>', 0)->orderByDesc('di');
        } else {
            $grid->model()->orderByDesc('id');
        }
        $grid->quickSearch();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();

            $filter->column(1 / 2, function ($filter) {
                $filter->equal('family_id', __('Family'))->select(Common::by_family_filter());

                $filter->column(1 / 2, function ($filter) {
                    $filter->where(function ($query) {
                        $input = $this->input;
                        $query->where(function ($q) use ($input) {
                            $q->where('name', 'like', "%$input%")
                                ->orWhere('uuid', 'like', "%$input%")
                                ->orWhere('special_id', 'like', "%$input%")
                                ->orWhere('nickname', 'like', "%$input%")
                                ->orWhere('email', 'like', "%$input%");
                        });
                    }, __('User'))->placeholder(__('Search by name , UUID , nickname and email'));

                    $filter->equal('UserVip.vip_id', __('vip'))->select(Common::by_ovip_filter());
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
        $grid->column('name', __('Name'));

            $grid->column('return', __('status user'))->display(function () {
                $userSetting = $this->userSetting ?? (object) ['show_invite_code' => 0, 'hide_chat' => 0];
                return (new \App\Admin\Actions\UserAction(
                    $this->id,
                    $this->charge_status,
                    $this->transfer_salary,
                    $userSetting->show_invite_code ?? 0,  // Extra fallback
                    $userSetting->hide_chat ?? 0,        // Extra fallback
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

        $grid->column('total_charge_level', __('admin.charge_level'));

        $grid->column('profile.avatar', __('image'))->display(function ($path) {
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $grid->column('phone', __('Phone'));

        $grid->column('agency_id', __('agency id'))->modal(__('agency'), function () {
            $agency =  $this->agency;
            $path = @$agency?->img;
            $defaultImage = asset("images/icon-agency.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $results = [
                __('name') => @$agency->owner->name ?? '',
                __('img') => "<img src='" . $url . "' style='width:100px;height:100px' class='img img-thumbnail'$ />",

            ];

            return new Table([__('Field Name'), __('Value')], $results);
        });

        $grid->column('target', __('target'))->expand(function ($model) {

            $targets = $model->targets()->orderByDesc('created_at')->get()->map(function ($target) {
                $target =
                    [
                        'id' => $target->id,
                        'add_month' => $target->add_month . '/' . $target->add_year,
                        'target_usd' => $target->target_usd,
                        'target_agency_share' => $target->target_agency_share,
                        'user_diamonds' => $target->user_diamonds,
                        'user_hours' => $target->user_hours,
                        'user_days' => $target->user_days,
                        'user_obtain' => $target->user_obtain,
                        'updated_at' => $target->updated_at,
                    ];



                return $target;
            });

            return new TableWidget(
                [
                    'ID',
                    __('month') . '/' . __('year'),
                    __('usd') . ' ' . __('deserved'),
                    __('agency share') . '(%)',
                    __('user diamonds'),
                    __('user hours'),
                    __('user days'),
                    __('user obtain'),
                    __('at time'),
                    __('updated at'),

                ],
                $targets->toArray()
            );
        });
        Admin::style('tr{background-color:var(--table-background-color);}.btn-circle {width: 30px; height: 30px; font-size:15px; border-radius: 50%; text-align: center; }');
        $grid->column('custom_button2', __('عدد الحسابات'))->display(function () {
            return $this->sameDeviceUsers()->count();
        })->modal('حسابات اخري علي نفس الجهاز', function ($model) {
            $users         = $this->sameDeviceUsers;

            $rows = $users->map(function ($user) {
                $path = $user->profile?->avatar;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;

                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }

                $image = handleShowImageWithTypes($user->id, $url, 40, 40);

                $nameColumn = "
                    <div style='display: flex; align-items: center; gap: 10px;'>
                        $image
                        <div>
                             <a  style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                                <span cursor: pointer;'>$user->name</span>
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
            $achivement = new UserAchievementService();
            $data_achivement = $achivement->getUserAchievement($model);

            $filtered = $data_achivement->map(function ($user) {
                $img = $user["valid_image"] ?? $user["custom_image"];
                $img = getDriverUrl() . '/' . $img;
                $img = "<img src='" . $img . "' style='width:50px;height:50px' class='img img-thumbnail'$ />";
                return [
                    'id' => $user['id'],
                    'target' => $user['target'],
                    'image' => $img,
                ];
            });
            return (new Table([__('Id'), __('target'), __('image')], $filtered->toArray()));
        });

        // $grid->column('custom_button3', __('تبديل الحساب'))->modal('حسابات اخري علي نفس الجهاز', function ($model) {
        //     $device_token = $this->device_token;
        //     $users = UserAccount::where('device_token', $device_token)
        //         ->select(['parent_user_id', 'child_user_id'])
        //         ->get();

        //     $parentUserIds = $users->pluck('parent_user_id');
        //     $childUserIds = $users->pluck('child_user_id');
        //     $allIds = $parentUserIds->merge($childUserIds)->unique()->values()->all();

        //     $userId = $this->id;
        //     $filteredIds = array_filter($allIds, function ($id) use ($userId) {
        //         return $id != $userId;
        //     });

        //     $filteredIds = array_values($filteredIds);
        //     $users = User::query()
        //         ->whereIn('id', $filteredIds)
        //         ->select(['name', 'uuid', 'phone'])
        //         ->get();

        //     $filteredUsers = $users->map(function ($user) {
        //         return $user->only(['name', 'uuid', 'phone']);
        //     });

        //     return new Table([__('Name'), __('uuid'), __('phone')], $filteredUsers->toArray());
        // });

        $grid->column('custom_button3', __('تبديل الحساب'))->modal(__('Other accounts on the same device'), function () {
            $deviceToken = $this->device_token;

            // First: get all related user IDs, excluding current user in SQL
            $relatedUserIds = UserAccount::query()
                ->where('device_token', $deviceToken)
                ->where(function ($query) {
                    $query->where('parent_user_id', '!=', $this->id)
                        ->orWhere('child_user_id', '!=', $this->id);
                })
                ->get()
                ->flatMap(function ($ua) {
                    return [$ua->parent_user_id, $ua->child_user_id];
                })
                ->filter(fn($id) => $id != $this->id)
                ->unique()
                ->values();

            // Now fetch users in one query
            $users = \App\Models\User::query()
                ->whereIn('id', $relatedUserIds)
                ->select(['name', 'uuid', 'phone'])
                ->get();

            // Format users for the table
            $rows = $users->map(function ($user) {
                return [
                    'name' => $user->name,
                    'uuid' => $user->uuid,
                    'phone' => $user->phone,
                ];
            });

            return new \Encore\Admin\Widgets\Table(
                [__('Name'), __('uuid'), __('phone')],
                $rows->toArray()
            );
        });

        $grid->actions(function ($actions) {
            $model = $actions->row;
            if ($model->agency_id >= 1) {
                $actions->add(new KickOfAgencyAction());
            }
            if ($model->family_id >= 1) {
                $actions->add(new KickOfFamilyAction());
            }
            if ($model->agency_id >= 1) {
                $actions->add(new ChangeAgencyAction($model->id));
            }
        });
        if (config('app.env') == 'production') $grid->disableCreateButton();
        $grid->disableExport();
        $this->extendGrid($grid);

        return $grid;
    }

    public function stop_charge(Request $request)
    {
        if ($request->stop_charge == "false") {
            settings()->set("stop_charge", "0");
        } else {
            settings()->set("stop_charge", "1");
        }
    }

    public function make_rooms_top(Request $request)
    {
        if ($request->make_rooms_top == "true") {
            settings()->set("make_rooms_top", "1");
        } else {
            settings()->set("make_rooms_top", "0");
        }
    }

    public function transferSalary(Request $request)
    {
        if ($request->transfer_salary == "true") {
            settings()->set("transfer_salary", "1");
        } else {
            settings()->set("transfer_salary", "0");
        }
    }

    public function show($id, Content $content)
    {
        return $content->row(
            function ($row) use ($id) {
                $user = User::withTrashed()->find($id);
                if ($user) {
                    $user->flowers = 0;
                    $user->save();
                }

                $type = $user->type_user;
                switch ($type) {
                    case 0:
                        $userType = __("User");
                        break;
                    case 1:
                        $userType = __("Host");
                        break;
                    case 2:
                        $userType = __("Host Agent");
                        break;
                    case 3:
                        $userType = __("Shipping Agent");
                        break;
                    case 4:
                        $userType = __("Resort & Shipping Agent");
                        break;
                    case 5:
                        $userType = __("Admin");
                        break;
                    default:
                        $userType = $type; // Keep the original value if no match is found
                        break;
                }
                $row->column(2, new InfoBox($user->salary, 'dollar', 'green', '?type=balance_details', __('Balance')));
                $row->column(2, new InfoBox(Common::level_center($user)['sender_level'], 'dollar', 'orange', '?type=balance_details', __('Level')));
                $row->column(2, new InfoBox(Common::level_center($user)['receiver_level'], 'dollar', 'blue', '?type=balance_details', __('worth')));
                $row->column(2, new InfoBox($user->getTotalDiamond(), 'dollar', 'red', '?type=balance_details', __('diamonds')));
                $row->column(2, new InfoBox($user->di, 'dollar', 'yellow', '?type=balance_details', __('coins')));
                $row->column(2, new InfoBox($userType ?? '', '', 'green', '?type=balance_details', __('type')));
            }
        )->row("<h3>" . __('pack') . "</h3>")->row(function ($row) use ($id) {
            $row->column(12, $this->packList($id));
        })
            ->row("<h3>" . __('vips') . "</h3>")->row(function ($row) use ($id) {
                $row->column(12, $this->vipList($id));
            });
    }

    protected function packList($id)
    {
        Pack::query()
            ->where('expire', '!=', 0)
            ->where('expire', '<', time())
            ->delete();

        $grid = new Grid(new Pack);
        $grid->model()
            ->where('user_id', $id)
            ->with(['ware' => function ($q) {
                $q->select('id', 'show_img');
            }]);

        $grid->id('ID');
        $grid->column('user_id', __('user id'));
        $grid->column('get_type', __('get type'))->using([
            1 => __('vip level automatic acquisition'),
            2 => __('activities'),
            3 => __('treasure box'),
            4 => __('purchase'),
            5 => __('background addition'),
        ]);

        $grid->column('type', __('type'))->using([
            1 => trans('Gemstone'),
            3 => trans('Card Scroll'),
            4 => trans('Avatar Frame'),
            5 => trans('Bubble Frame'),
            6 => trans('Entering Special Effects'),
            7 => trans('Microphone Aperture'),
            8 => trans('Badge'),
            9 => trans('NoKick'),
            10 => trans('Icon'),
            11 => trans('intro animation'),
            12 => trans('wapel'),
            13 => trans('hide country'),
            14 => trans('vip gifts'),
            15 => trans('no pan'),
            16 => trans('hidden room'),
            17 => trans('anonymous man'),
            18 => trans('colored name'),
            19 => trans('profile visitors hide in'),
            20 => trans('hide last active'),
            21 => trans('sound effect'),
            22 => trans('upload GIF image'),
        ]);

        $grid->column('target_id', __('img'))->display(function () {
            return $this->ware ? "<img width='30' src='" . getDriverUrl() . '/' . $this->ware->show_img . "'>" : '';
        });

        $grid->column('expire', __('expire'))->display(function ($row) {
            return $this->expire ? Carbon::createFromTimestamp($this->expire)->format('Y-m-d H:i:s') : __('no time');
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $actions->add(new DeletePackAction());
            $actions->add(new EditPackExpireAction());
        });

        $grid->disablePagination();
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableExport();

        return $grid;
    }

    protected function vipList($id)
    {
        $grid = new Grid(new UserVip());
        $grid->model()
            ->where('user_id', $id)
            ->select(['id', 'user_id', 'level', 'expire', 'qty', 'total']);

        $grid->id('ID');
        $grid->column('user_id', __('user id'));
        $grid->column('level', __('level'));
        $grid->column('expire', __('expire'))->display(function ($row) {
            return $this->expire ? Carbon::createFromTimestamp($this->expire)->format('Y-m-d H:i:s') : __('no time');
        });
        $grid->column('qty', __('qty'));
        $grid->column('total', __('total Price'));

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $actions->add(new DeleteUserVipAction());
        });

        $grid->disablePagination();
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableExport();

        return $grid;
    }

    public function showAdditionalInfo($id, Content $content)
    {
        return $content
            ->row(function (Row $row) {
                $row->column(12, $this->showColSearch());
            })
            ->row(
                function ($row) use ($id) {
                    $user = User::find($id);
                    if ($user) {
                        $user->flowers = 0;
                        $user->save();
                    }
                    $type = $user->type_user;
                    switch ($type) {
                        case 0:
                            $userType = __("User");
                            break;
                        case 1:
                            $userType = __("Host");
                            break;
                        case 2:
                            $userType = __("Host Agent");
                            break;
                        case 3:
                            $userType = __("Shipping Agent");
                            break;
                        case 4:
                            $userType = __("Resort & Shipping Agent");
                            break;
                        case 5:
                            $userType = __("Admin");
                            break;
                        default:
                            $userType = $type; // Keep the original value if no match is found
                            break;
                    }

                    $row->column(2, new InfoBox(__('Balance'), 'dollar', 'green', '?type=balance_details', $user->salary));
                    $row->column(2, new InfoBox(__('Level'), 'dollar', 'orange', '?type=balance_details', Common::level_center($user)['sender_level']));
                    $row->column(2, new InfoBox(__('worth'), 'dollar', 'blue', '?type=balance_details', Common::level_center($user)['receiver_level']));
                    $row->column(2, new InfoBox(__('diamonds'), 'dollar', 'red', '?type=balance_details', $user->getTotalDiamonds()));
                    $row->column(2, new InfoBox(__('coins'), 'dollar', 'red', '?type=balance_details', $user->di));
                    $row->column(2, new InfoBox(__('type'), 'dollar', 'red', '?type=balance_details', $userType));
                }
            );
    }

    protected function showColSearch()
    {
        $form = new Box();
        $form->view('admin.grid.users.userChargeView');

        return $form;
    }

    public function update($id)
    {

        unset(request()['level']);
        unset(request()['worth']);


        return $this->form()->update($id);
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());
        if ($form->isEditing()) {
            $userId           = request()->route('user');
            $user             = User::findOrFail($userId);
            $oldDiValue       = $user->getOriginal('di');
            $oldDiamoundValue = $user->getOriginal('user_diamond');
        } else {
            $oldDiValue       = null;
            $oldDiamoundValue = null;
        }


        $loggedInUserId = Admin::user()->id;
        $form->display('id', __('id'));
        if (!$form->isEditing()) {
            // Add a hidden field for 'uuid' in the edit form
            $form->text('uuid', __('uuid'))->creationRules([
                'required',
                Rule::unique('users', 'uuid'),
                function ($attribute, $value, $fail) {
                    if (DB::table('wares')->where('value', $value)->exists()) {
                        return $fail(__('لا يمكنك استخدام معرف المميز هذا'));
                    }
                }
            ])
                ->updateRules([
                    'required',
                    Rule::unique('users', 'uuid')->ignore(request()->route('id')),
                    // نفس الشيء هنا مع التحقق من عدم وجود القيمة في جدول wares
                    function ($attribute, $value, $fail) {
                        if (DB::table('wares')->where('value', $value)->exists()) {
                            return $fail(__('القيمة موجودة بالفعل في جدول wares.'));
                        }
                    }
                ]);
        }

        $form->belongsTo('image_color_id', ImageColors::class, __('Color'));


        $form->text('name', __('Name'));
        if ($form->isEditing()) {
            $form->hidden('oldDiValue')->default($oldDiValue);
            $form->hidden('oldDiamoundValue')->default($oldDiamoundValue);
        }
        $form->text('uuid', __('uuid'))->updateRules(['required', "unique:users,uuid,{{id}}"]);

        // $form->switch('is_gold_id', trans('	is_gold_id'))->states (Common::getSwitchStates());
        $form->image('profile.avatar', __('image'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        });

        $form->image('profile.image_id', __('image Id'));
        $state = [
            'on' => ['value' => 1, 'text' => 'open', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => 'close', 'color' => 'default'],
        ];

        $form->switch('charge_status', __("charge status"))->states($state);
        $form->switch('transfer_salary', __("transfer_salary"))->states($state);
        $form->switch('userSetting.show_invite_code', __("show invite code"))->states($state);
        $form->switch('userSetting.hide_chat', __("hide_chat"))->states($state);
        $form->select('country_id', trans('country'))->options(function () {
            $ops       = [null => __('no country')];
            $countries = Country::all();
            foreach ($countries as $country) {
                $ops[$country->id] = App::isLocale('en') ? $country->e_name : $country->name;
            }
            return $ops;
        });
        $states = [
            'default'  => ['value' => 0, 'text' => 'yes', 'color' => 'success'],
            'on'  => ['value' => 2, 'text' => 'yes', 'color' => 'success'],
            'off' => ['value' => 3, 'text' => 'no', 'color' => 'danger'],
        ];
        if ($form->isCreating()) {
            $form->switch('can_play', __('canPlay'))->default(0)->states($states);
        } elseif ($form->isEditing()) {
            $form->switch('can_play', __('canPlay'))->value(function ($can_play) {
                $can_play = UserHandling::chickLevelToPlay($this);
                return $can_play ? 'on' : 'off';
            })->states($states);
        }

        if ($loggedInUserId == 1 || $loggedInUserId == 2) {
            if ($form->isEditing()) {
                $form->number('di', __('Coins'))->default(0)
                ->disable($form->isEditing());
            } else {
                $form->number('di', __('Coins'))->default(0);
            }
            $form->number('user_diamond', __('Diamonds'))->default(0);
            $form->number('total_sender_level', __('Sender Level'))->default(0);
            $form->number('total_received_level', __('Received Level'))->default(0);
            $form->number('total_charge_level', __('admin.charge_level'))->default(0);
            $form->number('salary', __('salary'))->disable();
        }
        $form->select('profile.gender', __('gender'))->options([0 => __('female'), 1 => __('male')]);
        $form->email('email', __('Email'))->attribute('onfocus', "this.removeAttribute('readonly');")->attribute('readonly');
        $form->password('password', __('Password'))->attribute('onfocus', "this.removeAttribute('readonly');")->attribute('readonly')->creationRules('required');
        $form->text('phone', __('phone'))->creationRules(['required', "unique:users,phone,{{id}}"])->updateRules(['required', "unique:users,phone,{{id}}"]);
        $form->switch('status', __('block status'))->options(Common::getSwitchStates2());
        $form->select('type_user', trans('User Type'))->options([
            $form->model()->type_user => $form->model()->type_user,
            0                         => 'مستخدم',
            1                         => 'مضيف',
            2                         => 'وكيل مضيفين',
            3                         => 'وكيل شحن',
            4                         => ' وكيل مصيفين ووكيل شحن',
            5                         => 'اداري',

        ])->default(0);

        if (Session::has('show_alert')) {
            $form->html('<script>
            $(document).ready(function () {
                alert(" يملك هذا المستخدم وكالة   . الرجاء مسح الوكالة واخراج المضيفين اولا قبل تغيير نوع المستخدم");
            });
        </script>');
        }

        $form->saving(function (Form $form) use ($oldDiValue, $oldDiamoundValue) {
            $type_user = request()->type_user;
            $model     = $form->model();
            $user_id   = $model->id;
            if ($form->oldDiValue != $oldDiValue) {
                $form->di = $oldDiValue;
            }

            if ($form->oldDiamoundValue != $oldDiamoundValue) {
                $form->user_diamond = $oldDiamoundValue;
            }

            $agancy = Agency::where('app_owner_id', $user_id)->first();
            if ($agancy) {


                if (in_array(intval($type_user), [0, 1, 5]) && $model->isDirty('type_user')) {
                    session()->flash('show_alert', 'Your alert message');
                    return redirect()->back();
                }


                switch ($type_user) {


                    case 2:
                        User::where('id', $user_id)->update(['type_user' => 2]);
                        break;
                    case 3:
                        User::where('id', $user_id)->update(['type_user' => 3]);
                        break;
                    case 4:
                        User::where('id', $user_id)->update(['type_user' => 4]);
                        break;

                    default:

                        // dd();

                        break;
                }
            }
        });


        return $form;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */

    public function request_invite_code(Request $request)
    {
        if ($request->stop_invite_code == "true") {
            settings()->set("stop_invite_code", "1");
        } else {
            settings()->set("stop_invite_code", "0");
        }
    }
}
