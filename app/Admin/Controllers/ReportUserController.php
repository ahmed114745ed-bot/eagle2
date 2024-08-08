<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ReportUserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('uuid', __('uuid'));
            });
            $filter->where(function ($query) {
             //   $year = Request::input('year');

            }, __('Year'), 'year')->integer();
           $filter->where(function ($query) {
              //  $month = Request::input('month');

            }, __('Month'), 'month')->integer();

            $filter->column(1 / 2, function ($filter) {
                $filter->equal('agency_id', __('agency'))->select(Common::by_agency_filter_with_owner_id());
            });


        });

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('uuid', __('Uuid'));
        $grid->column('total_days', __('total_days'))->display(function() {
            if (request()->year == null && request()->month == null) {
                return $this->total_days;
            } else {
                // The subquery equivalent in Laravel
                $subQuery = DB::table('live_times')
                              ->select('uid', DB::raw('COUNT(*) AS entry_count'))
                              ->whereMonth('created_at',  request()->month)->whereYear('created_at', request()->year)
                    ->where('uid', $this->id)
                              ->groupBy('uid', DB::raw('DATE(created_at)')) // Group by uid and date
                              ->havingRaw('SUM(hours) > 1')
                ->get(); // Having condition



                return $subQuery->count('entry_count');
            }
        });
        $grid->column( __('reals_count'))->display(function(){

            return request()->year==null &&request()->month ==null ?$this->reals()->count():$this->reals()->whereMonth('created_at',  request()->month)->whereYear('created_at', request()->year)->count();
        });
        $grid->column( __('moment_count'))->display(function(){
            return request()->year==null &&request()->month ==null ?$this->moments()->count(): $this->moments()->whereMonth('created_at',  request()->month)->whereYear('created_at', request()->year)->count();
        });
        $grid->column( __('total_hours'))->display(function(){
            return  request()->year==null &&request()->month ==null ?$this->liveTime()->sum("hours"):$this->liveTime()->whereMonth('created_at',  request()->month)->whereYear('created_at', request()->year)->sum("hours");
        });

        $grid->column( __('Filtered salary'))->display(function(){
            return  request()->year==null &&request()->month ==null ?$this->salary :$this->getSalary(request()->month,request()->year);
        });

        $grid->column( __('Current Salary'))->display(function(){
            return  $this->salary;
        });

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

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('email', __('Email'));
        $show->field('email_verified_at', __('Email verified at'));
        $show->field('password', __('Password'));
        $show->field('remember_token', __('Remember token'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('phone', __('Phone'));
        $show->field('google_id', __('Google id'));
        $show->field('huawei_id', __('Huawei id'));
        $show->field('facebook_id', __('Facebook id'));
        $show->field('di', __('Di'));
        $show->field('coins', __('Coins'));
        $show->field('room_coins', __('Room coins'));
        $show->field('flowers', __('Flowers'));
        $show->field('flowers_value', __('Flowers value'));
        $show->field('gold', __('Gold'));
        $show->field('is_leader', __('Is leader'));
        $show->field('is_sign', __('Is sign'));
        $show->field('isOnline', __('IsOnline'));
        $show->field('status', __('Status'));
        $show->field('is_points_first', __('Is points first'));
        $show->field('locktime', __('Locktime'));
        $show->field('online_time', __('Online time'));
        $show->field('dress_1', __('Dress 1'));
        $show->field('dress_2', __('Dress 2'));
        $show->field('dress_3', __('Dress 3'));
        $show->field('dress_4', __('Dress 4'));
        $show->field('cp_card', __('Cp card'));
        $show->field('keys_num', __('Keys num'));
        $show->field('nickname', __('Nickname'));
        $show->field('idno', __('Idno'));
        $show->field('mykeep', __('Mykeep'));
        $show->field('system', __('System'));
        $show->field('channel', __('Channel'));
        $show->field('img_1', __('Img 1'));
        $show->field('img_2', __('Img 2'));
        $show->field('img_3', __('Img 3'));
        $show->field('points', __('Points'));
        $show->field('login_ip', __('Login ip'));
        $show->field('device_token', __('Device token'));
        $show->field('scale', __('Scale'));
        $show->field('is_idcard', __('Is idcard'));
        $show->field('now_room_uid', __('Now room uid'));
        $show->field('bio', __('Bio'));
        $show->field('agency_id', __('Agency id'));
        $show->field('family_id', __('Family id'));
        $show->field('is_host', __('Is host'));
        $show->field('whatsapp', __('Whatsapp'));
        $show->field('old_usd', __('Old usd'));
        $show->field('target_usd', __('Target usd'));
        $show->field('target_token_usd', __('Target token usd'));
        $show->field('uuid', __('Uuid'));
        $show->field('is_gold_id', __('Is gold id'));
        $show->field('chat_id', __('Chat id'));
        $show->field('notification_id', __('Notification id'));
        $show->field('vip', __('Vip'));
        $show->field('sub_sender_level', __('Sub sender level'));
        $show->field('sub_receiver_level', __('Sub receiver level'));
        $show->field('sub_sender_num', __('Sub sender num'));
        $show->field('sub_receiver_num', __('Sub receiver num'));
        $show->field('salary', __('Salary'));
        $show->field('monthly_diamond_send', __('Monthly diamond send'));
        $show->field('total_diamond_send', __('Total diamond send'));
        $show->field('monthly_diamond_received', __('Monthly diamond received'));
        $show->field('total_diamond_received', __('Total diamond received'));
        $show->field('sender_level', __('Sender level'));
        $show->field('received_level', __('Received level'));
        $show->field('type_user', __('Type user'));
        $show->field('is_manger', __('Is manger'));
        $show->field('dashboard_manager_id', __('Dashboard manager id'));
        $show->field('apple_id', __('Apple id'));
        $show->field('today_days', __('Today days'));
        $show->field('monthly_days', __('Monthly days'));
        $show->field('total_days', __('Total days'));
        $show->field('lang', __('Lang'));
        $show->field('lan', __('Lan'));
        $show->field('unread_count_message', __('Unread count message'));
        $show->field('country_id', __('Country id'));
//        $show->field('image_color_id', __('Image color id'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('current_app_version', __('Current app version'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        $form->text('name', __('Name'));
        $form->email('email', __('Email'));
        $form->datetime('email_verified_at', __('Email verified at'))->default(date('Y-m-d H:i:s'));
        $form->password('password', __('Password'));
        $form->text('remember_token', __('Remember token'));
        $form->mobile('phone', __('Phone'));
        $form->text('google_id', __('Google id'));
        $form->textarea('huawei_id', __('Huawei id'));
        $form->text('facebook_id', __('Facebook id'));
        $form->decimal('di', __('Di'));
        $form->decimal('coins', __('Coins'));
        $form->decimal('room_coins', __('Room coins'));
        $form->decimal('flowers', __('Flowers'));
        $form->decimal('flowers_value', __('Flowers value'));
        $form->decimal('gold', __('Gold'));
        $form->switch('is_leader', __('Is leader'));
        $form->switch('is_sign', __('Is sign'));
        $form->switch('isOnline', __('IsOnline'));
        $form->switch('status', __('Status'))->default(1);
        $form->switch('is_points_first', __('Is points first'));
        $form->datetime('locktime', __('Locktime'))->default(date('Y-m-d H:i:s'));
        $form->number('online_time', __('Online time'));
        $form->number('dress_1', __('Dress 1'));
        $form->number('dress_2', __('Dress 2'));
        $form->number('dress_3', __('Dress 3'));
        $form->number('dress_4', __('Dress 4'));
        $form->number('cp_card', __('Cp card'));
        $form->number('keys_num', __('Keys num'));
        $form->text('nickname', __('Nickname'));
        $form->text('idno', __('Idno'));
        $form->text('mykeep', __('Mykeep'));
        $form->text('system', __('System'))->default('normal');
        $form->text('channel', __('Channel'))->default('normal');
        $form->text('img_1', __('Img 1'));
        $form->text('img_2', __('Img 2'));
        $form->text('img_3', __('Img 3'));
        $form->number('points', __('Points'));
        $form->text('login_ip', __('Login ip'));
        $form->text('device_token', __('Device token'));
        $form->number('scale', __('Scale'));
        $form->switch('is_idcard', __('Is idcard'));
        $form->number('now_room_uid', __('Now room uid'));
        $form->textarea('bio', __('Bio'));
        $form->number('agency_id', __('Agency id'));
        $form->number('family_id', __('Family id'));
        $form->switch('is_host', __('Is host'));
        $form->text('whatsapp', __('Whatsapp'));
        $form->decimal('old_usd', __('Old usd'));
        $form->decimal('target_usd', __('Target usd'));
        $form->decimal('target_token_usd', __('Target token usd'));
        $form->text('uuid', __('Uuid'));
        $form->switch('is_gold_id', __('Is gold id'));
        $form->text('chat_id', __('Chat id'));
        $form->text('notification_id', __('Notification id'));
        $form->number('vip', __('Vip'));
        $form->number('sub_sender_level', __('Sub sender level'));
        $form->number('sub_receiver_level', __('Sub receiver level'));
        $form->number('sub_sender_num', __('Sub sender num'));
        $form->number('sub_receiver_num', __('Sub receiver num'));
        $form->decimal('salary', __('Salary'))->default(0.00);
        $form->number('monthly_diamond_send', __('Monthly diamond send'));
        $form->number('total_diamond_send', __('Total diamond send'));
        $form->number('monthly_diamond_received', __('Monthly diamond received'));
        $form->number('total_diamond_received', __('Total diamond received'));
        $form->number('sender_level', __('Sender level'));
        $form->number('received_level', __('Received level'));
        $form->number('type_user', __('Type user'));
        $form->switch('is_manger', __('Is manger'));
        $form->number('dashboard_manager_id', __('Dashboard manager id'));
        $form->text('apple_id', __('Apple id'));
        $form->number('today_days', __('Today days'));
        $form->number('monthly_days', __('Monthly days'));
        $form->number('total_days', __('Total days'));
        $form->text('lang', __('Lang'))->default('en');
        $form->text('lan', __('Lan'))->default('en');
        $form->number('unread_count_message', __('Unread count message'));
        $form->number('country_id', __('Country id'));
//        $form->number('image_color_id', __('Image color id'));
        $form->number('current_app_version', __('Current app version'));

        return $form;
    }
}
