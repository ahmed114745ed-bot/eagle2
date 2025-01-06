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
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
// use Encore\Admin\Admin;
use Illuminate\Validation\Rule;
use Encore\Admin\Layout\Content;
use App\Models\AgencyJoinRequest;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Actions\Response;
use Illuminate\Support\Facades\DB;
use App\Services\AppFeatureService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Traits\AdminTraits\AdminUserTrait;
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
        return parent::index($content
            ->title(trans('Agencies'))
            ->body($this->grid()));
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
                $row->column(3, new InfoBox(__('Users'), 'users', 'aqua', '?type=users', User::query()->where('agency_id', $id)->count()));
                $row->column(3, new InfoBox(__('Balance'), 'dollar', 'green', '?type=balance_details', $agency?->salary));
                $row->column(3, new InfoBox(__('Targets'), 'gift', 'yellow', '?type=target', UserTarget::query()->where('agency_id', $id)->where('agency_obtain', '>', 0)->selectRaw('agency_id,add_month,add_year,ROUND(SUM(agency_obtain), 2) as tot')
                    ->groupByRaw('agency_id,add_month,add_year')->get()->count()));
                //                $row->column(3, new InfoBox(__('Store'), 'shopping-cart', 'red', route ('admin.wares'), Ware::query ()->count ()));
            })
            ->row(function ($row) use ($id) {

                //     if (request ('type') == 'users'){
                //         $row->column(12,__ ('Users'));
                //         $row->column(12, $this->usersGrid($id));
                //     }elseif(request ('type') == 'target'){
                //         $row->column(12,__ ('target'));
                //         $row->column(12, $this->targetGrid($id));
                //     }elseif (request ('type') == 'balance_details'){
                //         $row->column(12,__ ('balance details'));
                //         $row->column(12, $this->balance_details($id));
                //     }else{
                //         $row->column(12,__ ('target'));
                //         $row->column(12, $this->targetGrid($id));
                //     }

                // })

                // ;

            }));
    }

    public function destroy($id)
    {
        AgencyJoinRequest::query()->where('agency_id', $id)->delete();
        User::query()->where('agency_id', $id)->update(['agency_id' => 0]);
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


        $grid->model()->where(function ($query) {
            $query->WhereDoesntHave('additionalInfo')->orWhereHas(
                'additionalInfo',
                function ($query) {
                    $query->where('status', 1);
                }
            );
        })->orderByDesc('id');
        if (request("active") == true) {
            $grid->model()->whereHas("agencySalaries", function ($q) {
                $q->where('month', now()->month)->where('year', now()->year);
            });
        }
        $grid->id(__('ID'));
        $grid->column('name', trans('name'));
        $grid->column('notice', trans('notice'));
        $grid->column('owner.name', trans('owner'));
        // $grid->column('status',trans ('status'))->switch(Common::getSwitchStates ());
        // $grid->column('Shipping_agency',trans ('Shipping agency'))->switch(Common::getSwitchStates ());
        // $grid->column('Host_agency',trans ('Host agency'))->switch(Common::getSwitchStates ());
        $grid->column('phone', trans('phone'));
        $grid->column('target', trans('target'))->display(function () {
            $target = $this->getTargetAttribute(); // استخدم الشهر والسنة كمعاملات إذا لزم الأمر
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
                    $salary = $memper->userSallary->sallary ?? '';
                    return [
                        'id' => $memper->id ?? 0,
                        'uuid' => $memper->uuid ?? 0,
                        'name' => $memper->name ?? '',
                        'reals_count' => count($memper->reals) ?? 0,
                        'total_days' => $memper->total_days ?? 0,
                        'total_hours' => $memper->liveTime->sum("hours"),
                        'monthly_diamond_received' => $memper->monthly_diamond_received ?? 0,
                        'image' => $imageHtml,
                        'salary' => $salary ?? '',

                    ];
                });

            // Using the mapped data to create a new table
            return new Table(
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

        $grid->column('img', trans('img'))->image('', 30);
        $grid->actions(function ($actions) {
            $model = $actions->row;
            $actions->disableView(); // Disable the "View" action
            $actions->add(new ChangeUsersAgencyAction($model->id));
        });
        $grid->disableExport();

        $this->extendGrid($grid);



        return $grid;
    }

    protected function balance_details($id)
    {
        $grid = new Grid(new Agency);

        $grid->model()->where('id', $id)->orderByDesc('id');

        $grid->id('ID');
        // $grid->column('owner_id',trans ('owner id'))->modal ('owner info',function ($model){
        //     return Common::getAdminShow ($model->owner_id);
        // });
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

        $form->select('app_owner_id', __('app owner id'))->options(function ($value) {
            $ops2 = [];
            foreach (User::Where('id', $value)->get() as $user) {
                $ops2[$user->id] = $user->uuid . '_' . $user->name;
            }
            return $ops2;
        })->ajax('/api/search/users3', 'id', 'name');
        if (!$form->isEditing()) {
            //$form->select('agency_manger_id', __('Agency Manger app Id'))->options($opsAgencyManger)->required();
            // $form->select('agency_dash_manger_id', __('Agency Manger Id'))->options($opsAgencyMangerDash)->required();
        }

        if ($form->isEditing()) {
            //            $form->hidden('app_owner_id', __('app owner id'));
            $form->hidden('agency_manger_id', __('app manger id'));
            // $form->hidden('agency_dash_manger_id', __('dash owner id'));
        }

        $form->text('name', __('name'))->rules('required');
        // $form->password('password', __('Password'))->attribute('onfocus', "this.removeAttribute('readonly');")->attribute('readonly');
        $form->text('notice', __('notice'))->rules('required');
        $form->switch('status', __('status'));
        $form->text('phone', __('phone'))->rules('required');
        $form->url('url', __('url'));
        $form->image('img', __('img'))->rules('required');
        $form->textarea('contents', __('contents'));


        // $form->switch('Shipping_agency', trans('Shipping agency'))->default(true);
        // $form->switch('Host_agency', trans('Host agency'))->default(false);

        // $form->switch('at_least_one_selected', __('At least one selected'))->default(false)->readonly();

        $form->switch('Host_agency', trans('Host agency'))->default(true)->rules(function ($form) {
            // $shippingAgency = $form->input('Shipping_agency');
            // return [
            //     Rule::requiredIf(!$shippingAgency && !$form->input('at_least_one_selected'))
            // ];
        });
        if (!Auth::user()->isRole('Agencies Managers')) {
            $form->switch('Shipping_agency', trans('Shipping agency'))->default(false)->rules(function ($form) {
                // $hostAgency = $form->input('Host_agency');
                // return [
                //     Rule::requiredIf(!$hostAgency && !$form->input('at_least_one_selected'))
                // ];
            });
        }

        if (Session::has('show_alert')) {
            $form->html('<script>
             $(document).ready(function () {
                 alert("الرجاء اختيار نوع الوكالة اولا");
             });
         </script>');
        }

        $form->saving(function (Form $form) {


            // $shippingAgency = $form->input('Shipping_agency');
            // $hostAgency = $form->input('Host_agency');

            // $atLeastOneSelected = $shippingAgency || $hostAgency;

            // $form->input('at_least_one_selected', $atLeastOneSelected);

            $appOwnerId = $form->input('app_owner_id');
            $Host_agency = $form->input('Host_agency');
            $Shipping_agency = $form->input('Shipping_agency');
            $host = 0;

            $originalOwnerId = $form->model()->getOriginal('app_owner_id');
            $newOwnerId = $form->model()->app_owner_id;
            if (!$form->model()->exists)  Common::createUserAdmin($appOwnerId);
            if ($form->model()->exists && $newOwnerId != $originalOwnerId) {
                $user = User::find($originalOwnerId);
                Admin::where('username', $user->uuid)->delete();
                $user->update([
                    'type_user' => 0,
                    'agency_id' => 0,
                ]);

                if ($Host_agency === 'on' && $Shipping_agency === 'off') {
                    User::find($newOwnerId)->update([
                        'type_user' => 2,
                        'agency_id' => $form->model()->id,
                    ]);
                } elseif ($Host_agency === 'on' && $Shipping_agency === 'on') {
                    User::find($newOwnerId)->update([
                        'type_user' => 4,
                        'agency_id' => $form->model()->id,
                    ]);
                } elseif ($Host_agency === 'off' && $Shipping_agency === 'on') {
                    User::find($newOwnerId)->update([
                        'type_user' => 3,
                        'agency_id' => $form->model()->id,
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
            User::where('id', intval($appOwnerId))->update(['type_user' => $newType, 'agency_id' => $form->model()->id,]);
            // }



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
