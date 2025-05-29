<?php

namespace App\Bd\Controllers;

use App\Admin\Controllers\MainController;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Agency;
use App\Models\Target;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Models\UserTarget;
use App\Helpers\UserCommon;
use App\Models\UserSallary;
use App\Models\AgencySallary;
use App\Models\AgencyUserJob;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Layout\Content;
use App\Models\AgencyJoinRequest;
use App\Models\UsersJoinedAgency;
use Encore\Admin\Actions\Response;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;
use App\Services\AppFeatureService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use App\Admin\Actions\DeleteAgencyAction;
use App\Traits\AdminTraits\AdminUserTrait;
use App\Admin\Actions\ChangeUsersAgencyAction;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Controllers\AdminController;


class AgencyController extends AdminController
{
    use HasResourceActions, AdminUserTrait;

    public $permission_name = 'agencies';
    public $hiddenColumns = [];
    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("agencies");
    }

    public function index(Content $content)
    {
        return parent::index($content
            ->title(__('Agencies'))
            ->description(__('List of Agencies'))
            ->row(function ($row) {



                $row->column(12, $this->grid());
            }));
    }

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

    // public function profile($id, Request $request, Content $content)
    // {
    //     $year = $request->year ?? Carbon::now()->year;
    //     $month = $request->month ?? Carbon::now()->month;
    //     $tab = request('tab') ?? null;

    //     $cacheKey = "agency_profile_{$id}";
    //     $agency = Agency::with([
    //         'admins',
    //         'charges' => function ($query) {
    //             $query->select('id', 'agency_id', 'amount', 'created_at')
    //                 ->latest()
    //                 ->take(10);
    //         },
    //         'mempers' => function ($query) {
    //             $query->select('id', 'agency_id', 'name', 'created_at')
    //                 ->latest()
    //                 ->take(10);
    //         },
    //         'owner' => function ($query) {
    //             $query->select('id', 'name', 'uuid');
    //         }
    //     ])->select('id', 'name', 'app_owner_id', 'phone', 'salary', 'coins', 'img')
    //         ->findOrFail($id);

    //     $path = @$agency->img;
    //     $defaultImage = asset("images/icon-agency.jpg");
    //     $imageUrl = getImagePath($path) ?? $defaultImage;

    //     if (!isImageExists($imageUrl)) {
    //         $imageUrl = $defaultImage;
    //     }

    //     $agency->display_image = $imageUrl;
    //     $agencyId = $agency->id;
    //     $members = $agency->mempers()
    //         ->select('id', 'name', 'uuid', 'total_days', 'monthly_diamond_received', 'agency_id', 'country_id')->with('country', 'agencyUserJob')
    //         ->paginate(10, ['*'], 'members_page');

    //     $charges = $agency->charges()
    //         ->select('id', 'amount', 'created_at')
    //         ->paginate(10, ['*'], 'charges_page');

    //     $salaries = AgencySallary::where('agency_id', $id)
    //         ->select('id', 'sallary', 'cut_amount', 'month', 'year', 'created_at')
    //         ->orderByDesc('id')
    //         ->paginate(10, ['*'], 'salary_page');

    //     $agencyJoinRequests = AgencyJoinRequest::where(['agency_id' => $id, 'status' => 0])
    //         ->with('user')
    //         ->whereHas('user')->orderByDesc('id')
    //         ->paginate(10, ['*'], 'join_page');

    //     $giftLog = GiftLog::where('agency_id', $id)->selectRaw("SUM(giftPrice) as exp, receiver_id")
    //         ->with('receiver')->groupBy('receiver_id')->whereHas('receiver')->orderByDesc('exp')->get();
    //     $memberTargets = $agency->mempers()->with(['targets' => function ($query) use ($agencyId, $month, $year) {
    //         $query->where('agency_id', $agencyId)->whereMonth('created_at', $month)->whereYear('created_at', $year);
    //     }])->paginate(10, ['*'], 'target_page');
    //     [$agencyTarget, $rate] =    $this->rateAgency($agencyId, $month, $year);

    //     $stars = $this->giftLogByAgency('receiver', $month, $year, $agencyId, 'receiver_id');
    //     $heroes = $this->giftLogByAgency('sender', $month, $year, $agencyId, 'sender_id');
    //     $data = compact('agency', 'members', 'charges', 'salaries', 'agencyJoinRequests', 'giftLog', 'memberTargets', 'agencyTarget', 'rate', 'stars', 'heroes','tab');

    //     return $content->title(__('agency profile'))
    //         ->view('agency_profile', $data);

    // }

    public function profile($id, Request $request, Content $content)
    {
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month ?? Carbon::now()->month;
        $tab = request('tab') ?? 'members';

        $agency = Cache::remember("agency_{$id}", 600, function () use ($id) {
            return Agency::with(['admins', 'owner:id,name,uuid'])
                ->select('id', 'name', 'app_owner_id', 'phone', 'salary', 'coins', 'img')
                ->findOrFail($id);
        });

        $path = $agency->img;
        $defaultImage = asset("images/icon-agency.jpg");
        $imageUrl = getImagePath($path) ?? $defaultImage;
        if (!isImageExists($imageUrl)) {
            $imageUrl = $defaultImage;
        }
        $agency->display_image = $imageUrl;

        $agencyId = $agency->id;

        $members = $charges = $salaries = $agencyJoinRequests = $giftLog = $memberTargets = $agencyTarget = $rate = $stars = $heroes = null;

        switch ($tab) {
            case 'members':
                $members = Cache::remember("agency_{$id}_members_page_" . request('members_page', 1), 600, function () use ($agency) {
                    return $agency->mempers()
                        ->select('id', 'name', 'uuid', 'total_days', 'monthly_diamond_received', 'agency_id', 'country_id')
                        ->with('country', 'agencyUserJob')
                        ->paginate(10, ['*'], 'members_page');
                });
                break;

            case 'charges':
                $charges = Cache::remember("agency_{$id}_charges_page_" . request('charges_page', 1), 600, function () use ($agency) {
                    return $agency->charges()
                        ->select('id', 'amount', 'created_at')
                        ->latest()
                        ->paginate(10, ['*'], 'charges_page');
                });
                break;

            case 'salary':
                $salaries = Cache::remember("agency_{$id}_salaries_page_" . request('salary_page', 1), 600, function () use ($id) {
                    return AgencySallary::where('agency_id', $id)
                        ->select('id', 'sallary', 'cut_amount', 'month', 'year', 'created_at')
                        ->orderByDesc('id')
                        ->paginate(10, ['*'], 'salary_page');
                });
                break;

            case 'requests':
                $agencyJoinRequests = Cache::remember("agency_{$id}_requests_page_" . request('join_page', 1), 600, function () use ($id) {
                    return AgencyJoinRequest::where(['agency_id' => $id, 'status' => 0])
                        ->with('user')
                        ->whereHas('user')
                        ->orderByDesc('id')
                        ->paginate(10, ['*'], 'join_page');
                });
                break;

            case 'targets':
                $memberTargets = Cache::remember("agency_{$id}_targets_{$month}_{$year}_page_" . request('target_page', 1), 600, function () use ($agency, $agencyId, $month, $year) {
                    return $agency->mempers()->with(['targets' => function ($query) use ($agencyId, $month, $year) {
                        $query->where('agency_id', $agencyId)
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year);
                    }])->paginate(10, ['*'], 'target_page');
                });

                [$agencyTarget, $rate] = Cache::remember("agency_{$id}_rate_{$month}_{$year}", 600, fn() => $this->rateAgency($agencyId, $month, $year));
                $stars = Cache::remember("agency_{$id}_stars_{$month}_{$year}", 600, fn() => $this->giftLogByAgency('receiver', $month, $year, $agencyId, 'receiver_id'));
                $heroes = Cache::remember("agency_{$id}_heroes_{$month}_{$year}", 600, fn() => $this->giftLogByAgency('sender', $month, $year, $agencyId, 'sender_id'));

                break;
        }

        $giftLog = Cache::remember("agency_{$id}_giftlog", 600, function () use ($id) {
            return GiftLog::where('agency_id', $id)
                ->selectRaw("SUM(giftPrice) as exp, receiver_id")
                ->with('receiver')
                ->groupBy('receiver_id')
                ->whereHas('receiver')
                ->orderByDesc('exp')
                ->get();
        });

        $data = compact(
            'agency', 'members', 'charges', 'salaries',
            'agencyJoinRequests', 'giftLog', 'memberTargets',
            'agencyTarget', 'rate', 'stars', 'heroes', 'tab'
        );

        return $content->title(__('agency profile'))
            ->view('agency_profile', $data);
    }


    public function giftLogByAgency($rel, $month, $year, $agencyId, $keywords)
    {
        return  GiftLog::where('agency_id', $agencyId)->whereHas($rel)->with($rel)->whereYear('created_at', $year)->whereMonth('created_at', $month)
            ->selectRaw("sum(giftPrice) as exp, $keywords")
            ->groupBy($keywords)->orderByRaw("exp desc")
            ->get()->reject(function ($q) {
                return $q->exp == 0;
            });
    }

    public function rateAgency($agencyId, $month, $year)
    {
        $agencyTarget = UserSallary::where('user_agency_id', $agencyId)->whereYear('created_at', $year)->whereMonth('created_at', $month)->sum('agency_sallary');
        $minValue = Target::where('usd', '<', $agencyTarget)->orderBy('usd', 'desc')->first();
        $rate = (@$minValue->agency_share / 100) * @$agencyTarget;
        return [$agencyTarget, $rate];
    }

    public function update($id)
    {
        $data = request()->all();
        $agency = Agency::find($id);
        if ($agency && array_key_exists('app_owner_id', $data) && $agency->app_owner_id != $data['app_owner_id']) {
            $oldOwner = $agency->owner;
            if ($oldOwner != null) {
                $oldType = $oldOwner->type_user;
                $oldOwner->agency_id = 0;
                $oldOwner->type_user = 0;
                $oldOwner->is_host = 0;
                $oldOwner->save();
            }


            $newUser = User::find($data['app_owner_id']);
            if ($newUser) {
                $newUser->agency_id = $agency->id;
                // if ($agency->Host_agency == 1 && $agency->Shipping_agency == 1) {
                //     $newUser->type_user = 4;
                // } elseif ($agency->Host_agency == 1 && $agency->Shipping_agency == 0) {
                //     $newUser->type_user = 2;
                // } elseif (
                //     $agency->Host_agency == 0 && $agency->Shipping_agency == 1
                // ) {
                //     $newUser->type_user = 3;
                // }
                $newUser->type_user = 2;
                $newUser->is_host = 1;
                $newUser->save();
            }
        }

        return parent::update($id);
    }

    // public function show($id, Content $content)
    // {

    //     return parent::show($id, $content
    //         ->title(__("agency details"))
    //         ->row(function ($row) use ($id) {
    //             $agency = Agency::find($id);
    //             $row->column(3, new InfoBox(__('Users'), 'users', 'aqua', '?type=users', $agency->users()->count()));
    //             $row->column(3, new InfoBox(__('Balance'), 'dollar', 'green', '?type=balance_details', $agency?->salary));
    //             $row->column(3, new InfoBox(__('Targets'), 'gift', 'yellow', '?type=target', UserTarget::query()->where('agency_id', $id)->where('agency_obtain', '>', 0)->selectRaw('agency_id,add_month,add_year,ROUND(SUM(agency_obtain), 2) as tot')
    //                 ->groupByRaw('agency_id,add_month,add_year')->count()));
    //         }));
    // }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Agency);

        $cacheKey = "agencies_grid_" . md5(json_encode(request()->all()));
        $grid->model()->select('id', 'name', 'app_owner_id', 'phone_code', 'phone', 'salary', 'coins', 'img')
            ->where(function ($query) {
                $query->WhereDoesntHave('additionalInfo')
                    ->orWhereHas('additionalInfo', function ($query) {
                        $query->where('status', 1);
                    });
            })

            ->with(['owner' => function ($query) {
                $query->select('id', 'name', 'uuid');
            }])
            // ->where('Host_agency',  1)
            ->where('bd_id',  Auth::user()->app_id)
            ->orderByDesc('id');

        if (request("active") == true) {
            $grid->model()->whereHas("agencySalaries", function ($q) {
                $q->where('month', now()->month)
                    ->where('year', now()->year);
            });
        }

        $grid->column('name', __('Agency'))
            ->display(function ($name) {
                $cacheKey = "agency_image_{$this->id}";
                $image = Cache::remember($cacheKey, 3600, function () {
                    $path = @$this->img;
                    $defaultImage = asset("images/icon-agency.jpg");
                    $url = getImagePath($path) ?? $defaultImage;

                    if (!isImageExists($url)) {
                        $url = $defaultImage;
                    }

                    return handleShowImageWithTypes($this->id, $url, 40, 40);
                });

                $profileUrl = route('admin.agency.profile', ['id' => $this->id]);

                return "
                    <a href='{$profileUrl}' style='text-decoration: none; color: inherit;'>
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            {$image}
                            <div style='display: flex; flex-direction: column;'>
                                <span style='text-decoration: underline; cursor: pointer;'>{$name}</span>
                                <span style='font-size: smaller;'>ID: {$this->id}</span>
                            </div>
                        </div>
                    </a>
                ";
            });


        $grid->column('owner.name', trans('owner'))->display(function ($name) {
            $uid = @$this->owner->uuid;
            $path = @$this->owner->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = $this->owner ? url("admin/users/{$this->owner->id}") : 0;
            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='font-size: smaller;'>UUID: $uid</span>
                    </div>
                </div>
            ";
        });

        $grid->column('phone', trans('phone'))->display(function ($number) {
            if (!$number) return '-';

            $iconUrl = asset('images/phone.jpg'); // Adjust the path based on your actual file location
            $phoneCode = $this->phone_code;
            // Return an image with a WhatsApp link
            return "<div style='display: flex; align-items: center; '>

             <span>{$phoneCode}{$number}</span>

              <img src='{$iconUrl}' alt='USD' width='20' height='20' style='margin-left:3px; filter: invert(1);'>
        </div>";
        });
        $grid->column('salary', __('Agency wallet'))->display(function ($coin) {
            $icon = asset('images/dollar.jpg'); // تأكد من وجود الصورة في هذا المسار
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });

         $grid->actions(function ($actions) {
            $model = $actions->row;
            // $actions->disableView(); // Disable the "View" action
            $actions->disableDelete();
            if (Admin::user()->can('browse-' . 'delete-agency-Switch') || Admin::user()->can('*')) {

                $actions->add(new DeleteAgencyAction());
            }
            if (Admin::user()->can('browse-' . 'change-users-agency-Switch') || Admin::user()->can('*')) {

                $actions->add(new ChangeUsersAgencyAction($model->id));
            }
        });
        $grid->disableExport();

