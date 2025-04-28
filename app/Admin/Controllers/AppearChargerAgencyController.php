<?php

namespace App\Admin\Controllers;

use App\Admin\Selectable\Users;
use App\Models\Agency;
use App\Models\User;
use App\Models\UsersJoinedAgency;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Admin\Controllers\MainController;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\SalaryTransaction\Entities\ChargeAgency;
use App\Traits\AdminTraits\AdminUserTrait;


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
        return parent::show($id,$content
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
        return parent::edit($id,$content
            ->title(trans('appear-charger-agency'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('appear-charger-agency'))
            ->body($this->form()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Agency());
    
        $grid->model()->with('owner.profile'); // إضافة profile إلى الاستعلام لتحميل بيانات المالك مرة واحدة
    
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
    
        $grid->model()->where('Shipping_agency', 1);
    
        $grid->column('id', __('Id'));
    
        $grid->column('name', __('Agency'))->display(function () {
            $name = $this->name ?? 'Unknown Agency';
            $id = $this->id ?? '';
            $coins = number_format($this->coins ?? 0);
            $path = $this->img ?? '';
            $defaultImage = asset("images/agency-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;
            $icon = asset('images/coin.jpg');
    
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
    
            $image = handleShowImageWithTypes($this->owner->id, $url, 40, 40);
    
            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                        <strong>$name</strong><br>
                        <span style='color: green;'> Coins: $coins</span>
                        <img src='{$icon}' alt='Coin' width='20' height='20'>
                        <br>
                        <span>ID: $id</span>
                    </div>
                </div>
            ";
        });
    
        $grid->column('owner_id', __('Owner'))->display(function () {
            $name = $this->owner->name ?? 'Unknown Owner';
            $uid = $this->owner->uuid ?? 'N/A';
            $phone = $this->owner->phone ?? '-';
            $path = $this->owner->profile->avatar ?? '';
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;
    
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
    
            $image = handleShowImageWithTypes($this->owner->id, $url, 40, 40);
    
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
                return $this->owner->appear_charger_agency ? 1 : 0;
            })
            ->switch(Common::getSwitchStates());
    

        $grid->column('is_frozen', __("frozen"))
            ->display(function () {
                return $this->is_frozen ? 1 : 0;
            })
            ->switch(Common::getSwitchStates());
       
    
        $grid->disableActions();
    
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
            ->ajax('/api/search/users3', 'id', 'name');
    
        $form->hidden('agency_manger_id', __('app manger id'));
    
        $form->text('name', __('name'))->rules('required');
        $form->text('notice', __('notice'))->rules('required');
        $form->switch('status', __('status'));
        $form->text('phone', __('phone'))->rules('required');
        $form->url('url', __('url'));
        $form->textarea('contents', __('contents'));
        $form->hidden('is_frozen', __('is_frozen'));
    
       
    
        // --- عرض تنبيه لو موجود في السيشن ---
        if (Session::has('show_alert')) {
            $form->html('<script>
                $(document).ready(function () {
                    alert("الرجاء اختيار نوع الوكالة اولا");
                });
            </script>');
        }
    
        // --- الأحداث عند الحفظ ---
        $form->saving(function (Form $form) {
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
    
                Common::userJoinAgency($originalOwnerId, $newOwnerId, $agencyId);
    
                Admin::where('username', $user->uuid)->delete();
    
                $user->update([
                    'type_user' => 0,
                    'agency_id' => 0,
                    'monthly_diamond_received' => 0,
                ]);
            }
    
            $newType = 4;
            User::where('id', intval($appOwnerId))->update([
                'type_user' => $newType,
                'monthly_diamond_received' => 0,
                'agency_id' => $form->model()->id,
            ]);
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
    

}
