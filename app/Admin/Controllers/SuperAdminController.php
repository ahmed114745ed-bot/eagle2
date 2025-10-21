<?php

namespace App\Admin\Controllers;

use App\Models\Agency;
use App\Models\Bd;
use App\Models\User;
use App\Models\Charge;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Country;
use App\Models\SuperAdmin;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Carbon;
use Encore\Admin\Facades\Admin;
use App\Models\SuperAdminReward;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use App\Enums\Charges\UserTypeEnum;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use App\Admin\Actions\DeleteSuperAdminAction;
use Modules\Milestones\Helpers\MilestoneHelper;

class SuperAdminController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Super Admin';
    public $permission_name = 'superadmin';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(__($this->title))
            ->row(function (Row $row) {
                $row->column(12, $this->grid2());
            })
            ->row(function ($row) {
                $row->column(12, $this->grid());
            }));
    }

    protected function grid2()
    {
        return (new Box(
            title: __('admin.description'),
            content: view('admin.grid.superadmin.description'),
        ));
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
            ->title(trans('Super Admin'))
            ->body($this->profile($id)));
    }

    public function showPreview(Content $content)
    {
        return $content
            ->title(trans('Super Admin'))
            ->body($this->profilePreview());
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
            ->title(trans('Super Admin'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Super Admin'))
            ->body($this->form()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SuperAdmin());
        $grid->model()->with(['appUser.packs'])
            ->orderByDesc('id');

        $grid->filter(function ($filter) {
            $filter->like('appUser.uuid', __('App User UUID'));
            $filter->like('appUser.name', __('User Name'));
        });
        $grid->column('id', __('Id'));
        $grid->column('username', __('Super Admin'))->display(function ($name) {
            if (request()->filled('_export_')) {
                return $name;
            }

            $id = $this->id ?? '-';
            $name = $this->username ?? 'غير معروف';
            $path = $this->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = url("admin/superadmin-users/{$this->id}");

            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='font-size: smaller;'>ID: $id</span>
                    </div>
                </div>
            ";
        });
        $grid->column('default', __('default_superadmin_status'))->display(function () {
            if (request()->filled('_export_')) {
                return $this->default;
            }

            if ($this->default == 1) {
                return <<<HTML
                    <span style="display: flex; align-items: center;">
                        <span style="
                            font-size: smaller;
                            background: red;
                            display: inline-block;
                            border-radius: 50%;
                            width: 10px;
                            height: 10px;
                            margin-left: 5px;
                        " title=""></span>
                    </span>
                HTML;
            } else {
                return '<span style="color: #999;"></span>';
            }
        });

        $grid->column('appUser.name', __('user'))->display(function ($name) {
            $user = $this->appUser;
            if (request()->filled('_export_')) {
                return $name;
            }
            if (!$user) return "<span style='color: red;'>غير مرتبط</span>";

            $uid = $user->uuid ?? 'غير معروف';
            $path = $user->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = url("admin/users/{$user->id}");

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

        $grid->column('country.name', __('country'));

        //        $grid->column('agencies_count', __('Agencies Count'))->display(function () {
        //            return $this->agencies_count;
        //        });
        //
        //
        //        $grid->column('total_salary', __('total proft'))->display(function () {
        //            return truncateAndTrim($this->total_salary, 2);
        //        });
        //
        //        $grid->column('current_balance', __('current_balance'))->display(function () {
        //            $total = floatval($this->total_salary);
        //            $cut   = floatval($this->total_cut);
        //            return truncateAndTrim($total - $cut, 2);
        //        });
        //
        //        $grid->column('total_cut', __('Cut amount'))->display(function () {
        //            return truncateAndTrim($this->total_cut, 2);
        //        });

        //        if (Admin::user()->can('stop-salary-switch-' . $this->permission_name) || Admin::user()->can('*')) {
        //            $col = $grid->column('transfer_salary', __("transfer_salary"))
        //                ->display(function () {
        //                    return $this->transfer_salary ? 1 : 0;
        //                });
        //
        //            if (! request()->filled('_export_')) {
        //                $col->switch(Common::getSwitchStates());
        //            }
        //        }

        $grid->column('created_at', __('Created at'))->display(function ($date) {
            $carbonDate = Carbon::parse($date);
            $locale = App::getLocale();
            $carbonDate->locale($locale);
            return $carbonDate->translatedFormat('d F Y H:i'); // مثال: 22 مايو 2025 14:30
        });

        $permission = $this->permission_name;
        $grid->actions(function ($actions) use ($permission) {
            $actions->disableDelete();
            if (Admin::user()->can('delete-switch-' . $permission) || Admin::user()->can('*')) {
                $actions->add(new DeleteSuperAdminAction());
            }
        });

        if (Admin::user()->can('choose-switch-' . $permission) || Admin::user()->can('*')) {
            $grid->tools(function (Grid\Tools $tools) {});
        }

        $grid->disableRowSelector();

        $this->extendGrid($grid);
        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SuperAdmin());
        $this->disableFormTools($form);

        $form->text('name', __('name'));
        $form->text('username', __('username'))->creationRules(['required', "unique:admin_users,username,{{id}}"])->updateRules(['required', "unique:admin_users,username,{{id}}"]);;
        $form->password('password', __('Password'))->rules('required');
        $form->image('avatar', __('img'));

        //        $form->hidden('transfer_salary', __('transfer_salary'));

        $form->select('country_id', trans('country'))->options(function ($value) {
            $ops       = [null => __('no country')];
            $countries = Country::doesntHave('superAdmin')->orWhere('id', $value)->get();
            foreach ($countries as $country) {
                $ops[$country->id] = App::isLocale('en') ? $country->e_name : $country->name;
            }
            return $ops;
        })->required();



        if ($form->isEditing()) {
            $form->select('app_id', __('validation.select_user'))->options(function ($value) {
                $ops2 = [];
                foreach (User::Where('id', $value)->get() as $user) {
                    $ops2[$user->id] = $user->uuid . '_' . $user->name;
                }
                return $ops2;
            })->ajax('/api/search/users-superadmin', 'id', 'name')->help('لا يمكن التعديل إلا إذا لم يكن هناك مستخدم مرتبط، أو كان المستخدم مرتبطًا لكن تم حذفه.')->rules('required');
        } else {
            $form->select('app_id', __('validation.select_user'))->options(function ($value) {
                $ops2 = [];
                foreach (User::Where('id', $value)->get() as $user) {
                    $ops2[$user->id] = $user->uuid . '_' . $user->name;
                }
                return $ops2;
            })->ajax('/api/search/users-superadmin', 'id', 'name')->rules('required');

//            $form->switch('default', __('set_superadmin_as_default'))
//                ->help(__('make_super_admin_default'));
        }
        $this->addPhoneFields($form);

        $form->hidden('type', __('Type'))->value('superadmin');
        //        $form->hidden('transfer_salary', __('transfer_salary'));

        $form->saving(function (Form $form) {
            $isEditing = $form->isEditing();
            $superAdmin = SuperAdmin::where('phone_code', request('phone_code'))->where('phone', request('phone'));
            if ($isEditing) $superAdmin->where('id', '!=', $form->model()->id);
            $exists = $superAdmin->exists();

            if ($exists) {
                $error = new \Illuminate\Support\MessageBag([
                    'title' => 'Error',
                    'message' => trans('you used this phone before'),
                ]);
                return back()->with(compact('error'))->withInput();
            }



            if ($isEditing) {
                $originalAppId = $form->model()->getOriginal('app_id');
                $newAppId = $form->input('app_id');
                if ($originalAppId !=  $newAppId) {
                    $OldUserAppId = User::find($originalAppId);
                    if ($OldUserAppId) {
                        $OldUserAppId->is_super_admin = 0;
                        $OldUserAppId->save();
                        MilestoneHelper::removeReward($OldUserAppId, 'super-admin');
                    }

                    $newUserAppId = User::find($newAppId);
                    $newUserAppId->is_super_admin = 1;
                    $newUserAppId->save();
                    $form->app_id = $newAppId;
                }
            }

            if ($form->password && $form->model()->password != $form->password) {
                $form->password   = Hash::make($form->password);
            }
        });

        $form->saved(function (Form $form) {
            $superAdmin = $form->model();
            $userId = $form->model()->id;
            $userAppId = $form->model()->app_id;

            $userApp = User::find($userAppId);
            if (isset($userApp)) {
                $userApp->is_super_admin = 1;
                $userApp->save();
                MilestoneHelper::grantMilestoneToUser($userApp->id, 'super-admin');
            }

            $role = DB::table('admin_roles')->where('slug', 'super-admin')->first();

            if ($role && $userId) {
                $exists = DB::table('admin_role_users')
                    ->where('user_id', $userId)
                    ->where('role_id', $role->id)
                    ->exists();

                if (!$exists) {
                    DB::table('admin_role_users')->insert([
                        'user_id' => $userId,
                        'role_id' => $role->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            $isEditing = $form->isEditing();
            if (!$isEditing) {
                $countryName = Country::whereId($superAdmin->country_id)->first()->e_name;

                $newBdId = DB::table('admin_users')->insertGetId([
                    'parent_id' => $superAdmin->id,
                    'username' => 'bd' . $countryName . 'default',
                    'name' => 'bd' . $countryName . 'default',
                    'password' => Hash::make('bd' . $countryName . 'default'),
                    'default' => 1,
                    'country_id' => $superAdmin->country_id,
                    'type' => 'bd',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Bd::where('country_id', $superAdmin->country_id)->update(['parent_id' => $superAdmin->id]);

                Agency::where('country_id', $superAdmin->country_id)->where(function ($q){
                    $q->whereDoesntHave('bd')
                        ->orWhereHas('bd', function($q){
                            $q->where([
                                'default' => 1,
                                'country_id' => 0
                            ]);
                        });
                })->update(['bd_id' => $newBdId]);
            }
        });

        return $form;
    }

    protected function addPhoneFields(Form $form)
    {

        $form->text('phone', __('whatsApp number'))
            ->rules('required')
            ->attribute('id', 'phone-input')
            ->attribute('maxlength', 12)
            ->default(function ($form) {
                if ($form->model()->phone && $form->model()->phone_code) {
                    return $form->model()->phone;
                }
                return null;
            });

        $form->hidden('phone_code')->default(function ($form) {
            return $form->model()->phone_code ?? '';
        });


        Admin::script($this->phoneJs());
    }


    protected function phoneJs()
    {
        return <<<JS
            function initPhoneInputById(inputId, hiddenId) {
                const input = document.querySelector(inputId);
                const hidden = document.querySelector(hiddenId);
                if (!input || input.classList.contains('iti-initialized')) return;

                const iti = window.intlTelInput(input, {separateDialCode: true, preferredCountries: ["eg"], utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"});
                input.classList.add('iti-initialized');

                if (input.value && hidden && hidden.value) iti.setNumber(hidden.value + input.value);

                input.addEventListener("countrychange", function () { if(hidden) hidden.value = "+" + iti.getSelectedCountryData().dialCode; });
                const form = input.closest('form');
                if(form && !form.classList.contains('phone-init')){
                    form.addEventListener('submit', function(){
                        // if(hidden) hidden.value = "+" + iti.getSelectedCountryData().dialCode;
                        // input.value = iti.getNumber(intlTelInputUtils.numberFormat.E164);
                                hidden.value = "+" + iti.getSelectedCountryData().dialCode;

                    });
                    form.classList.add('phone-init');
        }
    }

    function initAllPhones() { initPhoneInputById("#phone-input", "input[name='phone_code']"); }
    initAllPhones();
    $(document).on('pjax:complete', function () { setTimeout(initAllPhones, 100); });
    JS;
    }

    public function profile($id)
    {
        $tab = request()->query('tab', 'agencies');

        $superAdmin = SuperAdmin::select(['id', 'name', 'app_id', 'avatar', 'username', 'di', 'default', 'country_id'])->with('country')->findOrFail($id);

        $defaultImage = asset("images/icon-agency.jpg");
        $imageUrl = getImagePath($superAdmin->avatar);
        if (!isImageExists($imageUrl)) {
            $imageUrl = $defaultImage;
        }
        $superAdmin->display_image = $imageUrl;

        $agencies = $transactions = $target_history = null;
        $rewards = null;
        $totals = Charge::selectRaw("
            SUM(CASE WHEN user_type = ? AND user_id = ? THEN amount ELSE 0 END) as total_charges,
            SUM(CASE WHEN charger_type = ? AND charger_id = ? THEN amount ELSE 0 END) as total_spent
        ", [
            UserTypeEnum::SUPER_ADMIN,
            $superAdmin->id,
            UserTypeEnum::SUPER_ADMIN,
            $superAdmin->id
        ])
            ->first();

        $totalCharges = $totals->total_charges;
        $totalSpent   = $totals->total_spent;
        $types = ['vip', 'badge', 'ware'];
        $type = request()->get('type', 'vip');
        switch ($tab) {
            case 'agencies':
                $agencies = $superAdmin->agencies()->paginate(10, ['*'], 'agencies_page');
                break;
            case 'rewards':


                $rewards = SuperAdminReward::where('super_admin_id', $superAdmin->id)->where('type', $type)->with('ware', 'vip', 'badge')->paginate(10, ['*'], 'reward_page');
                break;
        }

        return view('superadmin.super_admin_profile', compact('superAdmin', 'agencies', 'totalCharges', 'totalSpent', 'type', 'types', 'rewards'));
    }

    public function profilePreview()
    {
        if (!session('preview_superadmin') || !session('country_id')) {
            abort(404, __('not found'));
        }

        $tab = request()->query('tab', 'agencies');
        $countryID = session('country_id');

        $superAdmin = SuperAdmin::select(['id', 'name', 'app_id', 'avatar', 'username', 'default', 'country_id'])
            ->with('country')->where('country_id', $countryID)->firstOrFail();

        $defaultImage = asset("images/icon-agency.jpg");
        $imageUrl = getImagePath($superAdmin->avatar);
        if (!isImageExists($imageUrl)) {
            $imageUrl = $defaultImage;
        }
        $superAdmin->display_image = $imageUrl;

        $agencies = $transactions = $target_history = null;

        $totals = Charge::selectRaw("
            SUM(CASE WHEN user_type = ? AND user_id = ? THEN amount ELSE 0 END) as total_charges,
            SUM(CASE WHEN charger_type = ? AND charger_id = ? THEN amount ELSE 0 END) as total_spent
        ", [
            UserTypeEnum::SUPER_ADMIN,
            $superAdmin->id,
            UserTypeEnum::SUPER_ADMIN,
            $superAdmin->id
        ])
            ->first();

        $totalCharges = $totals->total_charges;
        $totalSpent   = $totals->total_spent;

        switch ($tab) {
            case 'agencies':
                $agencies = $superAdmin->agencies()->paginate(10, ['*'], 'agencies_page');
                break;
        }

        return view('superadmin.super_admin_profile', compact('superAdmin', 'agencies', 'totalCharges', 'totalSpent'));
    }

    protected function detail($id)
    {
        $show = new Show(SuperAdmin::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('Username'));
        $show->field('avatar', __('Avatar'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('app_id', __('App id'));

        $this->extendShow($show);

        return $show;
    }

    //    public function sync($days = 0)
    //    {
    //        $days = request()->query('days', 0);
    //        $bds = DB::table('admin_users')
    //            ->where('type', 'bd')
    //            ->where('app_id', '!=', 0)
    //            ->get();
    //
    //        $updated = 0;
    //
    //        foreach ($bds as $bd) {
    //            $query = DB::table('agencies')
    //                ->where('bd_id', $bd->app_id);
    //
    //            if ($days > 0) {
    //                $query->where('created_at', '<=', now()->subDays($days));
    //            }
    //
    //            $affected = $query->update(['bd_id' => $bd->id]);
    //            $updated += $affected;
    //        }
    //
    //        return response()->json([
    //            'status' => 'success',
    //            'message' => $updated
    //        ]);
    //    }

}
