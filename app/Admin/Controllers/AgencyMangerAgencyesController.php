<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\AgencySallary;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AgencyMangerAgencyesController extends MainController
{

    public $permission_name = 'agency-manager';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('agency'))
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
            ->title(trans('agency'))
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
            ->title(trans('agency'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('agency'))
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
        $this->extendGrid($grid);

        $loggedInUserId = Admin::user()->app_id;

        $grid->model()->where('agency_manger_id', $loggedInUserId)
            ->addSelect(['current_salary_sum' => AgencySallary::selectRaw('COALESCE(SUM(sallary), 0)')
                ->whereColumn('agency_id', 'agencies.id')
                ->where('month', now()->month)
                ->where('year', now()->year)
            ]);

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('notice', __('notice'));
        $grid->column('status', __('status'));
        $grid->column('phone', __('Phone'));
        $grid->column('img', __('Img'))->image('', 30, 30); // Consider resizing images for better performance
        $grid->column('contents', __('contents'));
        $grid->column('current_salary_sum', __('Salary'))->display(function () {
            return $this->current_salary_sum ?? 0;
        });
        $grid->column('target_usd', __('target usd'));
        $grid->column('members', __('members'))->expand(function ($model) {
            $members = $model->mempers()
                ->select('users.*') // ensure it's still hydrating User models
                ->leftJoin('monthly_diamond_receives as mdr', function ($join) {
                    $join->on('users.id', '=', 'mdr.user_id')
                        ->where('mdr.month', now()->month)
                        ->where('mdr.year', now()->year);
                })
                ->with([
                    'userSallary' => function ($query) {
                        $query->select('id', 'user_id', 'sallary')
                            ->where('month', now()->month)
                            ->where('year', now()->year);
                    },
                    'profile:id,user_id,avatar',
                    'liveTime',
                ])
                ->withCount('reals')
                ->orderByDesc('mdr.monthly_diamond_received')
                ->take(5)
                ->get([
                    'users.id',
                    'users.uuid',
                    'users.total_days',
                    'users.name',
                    DB::raw('COALESCE(mdr.monthly_diamond_received, 0) as monthly_diamond_received')
                ])
                ->map(function ($member) {
                    $avatar = $member->profile ? getImagePath($member->profile?->avatar) : null;
                    $imageHtml = $avatar ? "<img src='{$avatar}' style='max-width:50px;max-height:50px;' />" : 'No Image';

                    return [
                        'id' => $member->id ?? 0,
                        'uuid' => $member->uuid ?? 0,
                        'name' => $member->name ?? '',
                        'reals_count' => $member->reals_count ?? 0,
                        'total_days' => $member->total_days ?? 0,
                        'total_hours' => $member->liveTime->sum("hours") ?? 0,
                        'monthly_diamond_received' => $member->monthly_diamond_received ?? 0,
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
        // $show->field('Shipping_agency', __('Shipping agency'));
        // $show->field('Host_agency', __('Host agency'));
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
            // $Host_agency = $form->input('Host_agency');
            // $Shipping_agency = $form->input('Shipping_agency');
            $host = 0;

            // if ($Host_agency === 'off' && $Shipping_agency === 'off') {
            //     session()->flash('show_alert', 'Your alert message');
            //     return redirect()->back();
            // }
            // if ($Host_agency === 'on') {
            //     $host += 2;
            // }

            // if ($Shipping_agency === 'on') {
            //     $host += 3;
            // }
            // if ($host > 3) {
            //     $host = 4;
            // }
            // if ($appOwnerId) {
            // $newType = intval($host);
            $user =   User::where('id', $appOwnerId)->first();
            $user->type_user = 2;
            $user->save();
        });
        return $form;
    }
}
