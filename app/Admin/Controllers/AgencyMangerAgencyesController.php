<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Traits\AdminTraits\AdminUserTrait;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use App\Models\Agency;
use App\Models\AgencySallary;
use App\Models\UserTarget;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Encore\Admin\Widgets\Table;

class AgencyMangerAgencyesController extends MainController
{
    use AdminUserTrait;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Agency';
    public $permission_name = 'agency-manager';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Agency());
        $this->extendGrid($grid);

        $loggedInUserId = Admin::user()->app_id;
        $grid->model()->where('agency_manger_id', $loggedInUserId);


        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('notice', __('Notice'));
        $grid->column('status', __('Status'));
        $grid->column('phone', __('Phone'));
        $grid->column('img', __('Img'))->image('', 30, 30); // Consider resizing images for better performance
        $grid->column('contents', __('Contents'));
        $grid->column('salary', __('Salary'))->display(function () {
            return AgencySallary::where('agency_id', $this->id)
                                ->where('month', now()->month)
                                ->where('year', now()->year)
                                ->sum('sallary') ?? 0;
        });
        $grid->column('target_usd', __('Target usd'));
        $grid->column('members', __('Members'))->expand(function ($model) {
            $members = $model->mempers()
                             ->with([
                                 'userSallary' => function ($query) {
                                     $query->select('id', 'user_id', 'sallary')
                                           ->where('month', now()->month)
                                           ->where('year', now()->year);
                                 },
                                 'profile' => function ($query) {
                                     $query->select('id', 'user_id', 'avatar');
                                 },
                                 'liveTime'
                             ])
                             ->orderBy('monthly_diamond_received', 'desc')
                             ->take(5)
                             ->get(['id', 'uuid', 'total_days', 'name', 'monthly_diamond_received'])
                             ->map(function ($member) {
                                 $avatar = $member->profile ? getImagePath($member->profile->avatar) : null;
                                 $imageHtml = $avatar ? "<img src='{$avatar}' style='max-width:50px;max-height:50px;' />" : 'No Image';
                                 return [
                                     'id' => $member->id,
                                     'uuid' => $member->uuid,
                                     'name' => $member->name,
                                     'reals_count' => count($member->reals),
                                     'total_days' => $member->total_days,
                                     'total_hours' => $member->liveTime->sum("hours"),
                                     'monthly_diamond_received' => $member->monthly_diamond_received,
                                     'image' => $imageHtml,
                                     'salary' => $member->userSallary->sallary ?? '',
                                 ];
                             });
    
            return new Table(
                ['ID', 'UID', __('Name'), __('Reals Count'), __('Total Days'), __('Total Hours'), __('Monthly DI'), __('Image'), __('Salary')],
                $members->toArray()
            );
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
        $show = new Show(Agency::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('owner_id', __('Owner id'));
        $show->field('name', __('Name'));
        $show->field('notice', __('Notice'));
        $show->field('status', __('Status'));
        $show->field('phone', __('Phone'));
        $show->field('url', __('Url'));
        $show->field('img', __('Img'));
        $show->field('contents', __('Contents'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('old_usd', __('Old usd'));
        $show->field('target_usd', __('Target usd'));
        $show->field('target_token_usd', __('Target token usd'));
        $show->field('app_owner_id', __('App owner id'));
        $show->field('salary', __('Salary'));
        $show->field('Shipping_agency', __('Shipping agency'));
        $show->field('Host_agency', __('Host agency'));
        $show->field('agency_manger_id', __('Agency manger id'));
        $this->extendShow($show);

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    /*protected function form()
    {
        $form = new Form(new Agency());

        $form->number('owner_id', __('Owner id'));
        $form->text('name', __('Name'));
        $form->text('notice', __('Notice'))->default('notice');
        $form->switch('status', __('Status'))->default(1);
        $form->mobile('phone', __('Phone'));
        $form->url('url', __('Url'));
        $form->image('img', __('Img'));
        $form->textarea('contents', __('Contents'));
        $form->decimal('old_usd', __('Old usd'));
        $form->decimal('target_usd', __('Target usd'));
        $form->decimal('target_token_usd', __('Target token usd'));
        $form->number('app_owner_id', __('App owner id'));
        $form->decimal('salary', __('Salary'))->default(0.00);
        $form->number('Shipping_agency', __('Shipping agency'));
        $form->number('Host_agency', __('Host agency'));
        $form->number('agency_manger_id', __('Agency manger id'));

        return $form;
    }*/

    protected function form()
    {
        $ops = [];
        foreach ($this->getAgencies() as $user) {
            $ops[$user->id] = $user->name;
        }

        // $ops2 = [];
        // foreach (User::where('agency_id', 0)->get() as $user) {
        //     $ops2[$user->id] = $user->uuid . '_' . $user->name;
        // }
        $opsAgencyManger = [];
        foreach (User::where('is_manger', 1)->get() as $user) {
            $opsAgencyManger[$user->id] = $user->uuid . '_' . $user->name;
        }

        $opsAgencyMangerDash = [];
        foreach (DB::table('admin_users')->get() as $user) {
            $opsAgencyMangerDash[$user->id] = $user->name;
        }

        $form = new Form(new Agency);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });
        $form->display('ID');
        // $form->select('owner_id', __('owner id'))->options ($ops);
        if (!$form->isEditing()) {
            $form->select('app_owner_id', __('app owner id'))->options('/api/search/users23')->ajax('/api/search/users3', 'id', 'name');
            // ->options(function ($value) {
            //     $ops2 = [];
            //     foreach (User::where(function ($query) {
            //         $query->where('agency_id', 0)->orWhere('agency_id', null);
            //     })->where('type_user', 0)->orWhere('id', $value)->get() as $user) {
            //         $ops2[$user->id] = $user->uuid . '_' . $user->name;
            //     }
            //     return $ops2;
            // });
          //  $form->select('app_owner_id', __('app owner id'))->options($ops2);
            // $form->select('agency_manger_id', __('Agency Manger app Id'))->options($opsAgencyManger)->required();
            // $form->select('agency_dash_manger_id', __('Agency Manger Id'))->options($opsAgencyMangerDash)->required();
        }

        if ($form->isEditing()) {
            $form->hidden('app_owner_id', __('app owner id'));
            //$form->hidden('agency_manger_id', __('app manger id'));
            // $form->hidden('agency_dash_manger_id', __('dash owner id'));
        }



        // $form->text('name', __('name'))->rules('required');
        // $form->text('notice', __('notice'))->rules('required');
        $form->switch('status', __('status'))->default(true);
        // $form->text('phone', __('phone'))->rules('required');
        // $form->url('url', __('url'));
        // $form->image('img', __('img'))->rules('required');
        // $form->textarea('contents', __('contents'));


        // $form->switch('Shipping_agency', trans('Shipping agency'))->default(true);
        // $form->switch('Host_agency', trans('Host agency'))->default(false);

        // $form->switch('at_least_one_selected', __('At least one selected'))->default(false)->readonly();

        // $form->switch('Host_agency', trans('Host agency'))->default(true)->rules(function ($form) {
        //     // $shippingAgency = $form->input('Shipping_agency');
        //     // return [
        //     //     Rule::requiredIf(!$shippingAgency && !$form->input('at_least_one_selected'))
        //     // ];
        // });



        if (Session::has('show_alert')) {
            $form->html('<script>
             $(document).ready(function () {
                 alert("الرجاء اختيار نوع الوكالة اولا");
             });
         </script>');
        }
        $form->saving(function (Form $form) {

            $agencyManagerId = Admin::user()->app_id;
            $form->model()->agency_manger_id = $agencyManagerId;
            // $shippingAgency = $form->input('Shipping_agency');
            // $hostAgency = $form->input('Host_agency');

            // $atLeastOneSelected = $shippingAgency || $hostAgency;

            // $form->input('at_least_one_selected', $atLeastOneSelected);

            $appOwnerId = $form->input('app_owner_id');
            $Host_agency = $form->input('Host_agency');
            $Shipping_agency = $form->input('Shipping_agency');
            $host = 0;

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
          $user =   User::where('id', $appOwnerId)->first();
          $user->type_user = 2;
          $user->save();


        });
        return $form;
    }
}
