<?php

namespace App\Admin\Controllers;

use App\Models\Gift;
use App\Models\Room;
use App\Models\User;
use App\Models\Ware;
use App\Models\Agency;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\UserTarget;
use App\Models\AgencySallary;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
use Illuminate\Validation\Rule;
use Encore\Admin\Layout\Content;
use App\Models\AgencyJoinRequest;
use App\Models\UsersJoinedAgency;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Actions\Response;
use Illuminate\Support\Facades\DB;
use App\Services\AppFeatureService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\Admin\Actions\DeleteAgencyAction;
use App\Traits\AdminTraits\AdminUserTrait;
use App\Admin\Widgets\Table as TableWidget;
use App\Admin\Actions\ChangeUsersAgencyAction;
use Encore\Admin\Controllers\HasResourceActions;
use TijsVerkoyen\CssToInlineStyles\Css\Rule\Rule as RuleRule;

class AgencyController extends MainController
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
        return $content
            ->title(__('Agencies'))
            ->description(__('List of Agencies'))
            ->row(function ($row) {

                // ---- LEFT: Settings panel ----
                $row->column(3, view('agency.settings'));

                // ---- RIGHT: The agencies Grid ----
                $row->column(9, $this->grid());
            });
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

    public function profile($id, Content $content)
    {
        $cacheKey = "agency_profile_{$id}";
        $data = Cache::remember($cacheKey, 3600, function () use ($id) {
            $agency = Agency::with([
                'charges' => function ($query) {
                    $query->select('id', 'agency_id', 'amount', 'created_at')
                        ->latest()
                        ->take(10);
                },
                'mempers' => function ($query) {
                    $query->select('id', 'agency_id', 'name', 'created_at')
                        ->latest()
                        ->take(10);
                },
                'owner' => function ($query) {
                    $query->select('id', 'name', 'uuid');
                }
            ])->select('id', 'name', 'app_owner_id', 'phone', 'salary', 'coins')
                ->findOrFail($id);

            $members = $agency->mempers()
                ->select('id', 'name', 'uuid', 'total_days', 'monthly_diamond_received')
                ->paginate(10, ['*'], 'members_page');

            $charges = $agency->charges()
                ->select('id', 'amount', 'created_at')
                ->paginate(10, ['*'], 'charges_page');

            $salaries = AgencySallary::where('agency_id', $id)
                ->select('id', 'sallary', 'cut_amount', 'month', 'year', 'created_at')
                ->orderByDesc('id')
                ->paginate(10, ['*'], 'salary_page');

            return compact('agency', 'members', 'charges', 'salaries');
        });

        return $content->title(__('agency profile'))
            ->view('agency_profile', $data);
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
                if ($agency->Host_agency == 1 && $agency->Shipping_agency == 1) {
                    $newUser->type_user = 4;
                } elseif ($agency->Host_agency == 1 && $agency->Shipping_agency == 0) {
                    $newUser->type_user = 2;
                } elseif (
                    $agency->Host_agency == 0 && $agency->Shipping_agency == 1
                ) {
                    $newUser->type_user = 3;
                }

                $newUser->is_host = 1;
                $newUser->save();
            }
        }

        return parent::update($id);
    }

    public function show($id, Content $content)
    {

        return parent::show($id, $content
            ->title(__("agency details"))
            ->row(function ($row) use ($id) {
                $agency = Agency::find($id);
                $row->column(3, new InfoBox(__('Users'), 'users', 'aqua', '?type=users', $agency->users()->count()));
                $row->column(3, new InfoBox(__('Balance'), 'dollar', 'green', '?type=balance_details', $agency?->salary));
                $row->column(3, new InfoBox(__('Targets'), 'gift', 'yellow', '?type=target', UserTarget::query()->where('agency_id', $id)->where('agency_obtain', '>', 0)->selectRaw('agency_id,add_month,add_year,ROUND(SUM(agency_obtain), 2) as tot')
                    ->groupByRaw('agency_id,add_month,add_year')->count()));
            }));
    }

    public function destroy($id)
    {
        AgencyJoinRequest::query()->where('agency_id', $id)->delete();
        User::query()->where('agency_id', $id)->update([
            'agency_id' => 0
        ]);
        return parent::destroy($id); // T-17 Change the autogenerated stub
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Agency);

        $cacheKey = "agencies_grid_" . md5(json_encode(request()->all()));
        $grid->model()->select('id', 'name', 'app_owner_id', 'phone', 'salary', 'coins', 'img')
            ->where(function ($query) {
                $query->WhereDoesntHave('additionalInfo')
                    ->orWhereHas('additionalInfo', function ($query) {
                        $query->where('status', 1);
                    });
            })
            ->with(['owner' => function ($query) {
                $query->select('id', 'name', 'uuid');
            }])
            ->orderByDesc('id');

        if (request("active") == true) {
            $grid->model()->whereHas("agencySalaries", function ($q) {
                $q->where('month', now()->month)
                    ->where('year', now()->year);
            });
        }

        $grid->id(__('ID'));
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

                return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                <span>$name</span>
            </div>
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

            // Return an image with a WhatsApp link
            return "<div style='display: flex; align-items: center; '>

            <span>{$number} </span>

              <img src='{$iconUrl}' alt='USD' width='20' height='20' style='margin-left:3px; filter: invert(1);'>
        </div>";
        });
        $grid->column('coins', __('coins'))->display(function ($coin) {
            $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });
        $grid->column('salary', __('salary'))->display(function ($coin) {
            $icon = asset('images/dollar.jpg'); // تأكد من وجود الصورة في هذا المسار
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });
        $grid->column('target', trans('target'))->display(function () {
            $target = $this->getTargetsAttribute(); // استخدم الشهر والسنة كمعاملات إذا لزم الأمر

            return $target ? "<span class='label-success' " . 'style="width: 8px;height: 8px;padding: 0;border-radius: 50%;display: inline-block;"' .
                "></span>" : "";
        });
        $grid->column('members', __('members'))->expand(function ($model) {
            $mempers = $model->mempers()
                ->orderBy('monthly_diamond_received', 'desc')
                ->with(['userSallary' => function ($query) {
                    $query->select('id', 'user_id', 'sallary')->where('month', now()->month)->where('year', now()->year);
                }, 'profile' => function ($query) {
                    $query->select('id', 'user_id', 'avatar'); // assuming 'avatar' is the column name for the image in 'profile'
                }])
                ->get(['id', 'uuid', 'total_days', 'name', 'monthly_diamond_received',]) // selecting specific fields from `mempers`
                ->map(function ($memper) {
                    $memper->image = @$memper->profile?->avatar ?? null;
                    $imageHtml = $memper->profile && $memper->profile->avatar
                        ? '<img src="' . getImagePath($memper->image) . '" style="max-width:50px;max-height:50px;" />' // تأكد من تعديل المسار حسب مكان تخزين الصور
                        : 'No Image';
                    $salary = $memper->userSallary->sallary ?? 0;
                    return [
                        'id' => $memper->id ?? 0,
                        'uuid' => $memper->uuid ?? 0,
                        'name' => $memper->name ?? '',
                        'reals_count' => $memper->reals()->count() ?? 0,
                        'total_days' => $memper->total_days ?? 0,
                        'total_hours' => $memper->liveTime->sum("hours"),
                        'monthly_diamond_received' => $memper->monthly_diamond_received ?? 0,
                        'image' => $imageHtml,
                        'salary' => $salary ?? 0,

                    ];
                });

            // Using the mapped data to create a new table
            return new TableWidget(
                [
                    'ID',
                    'UID',
                    __('name'),
                    __('reals_count'),
                    __('total_days'),
                    __('total_hours'),
                    __('Monthly DI'),
                    __('img'),
                    __('salary'),
                ],
                $mempers->toArray() // Convert the collection to an array for the table
            );
        });

        $grid->actions(function ($actions) {
            $model = $actions->row;
            $actions->disableView(); // Disable the "View" action
            $actions->disableDelete();
            $actions->add(new DeleteAgencyAction());
            $actions->add(new ChangeUsersAgencyAction($model->id));
        });
        $grid->disableExport();

        $this->extendGrid($grid);


        $grid->column('agency profile', __('agency profile'))->display(function () {
            $url = route('admin.agency.profile', ['id' => $this->id]);
            $name = __('agency profile');
            return "<a href='{$url}' class='btn btn-primary btn-sm'>{$name}</a>";
        });

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
        $this->extendGrid($grid);

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
        $form = new Form(new Agency);
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

            $form->select('app_owner_id', __('app owner id'))->options(function ($value) {
                $ops2 = [];
                foreach (User::Where('id', $value)->get() as $user) {
                    $ops2[$user->id] = $user->uuid . '_' . $user->name;
                }
                return $ops2;
            })->ajax('/api/search/users3', 'id', 'name');
            if ($form->isEditing()) {
                $form->hidden('agency_manger_id', __('app manger id'));
            }

            $form->text('name', __('name'))->rules('required');
            // $form->password('password', __('Password'))->attribute('onfocus', "this.removeAttribute('readonly');")->attribute('readonly');
            $form->text('notice', __('notice'))->rules('required');
            $form->switch('status', __('status'));
            $form->text('phone', __('phone'))->rules('required');
            $form->url('url', __('url'));
           // $form->image('img', __('img'))->rules('required');
            $form->textarea('contents', __('contents'));
            $form->switch('Host_agency', trans('Host agency'))->default(true);
            if (!Auth::user()->isRole('Agencies Managers')) {
                $form->switch('Shipping_agency', trans('Shipping agency'))->default(false);
            }
        } else {

            $form->select('app_owner_id', __('app owner id'))->options(function ($value) {
                $ops2 = [];
                foreach (User::Where('id', $value)->get() as $user) {
                    $ops2[$user->id] = $user->uuid . '_' . $user->name;
                }
                return $ops2;
            })->ajax('/api/search/users3', 'id', 'name');

            if ($form->isEditing()) {
                $form->hidden('agency_manger_id', __('app manger id'));
            }

            $form->text('name', __('name'))->rules('required');
            $form->text('notice', __('notice'))->rules('required');
            $form->switch('status', __('status'));
            $form->text('phone', __('phone'))->rules('required');
            $form->url('url', __('url'));
            $form->image('img', __('img'))->rules('required');
            $form->textarea('contents', __('contents'));
            $form->switch('Host_agency', trans('Host agency'))->default(true);
            if (!Auth::user()->isRole('Agencies Managers')) {
                $form->switch('Shipping_agency', trans('Shipping agency'))->default(false);
            }
        }

        if (Session::has('show_alert')) {
            $form->html('<script>
             $(document).ready(function () {
                 alert("الرجاء اختيار نوع الوكالة اولا");
             });
         </script>');
        }

        $form->saving(function (Form $form) {

            $appOwnerId = $form->input('app_owner_id');
            $Host_agency = $form->input('Host_agency');
            $Shipping_agency = $form->input('Shipping_agency');
            $host = 0;

            $originalOwnerId = $form->model()->getOriginal('app_owner_id');
            $newOwnerId = $form->model()->app_owner_id;
            if (!$form->model()->exists)  Common::createUserAdmin($appOwnerId);
            if ($form->model()->exists && $newOwnerId != $originalOwnerId) {
                Common::createUserAdmin($appOwnerId);
                $user = User::find($originalOwnerId);
                $agencyId = $form->model()->id;
                Common::userJoinAgency($originalOwnerId, $newOwnerId, $agencyId);

                Admin::where('username', $user->uuid)->delete();
                $user->update([
                    'type_user' => 0,
                    'agency_id' => 0,
                    'monthly_diamond_received' => 0,
                ]);

                if ($Host_agency === 'on' && $Shipping_agency === 'off') {
                    User::find($newOwnerId)->update([
                        'type_user' => 2,
                        'agency_id' => $form->model()->id,
                        'monthly_diamond_received' => 0,
                    ]);
                } elseif ($Host_agency === 'on' && $Shipping_agency === 'on') {
                    User::find($newOwnerId)->update([
                        'type_user' => 4,
                        'agency_id' => $form->model()->id,
                        'monthly_diamond_received' => 0,
                    ]);
                } elseif ($Host_agency === 'off' && $Shipping_agency === 'on') {
                    User::find($newOwnerId)->update([
                        'type_user' => 3,
                        'agency_id' => $form->model()->id,
                        'monthly_diamond_received' => 0,
                    ]);
                }
            }



            if ($Host_agency === 'off' && $Shipping_agency === 'off') {

                session()->flash('show_alert', 'Your alert message');
                return redirect()->back();
            }



            if ($Host_agency === 'on') {
                $host += 2;
            }

            if ($Shipping_agency === 'on') {
                $host += 3;
            }
            if ($host > 3) {
                $host = 4;
            }
            // if ($appOwnerId) {
            $newType = intval($host);
            User::where('id', intval($appOwnerId))->update(['type_user' => $newType, 'monthly_diamond_received' => 0, 'agency_id' => $form->model()->id,]);
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
}
