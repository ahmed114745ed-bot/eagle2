<?php

namespace App\Admin\Controllers;

use App\Models\Bd;
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
use App\Models\UsersJoinedAgency;
use Encore\Admin\Auth\Permission;
use Modules\UsersWallet\Entities\WalletLog;
use Modules\Vip\Entities\UserVip;
use App\Models\ChangeLevelHistory;
use Illuminate\Support\Facades\DB;
use App\Admin\Services\UserService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use App\Admin\Selectable\ImageColors;
use App\Admin\Services\AgencyService;
use Illuminate\Support\Facades\Cache;
use Modules\Badge\Entities\UserBadge;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Admin\Actions\ChangeAgencyAction;
use App\Admin\Actions\ChargeSwitchAction;
use App\Admin\Actions\InviteSwitchAction;
use App\Admin\Actions\KickOfAgencyAction;
use App\Admin\Actions\KickOfFamilyAction;
use App\Admin\Actions\CanPlaySwitchAction;

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

        // Optimize eager loading
        $grid->model()
            // ->when($countryID, fn($q) => $q->whereIn('country_id', $countryID))
            ->select(['id', 'name', 'sender_level', 'received_level', 'device_token', 'agency_id', 'family_id', 'uuid', 'special_id', 'di', 'can_play', 'huawei_version', 'android_version', 'ios_version', 'country_id', 'transfer_salary', 'is_bd'])
            ->with([
                'profile',
                'agency',
                'userSetting',
                'country',
                //            'sameDeviceUsers:id,name,uuid,special_id,sender_level,received_level',
                'senderLevel',
                'receiverLevel',
                'monthlyDiamondReceive',
                'packs' => fn($q) => $q->whereIn('type', [25])->where('is_used', true)->with('ware:id,value')
            ])->withCount('sameDeviceUsers');

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
                fn($q) =>
                $q->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
            );
        }

        if (request()->never_send == 1) {
            $grid->model()->doesntHave('chatMessages');
        }

        if (request()->sent_messages == 1) {
            $grid->model()->has('chatMessages');
        }

        if (request()->agencyMembers == 1) {
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
                if (! $user) {
                    return __('No User');
                }
                return app(UserService::class)->adminUserAvatar($user);
            });


        $arrowIcon = asset('images/arrows.png'); // Path to the arrows.png image



        $grid->column('agency_id', __('Agency'))
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
            $count = $this->same_device_users_count;
            return "<button class='btn btn-sm btn-primary show-same-device-modal' data-user-id='{$this->id}'>$count</button>";
        });

        $grid->column('versions', __('versions'))->modal(__('versions'), function () {
            $data = [
                ['iOS',     $this->ios_version],
                ['Huawei',  $this->huawei_version],
                ['Android', $this->android_version],
            ];

            return new Table(
                [__('Name'), __('Version')], // headers
                $data                        // rows
            );
        });
        $permission = $this->permission_name;


        // $grid->column('bd_action', __('BD Action'))->display(function () {
        //     if ($this->is_bd == 1) {
        //         $btn  = '<button type="button" class="btn btn-danger btn-sm remove-bd-btn" ';
        //         $btn .= 'data-id="' . $this->id . '" data-url="' . route('users.remove', $this->id) . '">';
        //         $btn .= __('Remove BD') . '</button>';
        //         return $btn;
        //     }
        //     return '';
        // });

        // Admin::script(<<<'JS'
        //     $(document).on('click', '.remove-bd-btn', function (e) {
        //         e.preventDefault();
        //         let btn = $(this);
        //         let url = btn.data('url');

        //         Swal.fire({
        //             title: 'هل أنت متأكد؟',
        //             text: "لن تستطيع التراجع بعد الحذف!",
        //             showCancelButton: true,
        //             confirmButtonColor: '#d33',
        //             cancelButtonColor: '#3085d6',
        //             confirmButtonText: 'نعم، احذف',
        //             cancelButtonText: 'إلغاء'
        //         }).then((result) => {
        //             if (result.value) {
        //                 let form = $('<form>', {
        //                     'method': 'POST',
        //                     'action': url
        //                 }).append($('<input>', {
        //                     'type': 'hidden',
        //                     'name': '_token',
        //                     'value': LA.token
        //                 })).append($('<input>', {
        //                     'type': 'hidden',
        //                     'name': '_method',
        //                     'value': 'POST'
        //                 }));
        //                 form.appendTo('body').submit();
        //             }
        //         });
        //     });
        // JS);



        Admin::script("
                    $(document).on('click', '.show-same-device-modal', function() {
                        console.log('here');
                        var userId = $(this).data('user-id');
                        $('#sameDeviceUsersModal .modal-body').html('Loading...');
                        $('#sameDeviceUsersModal').modal('show');
                        $.get('/admin/users/' + userId + '/same-device-users-table', function(html) {
                            $('#sameDeviceUsersModal .modal-body').html(html);
                        });
                    });
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
            if ($model->agency_id >= 1 && (Admin::user()->can('kick-agency-switch-' . $permission) || Admin::user()->can('*'))) {
                $actions->add(new KickOfAgencyAction());
            }
            if ($model->family_id >= 1 && (Admin::user()->can('kick-family-switch-' . $permission) || Admin::user()->can('*'))) {
                $actions->add(new KickOfFamilyAction());
            }
            if ($model->agency_id >= 1 && (Admin::user()->can('chang-agency-switch-' . $permission) || Admin::user()->can('*'))) {
                $actions->add(new ChangeAgencyAction($model->id));
            }
            if ($model->phone == '+201000100010') {
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

    public function show($id, Content $content)
    {
        $timezone = Common::timeZone();
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
        }])->orderByDesc('is_used')->latest()->paginate(10, ['*'], 'pack_page');

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
        })->with('receiver', 'sender', 'gift', 'room', 'agency')->when(isset($start) && isset($end), function ($query) use ($start, $end, $timezone) {
            $startUtc = Carbon::parse($start, $timezone)->startOfDay()->timezone('UTC');
            $endUtc   = Carbon::parse($end, $timezone)->endOfDay()->timezone('UTC');

            $query->whereBetween('created_at', [$startUtc, $endUtc]);
        })->when(isset($agencyId), function ($query) use ($agencyId) {
            $query->where('agency_id', $agencyId);
        })->orderByDesc('id')->paginate(10, ['*'], 'gift_page');

        $diamonds = GiftLog::when($giftType == 'receiver', function ($q) use ($id) {
            $q->where('receiver_id', $id);
        })->when($giftType == 'sender', function ($q) use ($id) {
            $q->where('sender_id', $id);
        })->when(isset($start) && isset($end), function ($query) use ($start, $end, $timezone) {
            $startUtc = Carbon::parse($start, $timezone)->startOfDay()->timezone('UTC');
            $endUtc   = Carbon::parse($end, $timezone)->endOfDay()->timezone('UTC');

            $query->whereBetween('created_at', [$startUtc, $endUtc]);
        })->when(isset($agencyId), function ($query) use ($agencyId) {
            $query->where('agency_id', $agencyId);
        })->selectRaw('SUM(giftPrice) AS total')->value('total');

        $userJoinAgencies = UsersJoinedAgency::with(['kickedByApp', 'kickedByAdmin'])->where('user_id', $id)->with('agency')->when(isset($joinDate), function ($query) use ($joinDate) {
            $query->whereDate('join_date', $joinDate);
        })->orderByDesc('id')->paginate(10, ['*'], 'user_agency_page');

        \DB::enableQueryLog(); // Before the query

        $usersCoins = UserCoinLog::where('user_id', $id)
            ->when(request('from_date'), fn($q) => $q->whereDate('from_date', '>=', request('from_date')))
            ->when(request('to_date'), fn($q) => $q->whereDate('to_date', '<=', request('to_date')))
            ->when(request('sub_type'), fn($q) => $q->where('sub_type', request('sub_type')))
            ->orderByDesc('id')->paginate(10, ['*'], 'coins_page');


        $userBadges = UserBadge::where('user_id', $id)->active()->with("badge")->get();
        $countries = $this->countries();
        $badges = UserBadge::where('user_id', $id)->with('admin')
            ->orderByRaw("
                    CASE 
                        WHEN expire = 0 THEN 0
                        WHEN expire >= ? THEN 0
                        ELSE 1
                    END
                ", [now()->timestamp])
            ->orderByDesc('expire')
            ->paginate(10, ['*'], 'badges_page');
        
        $curantBalance = wallet_available_by_user($id);
        $walletLogs = WalletLog::where('user_id', $user->id) ->orderBy('id', 'DESC') ->paginate(20);
       
       
        $data = compact('user', 'packs', 'type', 'userVips', 'salaries', 'userJoinAgencies', 'types', 'currentType', 'timezone', 'charges', 'tab', 'chargeTabType', 'giftSLogs', 'giftType', 'diamonds', 'hasVip', 'usersCoins', 'badges', 'countries' ,'availableBalance','curantBalance','walletLogs');
        return  parent::show($id, $content->title(__('user profile'))
            ->view('user_profile', $data));
    }

    public function countries()
    {
        $ops       = [null => __('no country')];
        $countries = Country::all();
        foreach ($countries as $country) {
            $ops[$country->id] = App::isLocale('en') ? $country->e_name : $country->name;
        }
        return $ops;
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
                $ops[$country->id] = App::isLocale('en') ?  ($country->e_name ?? $country->name) : $country->name;
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
            $dataUserProfile['avatar']  = Common::upload('images', $request->file('image'));
        }
        if ($profileUser) {

            $profileUser->update($dataUserProfile);
        } else {
            Profile::create($dataUserProfile);
        }

        return Redirect::back();
    }

    // app/Admin/Controllers/UsersAppController.php

    public function ajaxSameDeviceUsersTable($id)
    {
        $user = User::with(['sameDeviceUsers.profile'])->findOrFail($id);
        $users = $user->sameDeviceUsers;

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
        // Return just table's HTML (your AJAX will inject this)
        return $table->render();
    }


    public function removeBD($id)
    {

        Bd::where('app_id', $id)->update(['app_id' => 0]);
        $user = User::findOrFail($id);
        $user->is_bd = 0;
        $user->save();

        return redirect()->back();
    }

    public function deleteBadge($id)
    {
        UserBadge::where('id', $id)->delete();
        return redirect()->back();
    }
}
