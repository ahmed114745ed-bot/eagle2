<?php

namespace App\Admin\Controllers;

use App\Admin\Services\AgencyService;
use App\Admin\Services\UserService;
use App\Models\UserCoinLog;
use Carbon\Carbon;
use App\Models\Pack;
use App\Models\User;
use App\Models\Agency;
use App\Models\Charge;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\Country;
use App\Models\GiftLog;
use App\Models\UserVip;
use App\Models\UserSallary;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
use App\Admin\Widgets\InfoBox;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
use Illuminate\Validation\Rule;
use App\Admin\Forms\ProfileForm;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use App\Models\ChangeLevelHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use App\Admin\Selectable\ImageColors;
use Illuminate\Support\Facades\Cache;
use App\Admin\Actions\DeletePackAction;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Admin\Actions\ChangeAgencyAction;
use App\Admin\Actions\ChargeSwitchAction;
use App\Admin\Actions\InviteSwitchAction;
use App\Admin\Actions\KickOfAgencyAction;
use App\Admin\Actions\KickOfFamilyAction;
use App\Admin\Actions\CanPlaySwitchAction;
use App\Admin\Actions\DeleteUserVipAction;
use App\Admin\Actions\EditPackExpireAction;
use App\Models\UsersJoinedAgency;

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

        $content = $content->title(__($this->title));

        // Conditionally add the first row
        // if (Admin::user()->can('actions-switch' . $this->permission_name) || Admin::user()->can('*')) {
        //     $content = $content->row(function (Row $row) {
        //         $row->column(12, $this->grid2());
        //     });
        // }

        // Add the second row unconditionally
        $content = $content->row(function ($row) {
            $row->column(12, $this->grid());
        });

        return $content;
    }


    protected function grid2()
    {
        $transfer_salary = settings()->get('transfer_salary');
        $stop_invite_code = settings()->get('stop_invite_code');
        $stop_charge = settings()->get('stop_charge');
        $make_rooms_top = settings()->get('make_rooms_top');
        $make_gift_top = settings()->get('close_open_gifts');


        return (new Box(
            title: __('admin.Actions'),
            content: view('admin.grid.users.userChargeViewNew', compact(['stop_charge', 'make_rooms_top', 'stop_invite_code', 'transfer_salary', 'make_gift_top'])),
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
                // $filter->equal('family_id', __('Family'))->select(Common::by_family_filter());

                $filter->where(function ($query) {
                    $input = $this->input; // adjust as per your framework
                    $query->where('family_id', $input)
                        ->orWhereHas('family', function ($q) use ($input) {
                            $q->where('name', 'like', "%{$input}%");
                        });
                }, __('Family ID or Name'));
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

        $grid->column('name', __('Name'))
            ->display(function ($name) {

                $user = $this;
                if (! $user) {
                    return '';
                }

                return app(UserService::class)->adminUserAvatar($user);
            });


        $arrowIcon = asset('images/arrows.png'); // Path to the arrows.png image



        $grid->column('agency', __('Agency'))
            ->display(function () {
                $agency = $this->agency;
                if (! $agency) {
                    return '';
                }

                return app(AgencyService::class)->adminAgencyData($agency);
            });

        Admin::style('tr{background-color:var(--table-background-color);}.btn-circle {width: 30px; height: 30px; font-size:15px; border-radius: 50%; text-align: center; }');
        Admin::style("
            .modal-dialog {
                max-width: 90%;
            }

            .modal {
                top: 5%;
            }

            .modal-body {
                max-height: 70vh !important;
                overflow-y: auto !important;
            }
        ");
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


        $permission = $this->permission_name;
        $grid->actions(function ($actions) use ($permission) {
            $model = $actions->row;

            if (Admin::user()->can('charge-switch-' . $permission) || Admin::user()->can('*')) {
                $actions->add(new ChargeSwitchAction());
            }
            if (Admin::user()->can('invite-switch-' . $permission) || Admin::user()->can('*')) {

                $actions->add(new InviteSwitchAction());
            }
            if (Admin::user()->can('can-Play-switch-' . $permission) || Admin::user()->can('*')) {

                $actions->add(new CanPlaySwitchAction());
            }
            if ($model->agency_id >= 1 && (Admin::user()->can('kick-agency-switch-' . $permission) || Admin::user()->can('*'))) {
                $actions->add(new KickOfAgencyAction());
            }
            if ($model->family_id >= 1 && (Admin::user()->can('kick-family-switch-' . $permission) || Admin::user()->can('*'))) {
                $actions->add(new KickOfFamilyAction());
            }
            if ($model->agency_id >= 1 && (Admin::user()->can('chang-agency-switch-' . $permission) || Admin::user()->can('*'))) {
                $actions->add(new ChangeAgencyAction($model->id));
            }
            if ($model->phone = '+201000100010') {
                $actions->disableDelete();
            }

            if (! Admin::user()->can('delete-' . $permission) && !Admin::user()->can('*')) {
                $actions->disableDelete();
            }


            if (! Admin::user()->can('edit-' . $permission) && !Admin::user()->can('*')) {
                $actions->disableEdit();
            }
            if (! Admin::user()->can('show-' . $permission) && !Admin::user()->can('*')) {
                $actions->disableView();
            }
        });
        if (config('app.env') == 'production') $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableRowSelector();

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

    public function show($id, Content $content,)
    {
        $month = request('month'); // e.g., "5" for May
        $year = request('year');
        $start = request('start_at');
        $end = request('end_at');
        $tab = request('tab') ?? 'salary';
        $joinDate = request('join_date');
        $user = User::with('profile')->find($id);
        $type = request('type') ?? 4;
        $agencyId = request('agency_id');

        $userJoinAgencies = UsersJoinedAgency::where('user_id', $id)->with('agency')->when(isset($joinDate), function ($query) use ($joinDate) {
            $query->whereDate('join_date', $joinDate);
        })->paginate(10, ['*'], 'user_agency_page');

        $packs = Pack::where('user_id', $id)->where('type', $type)->with('admin', 'userVip')->whereHas('ware')->with(['ware' => function ($q) {
            $q->select('id', 'show_img');
        }])->orderByDesc('is_used')->paginate(10, ['*'], 'pack_page');
        $userVips = UserVip::where('user_id', $id)->paginate(10, ['*'], 'vip_page');
        $hasVip = UserVip::where('user_id', $id)
            ->where('is_used', 1)
            ->exists();

        $salaries = UserSallary::where('user_id', $id)
            ->with('agency')
            ->when(isset($year), function ($query) use ($year) {
                $query->where('year', $year);
            })->when(isset($month), function ($query) use ($month) {
                $query->where('month', $month);
            })->orderByDesc('id')->paginate(10, ['*'], 'salary_page');

        $typeMap = PACK_USER;

        $types =  collect($typeMap);
        $userPackTypes = Pack::where('user_id', $id)->pluck('type')->unique()->toArray();
        // $userPackTypes = $this->typesByLevel($id);
        $currentType = request()->get('type', $types->keys()->first());
        if ($userPackTypes) {
            $types = collect($typeMap)->filter(function ($name, $key) use ($userPackTypes) {
                return in_array($key, $userPackTypes);
            });
        } else {
            $types = $types;
        }
        $chargeTabType = request()->get('type', 'receiver');
        $giftType = request()->get('gift_type', 'receiver');

        $charges = Charge::query()
            ->when($chargeTabType == 'receiver', function ($q) use ($id) {
                $q->where('user_id', $id)->where('user_type', 'user');
            })
            ->when($chargeTabType == 'charger', function ($q) use ($id) {
                $q->where('charger_id', $id)->where('charger_type', 'user');
            })
            ->with(Common::chargerRelationsQuery())
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'charges_page');


        $giftSLogs = GiftLog::when($giftType == 'receiver', function ($q) use ($id) {
            $q->where('receiver_id', $id);
        })->when($giftType == 'sender', function ($q) use ($id) {
            $q->where('sender_id', $id);
        })->with('receiver', 'sender', 'gift', 'room', 'agency')->when(isset($start) && isset($end), function ($query) use ($start, $end) {
            $query->whereBetween('created_at', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ]);
        })->when(isset($agencyId), function ($query) use ($agencyId) {
            $query->where('agency_id', $agencyId);
        })->orderByDesc('id')->paginate(10, ['*'], 'gift_page');

        $diamonds = GiftLog::when($giftType == 'receiver', function ($q) use ($id) {
            $q->where('receiver_id', $id);
        })->when($giftType == 'sender', function ($q) use ($id) {
            $q->where('sender_id', $id);
        })->when(isset($start) && isset($end), function ($query) use ($start, $end) {
            $query->whereBetween('created_at', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ]);
        })->when(isset($agencyId), function ($query) use ($agencyId) {
            $query->where('agency_id', $agencyId);
        })->selectRaw('SUM(giftPrice) AS total')->value('total');

        $userJoinAgencies = UsersJoinedAgency::with(['kickedByApp', 'kickedByAdmin'])->where('user_id', $id)->with('agency')->when(isset($joinDate), function ($query) use ($joinDate) {
            $query->whereDate('join_date', $joinDate);
        })->orderByDesc('id')->paginate(10, ['*'], 'user_agency_page');

        $usersCoins = UserCoinLog::where('user_id', $id)
            ->when(request('from_date'), fn($q) => $q->whereDate('from_date', '>=', request('from_date')))
            ->when(request('to_date'), fn($q) => $q->whereDate('to_date', '<=', request('to_date')))
            ->when(request('sub_type'), fn($q) => $q->where('sub_type', request('sub_type')))
            ->orderByDesc('id')->paginate(10, ['*'], 'coins_page');
        $data = compact('user', 'packs', 'userVips', 'salaries', 'userJoinAgencies', 'types', 'currentType', 'charges', 'tab', 'chargeTabType', 'giftSLogs', 'giftType', 'diamonds', 'hasVip', 'usersCoins');
        return  parent::show($id, $content->title(__('user profile'))
            ->view('user_profile', $data));
    }

    public static function typesByLevel($id)
    {
        $userVipLevels = UserVip::where('user_id', $id)
            ->with(['OVip.privilegs'])
            ->get()
            ->filter(fn($vip) => $vip->OVip)
            ->groupBy(fn($vip) => $vip->OVip->level);

        $typesByLevel = [];

        foreach ($userVipLevels as $level => $vips) {
            $types = $vips
                ->flatMap(function ($vip) {
                    return $vip->OVip->privilegs->pluck('type');
                })
                ->unique()
                ->values();

            $typesByLevel[$level] = $types->toArray();
        }

        return $typesByLevel;
    }



    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());
        $this->disableFormTools($form);

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
        $form->text('original_uuid', __('uuid'))->updateRules(['required', "unique:users,uuid,{{id}}"]);

        // $form->switch('is_gold_id', trans('	is_gold_id'))->states (Common::getSwitchStates());

        $form->image('photo', __('image'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        });

        $form->image('profile.image_id', __('image Id'));
        // stop upload image
        // Admin::script(
        //     <<<'JS'
        //         $(function() {
        //             // For every file/image input (fileinput plugin)
        //             $('.btn-file').hide(); // Hide browse/upload buttons (common Bootstrap Fileinput class)
        //             $('.fileinput-upload').hide(); // Hide upload buttons if present
        //             $('input[type="file"]').prop('disabled', true); // Prevent any file selection
        //         });
        //     JS
        // );

        if (!Admin::user()->can('delete-profile-switch-' . $this->permission_name)) {
            Admin::script(
                <<<JS
                    $(document).ready(function() {
                        $('input[name="photo"]').closest('.form-group').find('.fileinput-remove').hide();
                    });
                    JS
            );
        }


        $form->hasMany('images', __('Profile Images'), function ($form) {
            $form->image('img', __('Image'));
        })->useTable()->disableCreate()->disableDelete();

        if (!Admin::user()->can('delete-profile-switch-' . $this->permission_name) && !Admin::user()->can('*')) {
            Admin::script(
                <<<JS
        $(document).ready(function() {
            // Hide 'remove' button on main image
            $('input[name="photo"]').closest('.form-group').find('.fileinput-remove').hide();

            // Hide 'remove' (×/close) button in hasMany images block, try these selectors:
            $('.has-many-images .has-many-remove').hide();
            $('.has-many-images .remove').hide();
            $('.has-many-images .close').hide();
            // Try direct selector as fallback for any <a> close button in hasMany block
            $('.has-many-images a.close').hide();
        });
        JS
            );
        }
        //        $form->multipleImage('images', 'Images');
        //
        //        if (!Admin::user()->can('delete-profile-switch-' . $this->permission_name)) {
        //            Admin::script(
        //                <<<JS
        //        $(document).ready(function() {
        //            $('input[name="images[]"]').closest('.form-group').find('.fileinput-remove').hide();
        //        });
        //        JS
        //            );
        //        }


        $form->select('country_id', trans('country'))->options(function () {
            $ops       = [null => __('no country')];
            $countries = Country::all();
            foreach ($countries as $country) {
                $ops[$country->id] = App::isLocale('en') ? $country->e_name : $country->name;
            }
            return $ops;
        });

        $form->select('profile.gender', __('gender'))->options([0 => __('female'), 1 => __('male')]);
        $form->email('email', __('Email'))->attribute('onfocus', "this.removeAttribute('readonly');")->attribute('readonly');
        $form->password('password', __('Password'))->attribute('onfocus', "this.removeAttribute('readonly');")->attribute('readonly')->creationRules('required');
        $form->text('phone', __('phone'))->creationRules(['nullable', "unique:users,phone,{{id}}"])->updateRules(['nullable', "unique:users,phone,{{id}}"]);


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
            $form->model()->uuid = $form->original_uuid;
            // $user = User::find($user_id);
            // $originalProfile = $user->profile;
            // $newAvatar = request()->input('profile.avatar'); // still okay if tightly coupled

            // if ($originalProfile && $newAvatar && $originalProfile->avatar !== $newAvatar) {
            //     $newCount = $user->profile_count + 1;
            //     $user->profile_count = $newCount;
            //     $user->save();

            //     // Upload and update avatar
            //     $form->model()->profile->avatar =  Common::uploadProfileUser('profile', $newAvatar, $originalProfile->id, $newCount);
            //     // dd($newImagePath);

            // }
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


    public function deletePack($id)
    {
        $pack = Pack::find($id);

        if (!$pack) {
            return response()->json([
                'status' => 404,
                'message' => __('not_found'),
            ], 404);
        }

        $pack->delete();

        return Redirect::back();
    }

    public function free(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:packs,id',
            'type' => 'required|in:0,1',
            'days' => 'required|integer|min:1',

        ]);

        $pack = Pack::find($request->id);
        $ex = ($request->days ?: 0);
        if (empty($pack->expire)) {
            $pack->days += $ex;
            $pack->save();
            return Redirect::back();
        }

        if ($request->type == 0) {
            $pack->expire += $ex * 86400;
        } else {
            $pack->expire -= $ex * 86400;
        }
        $pack->save();
        return Redirect::back();
    }

    public function deleteUserVip($id)
    {
        $userVip = UserVip::find($id);
        $userVip->packs()->delete();
        $user = User::query()->find($userVip->user_id);
        if ($user) {
            if ($user->vip == $userVip->id) {
                $uvip = UserVip::query()->where('user_id', $user->id)->where('id', '!=', $userVip->id)->orderByDesc('level')->first();
                if ($uvip) {
                    $user->vip = $uvip->id;
                    $user->save();
                }
            }
        }
        $userVip->delete();
        return Redirect::back();
    }


    public function editLevelUser(Request $request)
    {
        $user = User::find($request->id);

        ChangeLevelHistory::create([
            'user_id' => $user->id,
            'admin_id' => Auth::id(),
            'old_total_sender_level' => $user->total_sender_level,
            'new_total_sender_level' => $request->total_sender_level,
            'old_total_received_level' => $user->total_received_level,
            'new_total_received_level' => $request->total_received_level,

        ]);

        $user->total_sender_level = $request->total_sender_level;
        $user->total_received_level = $request->total_received_level;
        $user->save();
        return Redirect::back();
    }
}
