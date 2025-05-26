<?php

namespace App\Admin\Controllers;

use App\Models\Charge;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter\Where;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Models\AgencySallary;
use App\Models\ShippingAgency;
use App\Admin\Selectable\Users;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Models\AgencyJoinRequest;
use App\Models\UsersJoinedAgency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use App\Admin\Controllers\MainController;
use App\Traits\AdminTraits\AdminUserTrait;
use App\Admin\Actions\DeleteShippingAgencyAction;
use Modules\SalaryTransaction\Entities\ChargeAgency;

class AppearChargerAgencyController extends MainController
{
    /**
     *
     * Title for current resource.
     *
     * @var string
     */
    use AdminUserTrait;

    public $permission_name = 'appear-charger-agency';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('appear-charger-agency'))
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
            ->title(trans('appear-charger-agency'))
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
            ->title(trans('appear-charger-agency'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('appear-charger-agency'))
            ->body($this->form()));
    }

    public function profile($id, Request $request, Content $content)
    {
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month ?? Carbon::now()->month;
        $tab = request('tab') ?? 'members';


        $agency = Cache::remember("agency_{$id}", 600, function () use ($id) {
            return ShippingAgency::with(['admins', 'owner:id,name,uuid'])
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
            'agency',
            'members',
            'charges',
            'salaries',
            'agencyJoinRequests',
            'giftLog',
            'memberTargets',
            'agencyTarget',
            'rate',
            'stars',
            'heroes',
            'tab'
        );

        return $content->title(__('agency profile'))
            ->view('agency_profile', $data);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ShippingAgency());

        // إضافة profile إلى الاستعلام لتحميل بيانات المالك مرة واحدة
        $grid->model()->with('owner.profile');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column('1/2', function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('owner', function ($q) use ($input) {
                        $q->where('name', 'like', "%$input%")
                            ->orWhere('uuid', 'like', "%$input%")
                            ->orWhere('phone', 'like', "%$input%");
                    });
                }, __('User'))->placeholder(__('Search by name , UUID , phone'));
            });
        });


        $grid->column('id', __('Id'));

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

                $profileUrl = route('admin.shipping.agency.profile', ['id' => $this->id]);

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

        $grid->column('owner_id', __('Owner'))->display(function () {
            // التأكد من أن الـ owner موجود قبل الوصول إلى خصائصه
            $name = $this->owner ? $this->owner->name ?? 'Unknown Owner' : 'Unknown Owner';
            $uid = $this->owner ? $this->owner->uuid ?? 'N/A' : 'N/A';
            $phone = $this->owner ? $this->owner->phone ?? '-' : '-';
            $path = $this->owner && $this->owner->profile ? $this->owner->profile->avatar : '';
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            // التأكد من أن الـ owner موجود قبل استدعاء دالة `handleShowImageWithTypes`
            $image = $this->owner ? handleShowImageWithTypes($this->owner->id, $url, 40, 40) : '';

            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                        <strong>$name</strong><br>
                        <span style=' font-size: smaller;'>UID: $uid</span><br>
                        <span style=' font-size: smaller;'>Phone: $phone</span>
                    </div>
                </div>
            ";
        });

        $grid->column('charge_agency', __("Charge-agency"))
            ->display(function () {
                return ChargeAgency::where('agency_id', $this->id)->exists() ? 1 : 0;
            })
            ->switch(Common::getSwitchStates());

        $grid->column('appear_charger_agency', __("Appear charger agency"))
            ->display(function () {
                return $this->owner && $this->owner->appear_charger_agency ? 1 : 0;
            })
            ->switch(Common::getSwitchStates());

        $grid->column('is_frozen', __("frozen"))
            ->display(function () {
                return $this->is_frozen ? 1 : 0;
            })
            ->switch(Common::getSwitchStates());

        $grid->actions(function ($actions) {
            $actions->disableView();
            if (Admin::user()->can('browse-' . 'delete-shipping-agency-Switch') || Admin::user()->can('*')) {

                $actions->add(new DeleteShippingAgencyAction());
            }
            $actions->disableDelete();
        });

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

        // $show->field('id', __('Id'));
        // $show->field('name', __('Name'));
        // $show->field('phone', __('Phone'));
        // $show->field('uuid', __('Uuid'));
        // $show->field('appear_charger_agency', __('Appear charger agency'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ShippingAgency());

        $ops = [];
        foreach ($this->getAgencies() as $user) {
            $ops[$user->id] = $user->name;
        }

        // --- الحقول المشتركة ---
        $form->display('ID');

        $form->select('app_owner_id', __('app owner id'))
            ->options(function ($value) {
                $ops2 = [];
                foreach (User::where('id', $value)->get() as $user) {
                    $ops2[$user->id] = $user->uuid . '_' . $user->name;
                }
                return $ops2;
            })
            ->ajax('/api/search/users5', 'id', 'name')->rules('required');

        $form->hidden('agency_manger_id', __('app manger id'));

        $form->text('name', __('name'))->rules('required');
        $form->switch('status', __('status'));

        $form->text('phone', __('agency whatsApp number'))->attribute('id', 'phone-input');
        $form->url('url', __('url'));
        $form->hidden('is_frozen', __('is_frozen'))->default(0);
        $form->hidden('type', __('type'))->default(2);



        // --- عرض تنبيه لو موجود في السيشن ---
        if (Session::has('show_alert')) {
            $form->html('<script>
                $(document).ready(function () {
                    alert("الرجاء اختيار نوع الوكالة اولا");
                });
            </script>');
        }
        // $form->hidden('Shipping_agency')->default(1);

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
        // $form->hidden('Shipping_agency')->default(1);

        // --- الأحداث عند الحفظ ---
        $form->saving(function (Form $form) {

            $form->phone_code = request('phone_code');
            $appOwnerId = $form->input('app_owner_id');
            $originalOwnerId = $form->model()->getOriginal('app_owner_id');
            $newOwnerId = $form->model()->app_owner_id;

            if (!$form->model()->exists) {
                Common::createUserAdmin($appOwnerId);
            }

            if ($form->model()->exists && $newOwnerId != $originalOwnerId) {
                Common::createUserAdmin($appOwnerId);

                $user = User::find($originalOwnerId);
                $agencyId = $form->model()->id;


                Admin::where('username', $user->uuid)->delete();
            }
        });

        $form->saved(function (Form $form) {
            $checkAgencyUser = UsersJoinedAgency::where([
                'user_id' => $form->model()->app_owner_id,
                'agency_id' => $form->model()->id,
                'type' => 1,
            ])->whereNull('leave_date')->exists();

            if (!$checkAgencyUser) {
                UsersJoinedAgency::create([
                    'user_id' => $form->model()->app_owner_id,
                    'agency_id' => $form->model()->id,
                    'type' => 1,
                    'join_date' => now(),
                ]);
            }
        });

        return $form;
    }



    public function shippingProfile($id, Request $request, Content $content)
    {
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month ?? Carbon::now()->month;
        $tab = request('tab') ?? 'members';
        $filter_by = request('filter_by') ?? null;


        $agency = Cache::remember("agency_{$id}", 600, function () use ($id) {
            return ShippingAgency::with(['admins', 'owner:id,name,uuid'])
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

        $resived = $charges =  null;

        $filterBy = $request->filter_by ?? null;
        $filterId = $request->filter_id  ?? null;
        $charges = $resiveds = null;
        switch ($tab) {
            case 'charge':
                $charges = Charge::where('user_charger_type', 'agency')
                    ->where('charger_id', $agencyId);
                $relations = [];
                $charges->when($filter_by === 'user', function ($query) use (&$relations) {
                    $query->whereNotNull('user_id')
                        ->whereNull('agency_id');
                    $relations[] = 'resiver';
                });
                $charges->when($filter_by === 'agency', function ($query) use (&$relations) {
                    $query->whereNull('user_id')
                        ->whereNotNull('agency_id');
                    $relations[] = 'agency';
                });
                if (!empty($relations)) {
                    $charges->with($relations);
                }
                $charges = $charges->latest()
                    ->paginate(10, ['*'], 'charges_page');
                break;

            case 'resived':
                $resiveds = Charge::with(['sender'])
                    ->where('agency_id', $agencyId)
                    ->latest()
                    ->paginate(10, ['*'], 'resived_page');
                break;
        }

        // dd($charges);
        $data = compact(
            'agency',
            'resiveds',
            'charges',
            'tab'
        );

        return $content->title(__('agency profile'))
            ->view('shippingAgencyProfile', $data);
    }
}
