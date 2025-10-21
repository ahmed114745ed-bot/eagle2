<?php

namespace App\SuperAdmin\Controllers;

use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;
use App\Selectables\Agencies;
use App\Selectables\Families;
use App\Jobs\OfficialMessageJob;
use Encore\Admin\Layout\Content;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Selectables\ShippingAgencies;
use App\Models\SuperAdminNotification;
use Encore\Admin\Controllers\HasResourceActions;
use App\Admin\Controllers\OfficialMessageController;

class OfficialMessengerSuperAdminController extends OfficialMessageController
{
    use HasResourceActions;
    public $permission_name = 'official-messages';



    public function index(Content $content)
    {
        return $content
            ->title(trans('Official messages'))
            ->body($this->grid());
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('official-messages'))
            ->body($this->form());
    }

    protected function grid()
    {
        $grid = parent::grid();
        $grid->model()->where('admin_id', Auth::id())->where('type', 2)->orderByDesc('id');


        return $grid;
    }


    protected function form()
    {
        $form = parent::form();
        $this->selectFeature($form);
        $form->hidden('admin_id', __('type'))->default(Auth::user()->id);

        return $form;
    }


    protected function selectFeature(Form $form)
    {
        $form->select('feature', trans('feature'))->options([
            'agency'   => __('agency'),
            'family' => __('family'),
            'users'   => __('users'),
            'bds'  => __('BDs'),
            'shipping_agency'  => __('shipping agency')
        ])->when('agency', function (Form $form) {
            $form->select('sub_feature', __('type'))->options([
                'your_country' => __('All Agencies in your Country'),
                'ids'   => __('Specific Agency by ID'),
            ])->when('ids', function (Form $form) {
                $form->belongsToMany('feature_ids', Agencies::class, trans('agencies'));
                $form->select('member_title', trans('member'))->options([
                    'owner'   => __('owner'),
                    'admin' => __('admins'),
                    'members'   => __('members'),
                ]);
            });
        })->when('family', function (Form $form) {
            $form->belongsToMany('feature_ids', Families::class, trans('families'));
            $form->select('member_title', trans('member'))->options([
                'owner'   => __('owner'),
                'admin' => __('admins'),
                'members'   => __('members'),
            ]);
        })->when('users', function (Form $form) {
            $form->select('sub_feature', __('type'))->options([
                'your_country' => __('Users in your Country'),
                'logout'   => __('Logged Out Users'),
            ])->when('country', function (Form $form) {
                $form->select('feature_ids', __('country'))
                    ->options('/api/search/countries')
                    ->ajax('/api/search/countries', 'id', 'name');
            });
        })->when('bds', function (Form $form) {
            $form->select('sub_feature', __('type'))->options([
                'your_country' => __('bds in your Country'),
            ]);
        })->when('shipping_agency', function (Form $form) {
            $form->select('sub_feature', __('type'))->options([
                'ids'   => __('Specific shipping Agency by ID'),
                'your_country' => __('shipping agency in your Country'),
            ])->when('ids', function (Form $form) {
                $form->belongsToMany('feature_ids', ShippingAgencies::class, trans('agencies'));
                $form->select('member_title', trans('member'))->options([
                    'owner'   => __('owner'),
                ]);
            });;
        });

        $form->saved(function (Form $form) {
            $model = $form->model();
            dispatch(new OfficialMessageJob($model, request()->all(), Auth::user()))->onQueue('official-message');
        });
    }
}
