<?php

namespace App\Admin\Controllers;

use App\Helpers\AgencyPackageHelper;
use Utd\Bd\Entities\Bd;
use Carbon\Carbon;
use App\Models\Pack;
use App\Models\User;
use App\Models\Charge;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\Country;
use Utd\Gifts\Entities\GiftLog;
use App\Models\Profile;
use App\Models\UserCoinLog;
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
use Utd\UsersWallet\Entities\WalletLog;
use Utd\Vip\Entities\UserVip;
use App\Models\ChangeLevelHistory;
use Illuminate\Support\Facades\DB;
use App\Admin\Services\UserService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use App\Admin\Selectable\ImageColors;
use App\Admin\Services\AgencyService;
use Illuminate\Support\Facades\Cache;
use Utd\Badge\Entities\UserBadge;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Admin\Actions\ChargeSwitchAction;
use App\Admin\Actions\InviteSwitchAction;
use Utd\Family\Admin\Actions\KickOfFamilyAction;
use App\Admin\Actions\CanPlaySwitchAction;
use App\Support\PackageHelper;

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
        })->row(view('admin.same_device_users_modal'));

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
        $countryID = empty((array)session('filter_country_id')) ? Common::areaCountries() : (array)session('filter_country_id');

        $grid = new Grid(new User());
        $haveCoins = (request()->have_coins == 1);

        // Build select columns - conditionally include agency_id
        $selectColumns = ['id', 'name', 'sender_level', 'received_level', 'device_token', 'family_id', 'uuid', 'special_id', 'di', 'can_play', 'huawei_version', 'android_version', 'ios_version', 'country_id', 'transfer_salary', 'is_bd'];
        if (AgencyPackageHelper::isAgencyInstalled()) {
            $selectColumns[] = 'agency_id';
        }

        // Build eager loading - conditionally include agency
        $eagerLoads = [
            'profile',
            'userSetting',
            'country',
            'senderLevel',
            'receiverLevel',
            'monthlyDiamondReceive',
            'packs' => fn($q) => $q->whereIn('type', [25])->where('is_used', true)->with('ware:id,value')
        ];
        if (AgencyPackageHelper::isAgencyInstalled()) {
            $eagerLoads['agency'] = fn($q) => $q;
        }

        // Optimize eager loading
        $grid->model()
            // ->when($countryID, fn($q) => $q->whereIn('country_id', $countryID))
            ->select($selectColumns)
            ->with($eagerLoads)->withCount('sameDeviceUsers');

        if (request()->signups == 'today') {
            $grid->model()->whereDate('created_at', today());
        }

        if (request()->signups == 'week') {
            $grid->model()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        if (request()->signups == 'month') {
            $grid->model()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }

        if (request()->messages == 'today') {
            $grid->model()->whereHas('chatMessages', fn($q) => $q->whereDate('created_at', today()));
        }

        if (request()->messages == 'month') {
            $grid->model()->whereHas(
                'chatMessages',
                fn($q) => $q->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
            );
        }

        if (request()->never_send == 1) {
            $grid->model()->doesntHave('chatMessages');
        }

        if (request()->sent_messages == 1) {
            $grid->model()->has('chatMessages');
        }

        if (request()->agencyMembers == 1 && AgencyPackageHelper::isAgencyInstalled()) {
            $grid->model()->where('agency_id', '!=', 0)
                ->whereHas('agency', function ($q) use ($countryID) {
                    $q->where('country_id', $countryID);
                });
        }

        if (request()->online == 1) {
            $grid->model()->where('online', 1);
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
                if (!$user) {
                    return __('No User');
                }
                return app(UserService::class)->adminUserAvatar($user);
            });


        $arrowIcon = asset('images/arrows.png'); // Path to the arrows.png image

        // Only show agency column if agency package is installed
        if (AgencyPackageHelper::isAgencyInstalled()) {
            $grid->column('agency_id', __('Agency'))
                ->display(function () {
                    $agency = $this->agency;
                    if (!$agency) {
                        return '';
                    }

                    return app(AgencyService::class)->adminAgencyData($agency);
                });
        }

        Admin::style('.btn-circle {width: 30px; height: 30px; font-size:15px; border-radius: 50%; text-align: center; }');
        Admin::style("
            .modal-dialog {
                max-width: 90%;
            }

            .modal-body {
                max-height: 70vh !important;
                overflow-y: auto !important;
            }
        ");

        $grid->column('custom_button2', __('accounts number'))->display(function () {
            $count = $this->same_device_users_count;
            return "<button class='btn btn-sm btn-primary show-same-device-modal' data-user-id='{$this->id}'>$count</button>";
        });

        $grid->column('versions', __('versions'))->modal(__('versions'), function () {
            $data = [
                ['iOS', $this->ios_version],
                ['Huawei', $this->huawei_version],
                ['Android', $this->android_version],
            ];

            return new Table(
                [__('Name'), __('Version')], // headers
                $data                        // rows
            );
        });
        $permission = $this->permission_name;

        Admin::script("
            // Initial modal open
            $(document).on('click', '.show-same-device-modal', function() {
                var userId = $(this).data('user-id');
                loadSameDeviceUsers(userId, 1);
            });

            // Pagination click
            $(document).on('click', '.ajax-pagination', function(e) {
                e.preventDefault();
                var userId = $(this).data('user-id');
                var page = $(this).data('page');

                if (!$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
                    loadSameDeviceUsers(userId, page);
                }
            });

            function loadSameDeviceUsers(userId, page) {
                $('#sameDeviceUsersModal .modal-body').html('<div class=\"text-center\"><i class=\"fa fa-spinner fa-spin fa-2x\"></i></div>');
                $('#sameDeviceUsersModal').modal('show');

                $.get('/admin/users/' + userId + '/same-device-users-table', { page: page }, function(html) {
                    $('#sameDeviceUsersModal .modal-body').html(html);
                });
            }
        ");


        $grid->actions(function ($actions) use ($permission) {
            $model = $actions->row;

            if (Admin::user()->can('charge-switch-' . $permission) || Admin::user()->can('*')) {
                $actions->add(new ChargeSwitchAction());
            }
            if (Admin::user()->can('invite-switch-' . $permission) || Admin::user()->can('*')) {

                $actions->add(new InviteSwitchAction());
            }
            if (Admin::user()->can('can-Play-switch-' . $permission) || Admin::user()->can('*')) {

                $row = $actions->row; // force load

                $actions->add(new \App\Admin\Actions\CanPlaySwitchAction($row['can_play']));
            }
            // Only show agency actions if package is installed
            if (AgencyPackageHelper::isAgencyInstalled()) {
                if ($model->agency_id >= 1 && (Admin::user()->can('kick-agency-switch-' . $permission) || Admin::user()->can('*'))) {
                    $actions->add(new \Utd\Agency\Actions\KickFromAgencyAction());
                }
            }
            if ($model->family_id >= 1 && (Admin::user()->can('kick-family-switch-' . $permission) || Admin::user()->can('*'))) {
                $actions->add(new KickOfFamilyAction());
            }
            // Only show change agency action if package is installed
            if (AgencyPackageHelper::isAgencyInstalled()) {
                if ($model->agency_id >= 1 && (Admin::user()->can('chang-agency-switch-' . $permission) || Admin::user()->can('*'))) {
                    if (class_exists(\Utd\Agency\Actions\ChangeAgencyAction::class)) {
                        $actions->add(new \Utd\Agency\Actions\ChangeAgencyAction($model->id));
                    }
                }
            }
            if ($model->phone == '+201000100010') {
                $actions->disableDelete();
            }

            if (!Admin::user()->can('delete-' . $permission) && !Admin::user()->can('*')) {
                $actions->disableDelete();
            }


            if (!Admin::user()->can('edit-' . $permission) && !Admin::user()->can('*')) {
                $actions->disableEdit();
            }
            if (!Admin::user()->can('show-' . $permission) && !Admin::user()->can('*')) {
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
        $value = $request->make_rooms_top == "true" ? "1" : "0";
        settings()->set("make_rooms_top", $value);
        Cache::forever('rooms_make_rooms_top', $value);
    }

    public function transferSalary(Request $request)
    {
        if ($request->transfer_salary == "true") {
            settings()->set("transfer_salary", "1");
        } else {
            settings()->set("transfer_salary", "0");
        }
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



    public function show($id, Content $content)
    {
        $timezone   = Common::timeZone();
        $month      = request('month');
        $year       = request('year');
        $start      = request('start_at');
        $end        = request('end_at');
        $joinDate   = request('join_date');
        $type       = request('type', 4);
        $agencyId   = request('agency_id');
        $chargeTabType = request('type', 'receiver');
        $giftType   = request('gift_type', 'receiver');
        $packs = null;
        $types = collect();
        $currentType = null;
        $userVips = $hasVip = null;
        $salaries = null;
        $charges = null;
        $giftSLogs = $diamonds = null;
        $userJoinAgencies = null;
        $usersCoins = null;
        $badges = null;
        $walletLogs = null;

        // Decide active tab early so we only eager load what we need
        $activeTab = request('tab', 'packs');

        /* =========================
     | USER (ONE QUERY ONLY) — conditional eager loading + select
     ========================= */
        $userQuery = User::query()->select(['id', 'name', 'uuid', 'special_id', 'country_id', 'di']);

        $with = [
            'profile:id,user_id,avatar',
            'country:id,name,flag,language,e_name,phone_code,iso,iso_numeric,currency_numeric',
            'senderLevel',
            'receiverLevel',
        ];

        // Only load packs when viewing packs tab
        if ($activeTab === 'packs') {
            $with['packs'] = function ($q) {
                $q->where('type', 25)
                    ->where('is_used', true)
                    ->where(fn($q) => $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp))
                    ->with('ware:id,value');
            };
        }

        $user = $userQuery->with($with)->findOrFail($id);

        // Avoid duplicate wallet calls
        $availableBalance = $curantBalance = wallet_available_by_user($id);
        /* =========================
        | USER IMAGE
        ========================= */
        $defaultImage = asset('images/businessman-icon.jpg');
        $avatar = optional($user->profile)->avatar;
        $user->display_image = isImageExists(getImagePath($avatar)) ? getImagePath($avatar) : $defaultImage;
        // $activeTab already set above
        switch ($activeTab) {

            case 'packs':
                $types = collect(PACK_USER);
                $packBase = Pack::with(['userVip.admin:id,name,avatar', 'admin:id,name,avatar', 'userVip', 'sender', 'ware:id,show_img'])
                    ->where('user_id', $id)
                    ->whereHas('ware')
                    ->whereNull('deleted_at');

                $packs = (clone $packBase)
                    ->where('type', request('type', 4))
                    ->orderByDesc('is_used')
                    ->latest()
                    ->paginate(10, ['*'], 'pack_page');

                $userPackTypes = (clone $packBase)->pluck('type')->unique()->toArray();
                $types = $types->filter(fn($_, $key) => in_array($key, $userPackTypes));
                $currentType = request('type', $types->keys()->first());
                break;

            case 'vips':
                $userVips = PackageHelper::isInstalled('vip') ? UserVip::where('user_id', $id)->paginate(10, ['*'], 'vip_page') : collect();
                $hasVip = $userVips instanceof \Illuminate\Pagination\LengthAwarePaginator ? $userVips->contains('is_used', 1) : false;
                break;

            case 'salary':
                $year = request('year');
                $month = request('month');
                $salaries = UserSallary::with('agency:id,name')
                    ->where('user_id', $id)
                    ->when($year, fn($q) => $q->where('year', $year))
                    ->when($month, fn($q) => $q->where('month', $month))
                    ->orderByDesc('id')
                    ->paginate(10, ['*'], 'salary_page');
                break;

            case 'charge':
                $chargeTabType = request('type', 'receiver');
                $charges = Charge::with(Common::chargerRelationsQuery())
                    ->when($chargeTabType === 'receiver', fn($q) => $q->where('user_id', $id)->where('user_type', 'user'))
                    ->when($chargeTabType === 'charger', fn($q) => $q->where('charger_id', $id)->where('charger_type', 'user'))
                    ->orderByDesc('id')
                    ->paginate(10, ['*'], 'charges_page');
                break;

            case 'gift-log':
                $giftType = request('gift_type', 'receiver');
                $start = request('start_at');
                $end = request('end_at');
                $agencyId = request('agency_id');
                $timezone = Common::timeZone();

                $giftBaseQuery = GiftLog::query()
                    ->when($giftType === 'receiver', fn($q) => $q->where('receiver_id', $id))
                    ->when($giftType === 'sender', fn($q) => $q->where('sender_id', $id))
                    ->when($start && $end, fn($q) => $q->whereBetween('created_at', [
                        Carbon::parse($start, $timezone)->startOfDay()->utc(),
                        Carbon::parse($end, $timezone)->endOfDay()->utc(),
                    ]))
                    ->when($agencyId, fn($q) => $q->where('agency_id', $agencyId));

                $giftSLogs = (clone $giftBaseQuery)
                    ->with([
                        'receiver:id,name,uuid,special_id',
                        'sender:id,name,uuid,special_id',
                        // 'sender.packs' => function ($q) {
                        //     $q->whereIn('type', [25])
                        //         ->where('is_used', true)
                        //         ->with('ware:id,value');
                        // },
                        // 'receiver.packs' => function ($q) {
                        //     $q->whereIn('type', [25])
                        //         ->where('is_used', true)
                        //         ->with('ware:id,value');
                        // },
                        // 'receiver.profile',
                        // 'sender.profile',
                        'gift:id,name,price',
                        'room',
                        'agency:id,name',
                    ])
                    ->orderByDesc('id')
                    ->paginate(10, ['*'], 'gift_page');

                $diamonds = (clone $giftBaseQuery)->sum('giftPrice');
                break;

            case 'user-agency':
                $joinDate = request('join_date');
                $userJoinAgencies = UsersJoinedAgency::with(['agency:id,name,type', 'kickedByApp:id,name', 'kickedByAdmin:id,name'])
                    ->where('user_id', $id)
                    ->when($joinDate, fn($q) => $q->whereDate('join_date', $joinDate))
                    ->orderByDesc('id')
                    ->paginate(10, ['*'], 'user_agency_page');
                break;

            case 'user-coins':
                $usersCoins = UserCoinLog::where('user_id', $id)
                    ->when(request('from_date'), fn($q) => $q->whereDate('from_date', '>=', request('from_date')))
                    ->when(request('to_date'), fn($q) => $q->whereDate('to_date', '<=', request('to_date')))
                    ->when(request('sub_type'), fn($q) => $q->where('sub_type', request('sub_type')))
                    ->orderByDesc('id')
                    ->paginate(10, ['*'], 'coins_page');
                break;

            case 'badges':
                if (PackageHelper::isInstalled('badge')) {
                    $badges = UserBadge::with('admin:id,name')
                        ->where('user_id', $id)
                        ->orderByRaw("CASE WHEN expire = 0 THEN 0 WHEN expire >= ? THEN 0 ELSE 1 END", [now()->timestamp])
                        ->orderByDesc('expire')
                        ->paginate(10, ['*'], 'badges_page');
                }
                break;

            case 'wallet_logs':
                if (PackageHelper::isInstalled('usersWallet')) {
                    $year = request('year');
                    $month = request('month');
                    $walletLogs = WalletLog::where('user_id', $id)
                        ->when($year, fn($q) => $q->whereYear('created_at', $year))
                        ->when($month, fn($q) => $q->whereMonth('created_at', $month))
                        ->orderByDesc('id')
                        ->paginate(20, ['*'], 'wallet_logs_page')
                        ->appends(['tab' => 'wallet_logs', 'year' => $year, 'month' => $month]);
                }
                break;
        }
        $countries = $this->countries();

        /* =========================
     | VIEW
     ========================= */
        $data = compact(
            'user',
            'countries',
            'packs',
            'types',
            'giftType',
            'currentType',
            'userVips',
            'chargeTabType',
            'hasVip',
            'salaries',
            'charges',
            'giftSLogs',
            'diamonds',
            'userJoinAgencies',
            'usersCoins',
            'badges',
            'type',
            'walletLogs',
            'activeTab',
            'availableBalance',
            'curantBalance'
        );

        return parent::show($id, $content->title(__('user profile'))->view('user_profile', $data));
    }



    public function countries()
    {
        $ops = [null => __('no country')];
        $countries = Country::all();
        foreach ($countries as $country) {
            $ops[$country->id] = App::isLocale('en') ? $country->e_name : $country->name;
        }
        return $ops;
    }

    public static function typesByLevel($id)
    {
        $userVipLevels = PackageHelper::isInstalled('vip') ? UserVip::where('user_id', $id)
            ->with(['OVip.privilegs'])
            ->get()
            ->filter(fn($vip) => $vip->OVip)
            ->groupBy(fn($vip) => $vip->OVip->level) : collect();

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
            $userId = request()->route('user');
            $user = User::findOrFail($userId);
            $oldDiValue = $user->getOriginal('di');
            $oldDiamoundValue = $user->getOriginal('user_diamond');
        } else {
            $oldDiValue = null;
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

        // $form->hidden('transfer_salary', __('transfer_salary'))->default(0);

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

        $form->html('<div class="full-column-width">');
        $form->hasMany('images', __('Profile Images'), function ($form) {
            $form->image('img', __('Image'));
        })->useTable()->disableCreate()->disableDelete();
        $form->html('</div>');

        Admin::style('
            .has-many-images .has-many-images-forms {
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 20px !important;
            }

            .has-many-images .form-group {
                margin-bottom: 0 !important;
            }

            .has-many-images .file-preview-image {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                border-radius: 8px !important;
            }
        ');

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
            $ops = [null => __('no country')];
            $countries = Country::all();
            foreach ($countries as $country) {
                $ops[$country->id] = App::isLocale('en') ? ($country->e_name ?? $country->name) : $country->name;
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

        $form->belongsTo('image_color_id', ImageColors::class, __('Color'))->setElementName('full-column-width');

        $form->saving(function (Form $form) use ($oldDiValue, $oldDiamoundValue) {
            $type_user = request()->type_user;
            $model = $form->model();
            $user_id = $model->id;
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

            $agancy = null;
            if (AgencyPackageHelper::isAgencyInstalled()) {
                $agencyClass = AgencyPackageHelper::getAgencyClass();
                $agancy = $agencyClass::where('app_owner_id', $user_id)->first();
            }
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


    public function request_invite_code(Request $request)
    {
        if ($request->stop_invite_code == "true") {
            settings()->set("stop_invite_code", "1");
        } else {
            settings()->set("stop_invite_code", "0");
        }

        return true;
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
        $userVip = PackageHelper::isInstalled('vip') ? UserVip::find($id) : null;
        if (!$userVip) {
            return Redirect::back();
        }
        $userVip->packs()->delete();
        $user = User::query()->find($userVip->user_id);
        if ($user) {
            if ($user->vip == $userVip->id) {
                $uvip = PackageHelper::isInstalled('vip') ? UserVip::query()->where('user_id', $user->id)->where('id', '!=', $userVip->id)->orderByDesc('level')->first() : null;
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


    public function updateUsers(Request $request)
    {
        $user = User::find($request->id);
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'uuid' => [
                'sometimes',
                Rule::unique('users', 'uuid')->ignore($user->id),
            ],
            'phone' => [
                'nullable',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'email' => ['nullable', 'email'],
        ]);
        $data = [
            'name' => $request->name,
            'uuid' => $request->uuid,
            'email' => $request->email,
            'phone' => $request->phone,
            'bio' => $request->bio,
        ];
        $user->update($data);
        $profileUser = Profile::where('user_id', $user->id)->first();
        $dataUserProfile = [

            'gender' => $request->gender,

        ];
        if ($request->hasFile('image')) {
            $dataUserProfile['avatar'] = Common::upload('images', $request->file('image'));
        }
        if ($profileUser) {

            $profileUser->update($dataUserProfile);
        } else {
            Profile::create($dataUserProfile);
        }

        return Redirect::back();
    }

    // app/Admin/Controllers/UsersAppController.php

    public function ajaxSameDeviceUsersTable($id, Request $request)
    {
        $user = User::findOrFail($id);
        $perPage = 10;
        $page = $request->get('page', 1);

        if (empty($user->device_token)) {
            return '<div class="alert alert-warning text-center">
            This user has no device identifier.
        </div>';
        }

        $users = User::with('profile')
            ->where('device_token', $user->device_token)
            ->paginate($perPage, ['*'], 'page', $page);

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
                'createdAt' => $user->created_at,
            ];
        });

        $table = new Table([__('Name'), __('phone'), __('created_at')], $rows->toArray());
        $pagination = $this->buildAjaxPagination($users, $id);
        // Return just table's HTML (your AJAX will inject this)
        return $table->render() . $pagination;
    }

    private function buildAjaxPagination($paginator, $userId)
    {
        if ($paginator->lastPage() <= 1) {
            return '';
        }

        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        $html = '<nav aria-label="Page navigation" style="margin-top: 15px;">';
        $html .= '<ul class="pagination justify-content-center">';

        // Previous button
        $prevDisabled = $currentPage == 1 ? 'disabled' : '';
        $prevPage = $currentPage - 1;
        $html .= "<li class='page-item {$prevDisabled}'>";
        $html .= "<a class='page-link ajax-pagination' href='#' data-user-id='{$userId}' data-page='{$prevPage}'>&laquo;</a>";
        $html .= "</li>";

        // Page numbers
        for ($i = 1; $i <= $lastPage; $i++) {
            $active = $i == $currentPage ? 'active' : '';
            $html .= "<li class='page-item {$active}'>";
            $html .= "<a class='page-link ajax-pagination' href='#' data-user-id='{$userId}' data-page='{$i}'>{$i}</a>";
            $html .= "</li>";
        }

        // Next button
        $nextDisabled = $currentPage == $lastPage ? 'disabled' : '';
        $nextPage = $currentPage + 1;
        $html .= "<li class='page-item {$nextDisabled}'>";
        $html .= "<a class='page-link ajax-pagination' href='#' data-user-id='{$userId}' data-page='{$nextPage}'>&raquo;</a>";
        $html .= "</li>";

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }

    public function removeBD($id)
    {
        if (PackageHelper::isInstalled('bd')) {
            Bd::where('app_id', $id)->update(['app_id' => 0]);
        }
        $user = User::findOrFail($id);
        $user->is_bd = 0;
        $user->save();

        return redirect()->back();
    }

    public function deleteBadge($id)
    {
        if (PackageHelper::isInstalled('badge')) {
            UserBadge::where('id', $id)->delete();
        }
        return redirect()->back();
    }
}