//        $this->extendGrid($grid);

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();

            $filter->disableIdFilter();
            $filter->equal('id', __('ID'));

            $filter->where(function ($query) {
                $query->whereHas('owner', function ($subQuery) {
                    $subQuery->where('uuid', 'like', "%{$this->input}%");
                });
            }, __('UUID'))->placeholder(__('search for agency or host by UUID'));
        });

        Admin::style("
            .box-footer {
                flex-direction: row-reverse;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                padding: 10px;
            }

            .pagination-info {
                margin: 5px 0;
                white-space: nowrap;
                text-align: right;
                width: auto;
                order: 2;
            }

            .box-footer .pull-right {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 5px;
                margin: 5px 0;
                order: 1;
            }

            .box-footer .pull-right .dropdown {
                margin-left: 5px;
            }

            .pagination > li > a,
            .pagination > li > span {
                min-width: 35px;
                height: 35px;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 5px;
            }

            .pagination {
                margin: 0;
                padding: 0;
                display: flex;
            }

            @media (max-width: 576px) {
                .box-footer {
                    flex-direction: column;
                    align-items: center;
                }

                .pagination-info,
                .box-footer .pull-right {
                    width: 100%;
                    display: flex;
                    justify-content: center;
                    text-align: center;
                }

                .pagination-info {
                    order: 1;
                    margin-bottom: 10px;
                }

                .box-footer .pull-right {
                    order: 2;
                }
            }
        ");

        return $grid;
    }

    protected function balance_details($id)
    {
        $grid = new Grid(new Agency);

        $grid->model()->where('id', $id)->orderByDesc('id');
        $grid->id('ID');
        $grid->column('name', trans('name'));
        $grid->column('salary', trans('salary'));
        $grid->column('img', trans('img'))->image('', 30);
        $grid->disableActions();
        $grid->disableCreateButton();
//        $this->extendGrid($grid);

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
        $show = new Show(Agency::findOrFail($id));

        $show->id('ID');
        $show->field('owner_id', __('owner_id'));
        $show->field('name', __('name'));
        $show->field('notice', __('notice'));
        $show->field('status', __('status'));
        $show->field('phone', __('phone'));
        $show->field('url', __('url'));
        $show->field('img', __('image'))->image('', 200);
        $show->field('contents', __('contents'));
//        $this->extendShow($show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Agency());
        $ops = [];
        foreach ($this->getAgencies() as $user) {
            $ops[$user->id] = $user->name;
        }

        $opsAgencyManger = [];
        foreach (User::where('is_manger', 1)->get() as $user) {
            $opsAgencyManger[$user->id] = $user->uuid . '_' . $user->name;
        }

        $opsAgencyMangerDash = [];
        foreach (DB::table('admin_users')->get() as $user) {
            $opsAgencyMangerDash[$user->id] = $user->name;
        }

        $form->display('ID');
        if (!$form->isEditing()) {
            $form->row(function ($row) {
                $row->width(12)->select('app_owner_id', __('app owner id'))->options(function ($value) {
                    $ops2 = [];
                    foreach (User::Where('id', $value)->get() as $user) {
                        $ops2[$user->id] = $user->uuid . '_' . $user->name;
                    }
                    return $ops2;
                })->ajax('/api/search/users3', 'id', 'name')->rules('required');

                $row->width(12)->hidden('agency_manger_id', __('app manger id'));
                $row->width(12)->text('name', __('agency name'))->rules('required');
                $row->width(12)->switch('status', __('status'));
                $row->width(9)->text('phone', __('agency whatsApp number'))->rules('required')->attribute('id', 'phone-input');

                // $row->width(12)->hidden('Host_agency')->default(1);

                if (!Auth::user()->isRole('Agencies Managers')) {
                    // $row->width(12)->hidden('Shipping_agency')->default(0);
                }
            });
        } else {

            $form->row(function ($row) {
                $row->width(12)->select('app_owner_id', __('app owner id'))->options(function ($value) {
                    $ops2 = [];
                    foreach (User::Where('id', $value)->get() as $user) {
                        $ops2[$user->id] = $user->uuid . '_' . $user->name;
                    }
                    return $ops2;
                })->ajax('/api/search/users3', 'id', 'name')->rules('required');

                // if (request()->route('form')->isEditing()) {
                //     $row->hidden('agency_manger_id', __('app manger id'));
                // }

                $row->width(12)->text('name', __('agency name'))->rules('required');
                $row->width(12)->switch('status', __('status'));
                $row->width(12)->text('phone', __('agency whatsApp number'))->rules('required')->attribute('id', 'phone-input');

                // $row->width(12)->hidden('Host_agency')->default(1);


                if (!Auth::user()->isRole('Agencies Managers')) {
                    // $row->width(12)->switch('Shipping_agency', __('Shipping agency'))->default(0);
                }
            });
        }

        if (Session::has('show_alert')) {
            $form->html('<script>
             $(document).ready(function () {
                 alert("الرجاء اختيار نوع الوكالة اولا");
             });
         </script>');
        }



        Admin::script(<<<'JS'
        function initPhoneInput() {
            const input = document.querySelector("#phone-input");
            if (input && !input.classList.contains('iti-initialized')) {
                const parentDiv = input.parentElement;
                parentDiv.style.position = 'relative';

                const iti = window.intlTelInput(input, {
                    separateDialCode: true,
                    preferredCountries: ["eg"],
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
                });

                document.head.insertAdjacentHTML('beforeend', `
                    <style>
                        .iti { width: 100%;  }
                        .iti__flag-container { z-index: 99; }
                        #phone-input {
                            padding-left: 90px !important;
                            width: 50%;
                        }
                        .fields-group .form-group { overflow: visible; }
                    </style>
                `);

                input.classList.add('iti-initialized');

                const form = input.closest('form');
                if (form && !form.classList.contains('phone-init')) {
                    form.addEventListener('submit', function () {
                        if (iti) {
                            const dialCode = iti.getSelectedCountryData().dialCode;
                            const nationalNumber = input.value.replace(/\s/g, '');

                            const hiddenInput = document.createElement('input');
                            hiddenInput.name = 'phone_code';
                            hiddenInput.value = `+${dialCode}`;
                            form.appendChild(hiddenInput);

                            input.value = nationalNumber;
                        }
                    });
                    form.classList.add('phone-init');
                }
            }
        }

        initPhoneInput();
        $(document).on('pjax:complete', function () {
            setTimeout(initPhoneInput, 100);
        });
    JS);

        $form->saving(function (Form $form) {
            $form->input('bd_id', Auth::user()->app_id);
            $form->model()->bd_id =Auth::user()->app_id;
            $appOwnerId = $form->input('app_owner_id');
            // $Host_agency = $form->input('Host_agency');

            // $Shipping_agency = $form->input('Shipping_agency') ;
            $host = 0;

            $originalOwnerId = $form->model()->getOriginal('app_owner_id');
            $newOwnerId = $form->model()->app_owner_id;
            // Create admin dashboard for agency when accept it
            $modelExists = $form->model()->exists;
            if (!$modelExists)  Common::createUserAdmin($appOwnerId);

            if ($modelExists && $newOwnerId != $originalOwnerId) {
                Common::createUserAdmin($appOwnerId);
                $user = User::find($originalOwnerId);
                $agencyId = $form->model()->id;
                Common::userJoinAgency($originalOwnerId, $newOwnerId, $agencyId);

                Admin::where('username', $user->uuid)->delete();
                $user->update([
                    'type_user' => 0,
                    'agency_id' => 0,
                    'monthly_diamond_received' => 0,
                    'is_host' => 0,
                ]);

                // if (($Host_agency == 'on' && $Shipping_agency == 'off') || ($Host_agency == 0 && $Shipping_agency == 0)) {
                //     User::find($newOwnerId)->update([
                //         'type_user' => 2,
                //         'agency_id' => $form->model()->id,
                //         'monthly_diamond_received' => 0,
                //         'is_host' => 1,
                //     ]);
                // } elseif (($Host_agency === 'on' && $Shipping_agency === 'on') || ($Host_agency == 1 && $Shipping_agency == 1)) {
                //     User::find($newOwnerId)->update([
                //         'type_user' => 4,
                //         'agency_id' => $form->model()->id,
                //         'monthly_diamond_received' => 0,
                //         'is_host' => 1,
                //     ]);
                // } elseif (($Host_agency === 'off' && $Shipping_agency === 'on') || ($Host_agency == 0 && $Shipping_agency == 1)) {
                //     User::find($newOwnerId)->update([
                //         'type_user' => 3,
                //         'agency_id' => $form->model()->id,
                //         'monthly_diamond_received' => 0,
                //         'is_host' => 1,
                //     ]);
                // }
            }



            // if (($Host_agency == 'off' && $Shipping_agency == 'off') || ($Host_agency == 0 && $Shipping_agency == 0)) {

            //     session()->flash('show_alert', 'Your alert message');
            //     return redirect()->back();
            // }




            // if ($Host_agency == 'on' || $Host_agency == 1) {
            //     $host += 2;
            // }

            // if ($Shipping_agency == 'on' || $Shipping_agency == 1) {
            //     $host += 3;
            // }
            if ($host > 3) {
                $host = 4;
            }
            // if ($appOwnerId) {
            $newType = intval($host);
            /*// Reset diamond only when agency created
            if (!$modelExists) $values['monthly_diamond_received'] = 0;*/
            User::where('id', intval($appOwnerId))->update([
                'type_user' => $newType,
                'is_host' => 1,
                'agency_id' => $form->model()->id,]);
            // }



        });

        $form->saved(function (Form $form) {
            $checkAgencyUser = UsersJoinedAgency::where([
                'user_id' => $form->model()->app_owner_id,
                'agency_id' => $form->model()->id,
                'type' => 1,
            ])->where('leave_date', null)->exists();
            if (!$checkAgencyUser) {
                UsersJoinedAgency::create([
                    'user_id' => $form->model()->app_owner_id,
                    'agency_id' => $form->model()->id,
                    'type' => 1,
                    'join_date' => now(),
                ]);
            }
        });
        // Add this to your admin view
        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        return $form;
    }



    public function usersGrid($id)
    {
        $grid = new Grid(new User());
        $grid->model()->where('agency_id', $id);
        $grid->quickSearch();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('uuid', __('uuid'));
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('family_id', __('Family'))->select(Common::by_family_filter());
            });
        });
        $grid->column('id', __('Id'));

        $grid->column('uuid', __('uuid'));

        $grid->column('name', __('Name'));


        $grid->column('salary', __('salary'));

        $grid->column('coins', __('diamonds'));

        $grid->column('target', __('target'))->expand(function ($model) {

            $targets = $model->targets()->orderBy('created_at', 'desc')->get()->map(function ($target) {
                $target = $target->only(
                    [
                        'id',
                        'add_month',
                        'add_year',
                        'target_usd',
                        'target_hours',
                        'target_days',
                        'target_agency_share',
                        'user_diamonds',
                        'user_hours',
                        'user_days',
                        'user_obtain',
                        'agency_obtain',
                        'updated_at'
                    ]
                );


                return $target;
            });

            return new Table(
                [
                    'ID',
                    __('month'),
                    __('year'),
                    __('usd') . ' ' . __('deserved'),
                    __('target hours'),
                    __('target days'),
                    __('agency share') . '(%)',
                    __('user diamonds'),
                    __('user hours'),
                    __('user days'),
                    __('user obtain'),
                    __('agency obtain'),
                    __('at time'),

                ],
                $targets->toArray()
            );
        });


        $grid->disableActions();
        $grid->disableCreateButton();


        return $grid;
    }

    public function targetGrid($id)
    {
        $grid = new Grid(new UserTarget);
        $grid->model()->where('agency_obtain', '>', 0);
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('add_month', __('month'));
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('add_year', __('year'));
            });
        });
        $grid->model()->where('agency_id', $id)
            ->selectRaw('agency_id,add_month as m,add_year as y,ROUND(SUM(agency_obtain), 4) as tot')
            ->groupByRaw('agency_id,m,y');
        $grid->column('agency_id', __('agency id'))->modal('agency info', function ($model) {
            return Common::getAgencyShow($model->agency_id);
        });
        $grid->column('m', __('month'));
        $grid->column('y', __('year'));
        $grid->column('tot', __('agency obtain'));
        $grid->disableActions();
        $grid->disableCreateButton();



        return $grid;
    }

    public function response()
    {
        if (is_null($this->response)) {
            $this->response = new Response();
        }

        if (method_exists($this, 'dialog')) {
            $this->response->swal();
        } else {
            $this->response->toastr();
        }

        return $this->response;
    }




    //////////////////show agency ///////////////////////////////

    // public function show($id, Content $content)
    // {
    //     return $content->row(function ($row) use ($id) {


    //         // Info Boxes
    //         // $row->column(12, function ($column)  {
    //         //     $column->row(view('admin.grid.users.show', compact('user')));


    //         // });

    //         $row->column(12, function ($column) use ($id) {
    //             $tab = new Tab();

    //             Admin::style('
    //                         .nav-tabs-custom {
    //                             background: transparent !important;
    //                             box-shadow: none !important;
    //                             border: none !important;
    //                         }
    //                         .nav-tabs-custom>.nav-tabs {
    //                             background: transparent;
    //                             border: none;
    //                             display: flex;
    //                             padding: 0;
    //                             margin: 0;
    //                             width: 100%;
    //                         }
    //                         .nav-tabs-custom > .nav-tabs > li {
    //                             flex: 1;
    //                             border: none;
    //                             margin: 0;
    //                             padding: 0 2px;
    //                         }
    //                         .nav-tabs-custom > .nav-tabs > li:first-child {
    //                             padding-left: 0;
    //                         }
    //                         .nav-tabs-custom > .nav-tabs > li:last-child {
    //                             padding-right: 0;
    //                         }
    //                         .nav-tabs-custom > .nav-tabs > li > a {
    //                             background: #1e1e1e;
    //                             color: white;
    //                             padding: 8px 24px;
    //                             border-radius: 4px;
    //                             margin: 0;
    //                             border: none;
    //                             font-size: 14px;
    //                             text-align: center;
    //                             width: 100%;
    //                             display: block;
    //                         }
    //                         .nav-tabs-custom > .nav-tabs > li.active > a {
    //                             background: #ff9800;
    //                             color: white;
    //                             border: none;
    //                         }
    //                         .nav-tabs-custom > .nav-tabs > li > a:hover {
    //                             background: #ff9800;
    //                             color: white;
    //                             border: none;
    //                         }
    //                         .nav-tabs-custom>.tab-content {
    //                             background: transparent;
    //                             border: none;
    //                             padding: 10px 0;
    //                         }
    //                        .nav-tabs-custom > .nav-tabs > li.pull-right.header {
    //                             display: none !important;
    //                         }

    //                         .nav-tabs-custom > .nav-tabs > li.pull-right {
    //                             display: none !important;
    //                         }
    //                     ');
    //             $tab->add(__('Agency Join Requests'), $this->joinRequest($id)->render());
    //             $tab->add(__('Assign Admin'), $this->members($id)->render());
    //             $tab->add(__('Stars'), $this->stars($id)->render());
    //             // $tab->add(__('Heroes'), $this->heroes($id)->render());
    //             // $tab->add(__('Target'), $this->targets($id)->render());

    //             $column->append($tab);
    //         });
    //     });
    // }



    public function acceptJoin($id)
    {

        $agencyJoinRequest = AgencyJoinRequest::where('id', $id)->with('user', 'agency')->first();
        if (!$agencyJoinRequest) response()->json([
            'status' => false,
            'message' => __('Not found')
        ], 404);
        $user = $agencyJoinRequest->user;

        if (!$user) return response()->json([
            'status' => false,
            'message' => __(' user Not found')
        ], 404);
        $agency = $agencyJoinRequest->agency;
        if (!$agency) return response()->json([
            'status' => false,
            'message' => __(' agency Not found')
        ], 404);
        if ($user->agency_id) return response()->json([
            'status' => false,
            'message' => __('user joined agency before')
        ], 404);
        // dd($agency,$user->agency_id);
        $agencyJoinRequest->status = 1;
        $agencyJoinRequest->save();

        $user->agency_id = $agencyJoinRequest->agency_id;
        $user->type_user = 1;
        $user->save();
        $checkAgencyUser = UsersJoinedAgency::where('user_id', $user->id)->where('agency_id', $agency->id)->where('leave_date', null)->exists();
        if (!$checkAgencyUser) {
            $joinAgencyData = [
                'user_id' =>  $user->id,
                'agency_id' => $agency->id,
                'type' => 2,
                'join_date' => now(),
            ];
            UsersJoinedAgency::create($joinAgencyData);
        }
        // add vip to user
        UserCommon::userVip($user);
        CustomNotification::acceptAgencyApp($agency, $user);

        return  response()->json([
            'status' => true,
            'message' => __('Joined successfully')
        ]);
    }

    public function adminAgency($id)
    {
        $user = User::Find($id);
        $admin =  AgencyUserJob::where('user_id', $user->id)->where('agency_id', $user->agency_id)->where('type', 'requestManger')->exists();
        if ($admin) return response()->json([
            'status' => false,
            'message' => __('this user admin in  this agency')
        ], 404);
        $data = [
            'agency_id' => $user->agency_id,
            'user_id' => $user->id,
            'type' => "requestManger",
        ];
        AgencyUserJob::create($data);

        return response()->json([
            'status' => true,
            'message' => __('done')
        ]);
    }

    public function rejectJoin($id)
    {
        $agencyJoinRequest = AgencyJoinRequest::where('id', $id)->with('user')->first();
        if (!$agencyJoinRequest) return response()->json([
            'status' => false,
            'message' => __('Not found')
        ], 404);

        $agencyJoinRequest->status = 2;
        $agencyJoinRequest->save();

        return response()->json([
            'status' => true,
            'message' => __('Rejected successfully')
        ]);
    }

    public function members($agencyId)
    {
        $grid = new Grid(new User());

        $grid->model()->where('agency_id', $agencyId)->where('type_user', 1)->whereDoesntHave('agencyUserJob');
        $grid->column('name', __('User'))
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

        $grid->column('whatsapp', __('whatsapp'))->display(function ($number) use ($agencyId) {
            $joinRequest = AgencyJoinRequest::where(['agency_id' => $agencyId, 'user_id' => $this->id])->first();
            if (!$joinRequest) return '-';
            $number = $joinRequest->whatsapp;
            if (!$number) return '-';
            $iconUrl = asset('images/whatsapp.png'); // Adjust the path based on your actual file location

            // Return an image with a WhatsApp link
            return "<div style='display: flex; align-items: center; '>

            <span>{$number} </span>

              <img src='{$iconUrl}' alt='USD' width='20' height='20' style='margin-left:3px; filter: invert(1);'>
        </div>";
        });

        $grid->column('country.name', __('country'))->display(function ($name) {
            if (!$name) return '-';

            $name = app()->getLocale() == 'ar' ? $name ?? @$this->country?->e_name : @$this->country?->e_name ?? $name;
            $path =    @$this->user?->country?->flag ?? '';

            $url = getImagePath($path);

            // Check if the image exists

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            // Return an image with a WhatsApp link
            return "
            <div style='display: flex; flex-direction: column; align-items: start;'>
                <span>{$name}</span>
                <img src='{$image}' alt='USD' width='20' height='20' style='margin-top: 3px; filter: invert(1);'>
            </div>
        ";
        });

        $grid->column('return', __('action'))->display(function () {
            return (new \App\Admin\Actions\AgencyAdmin($this->id))->render();
        });

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableActions();
        return $grid;
    }
}
