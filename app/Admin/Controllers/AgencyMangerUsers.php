<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;


use App\Admin\Actions\DeletePackAction;
use App\Admin\Actions\DeleteUserVipAction;
use App\Admin\Actions\EditPackExpireAction;
use App\Admin\Actions\KickOfAgencyAction;
use App\Admin\Actions\KickOfFamilyAction;
use App\Admin\Forms\ProfileForm;
use App\Helpers\Common;
use App\Models\Agency;
use App\Models\Charge;
use App\Models\Country;
use App\Models\Pack;
use App\Models\UserTarget;
use App\Models\UserVip;
use App\Models\Ware;
use App\Traits\AdminTraits\AdminControllersTrait;
use Carbon\Carbon;
// use Encore\Admin\Actions\Response;
use Illuminate\Http\Response;

use Encore\Admin\Auth\Permission;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

class AgencyMangerUsers extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User';
    public $permission_name = 'users';
    public $hiddenColumns = [
        'is_host',
        'status',
        'is_gold_id',
        'id',
        'email',
        'phone',
        'di',
        'gold',
        'coins',
        'actions'
    ];
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $grid = new Grid(new User());
        $grid->model ()->ofAgency();
        $grid->quickSearch ();
        $grid->filter (function (Grid\Filter $filter){
            $filter->expand ();
            $filter->column(1/2, function ($filter) {
                $filter->equal('uuid',__ ('uuid'));
                // $filter->equal('is_host',__('is host'))->select([0=>'normal',1=>'host']);
            });
            // $filter->column(1/2, function ($filter) {
            //     $filter->equal('agency_id',__('agency'))->select(Common::by_agency_filter ());
            //     $filter->equal('family_id',__('Family'))->select(Common::by_family_filter ());
            // });
        });
        $grid->column('id', __('Id'));
        $grid->column ('uuid',__('uuid'));

//        $grid->column ('is_gold_id',__ ('use Gold id'))->switch (Common::getSwitchStates ());
        $grid->column('name', __('Name'));
        $grid->column('nickname', __('NickName'));
        $grid->column('profile.avatar', __('image'))->image ('',50);
        $grid->column('profile.image_id', __('image Id'))->image ('',50);
        $grid->column('phone', __ ('Phone'));
//        $grid->column('di', __('coins'));
//        $grid->column('gold', __('silver coins'));
//        $grid->column('coins', __('diamonds'));
//        $grid->column('status', __('block status'))->switch (Common::getSwitchStates2 () );
        // $grid->column ('agency_id',__ ('agency id'))->modal ('agency info',function ($model){
        //     if ($model->agency_id){
        //         $a = Agency::query ()->find ($model->agency_id);
        //         if (!$a){
        //             $model->agency_id = 0;
        //             $model->save();
        //             return null;
        //         }
        //         return Common::getAgencyShow (@$model->agency_id);
        //     }
        //     return null;
        // });

        // $grid->column ('target',__ ('target'))->expand(function ($model) {

        //     $targets = $model->targets()->orderBy('created_at','desc')->get()->map(function ($target) {
        //         $target = $target->only(
        //             [
        //                 'id',
        //                 'add_month',
        //                 'add_year',
        //                 'target_usd',
        //                 'target_hours',
        //                 'target_days',
        //                 'target_agency_share',
        //                 'user_diamonds',
        //                 'user_hours',
        //                 'user_days',
        //                 'user_obtain',
        //                 'agency_obtain',
        //                 'updated_at'
        //             ]
        //         );


        //         return $target;
        //     });

        //     return new Table(
        //         [
        //             'ID',
        //             __('month'),
        //             __('year'),
        //             __('usd').' '.__ ('deserved'),
        //             __ ('target hours'),
        //             __ ('target days'),
        //             __ ('agency share').'(%)',
        //             __ ('user diamonds'),
        //             __ ('user hours'),
        //             __ ('user days'),
        //             __('user obtain'),
        //             __('agency obtain'),
        //             __('at time'),

        //         ]
        //         , $targets->toArray());
        // });


       $grid->disableExport();

        $this->extendGrid ($grid);

        $grid->actions (function ($actions){
            $actions->add(new KickOfAgencyAction());
            $actions->add(new KickOfFamilyAction());
        });



        $grid->model()->where('is_manger', 1);
            $grid->actions(function ($actions){
               $actions->disableEdit();
               $actions->disableView();
            });
            $grid->disableCreateButton();
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
        $show->field('special_id', __('Special id'));
        $show->field('name', __('Name'));
        $show->field('email', __('Email'));
        $show->field('email_verified_at', __('Email verified at'));
        $show->field('password', __('Password'));
        $show->field('remember_token', __('Remember token'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('phone', __('Phone'));
        $show->field('google_id', __('Google id'));
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
        $show->field('dress_5', __('Dress 5'));
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
        $show->field('country_id', __('Country id'));
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
        $show->field('today_days', __('Today days'));
        $show->field('monthly_days', __('Monthly days'));
        $show->field('total_days', __('Total days'));
        $show->field('credential', __('Credential'));
        $show->field('type_user', __('Type user'));
        $show->field('apple_id', __('Apple id'));
        $show->field('is_manger', __('Is manger'));

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

        // $form->number('special_id', __('Special id'));
        // $form->text('name', __('Name'));
        // $form->email('email', __('Email'));
        // $form->datetime('email_verified_at', __('Email verified at'))->default(date('Y-m-d H:i:s'));
        // $form->password('password', __('Password'));
        // $form->text('remember_token', __('Remember token'));
        // $form->mobile('phone', __('Phone'));
        // $form->text('google_id', __('Google id'));
        // $form->text('facebook_id', __('Facebook id'));
        // $form->decimal('di', __('Di'));
        // $form->decimal('coins', __('Coins'));
        // $form->decimal('room_coins', __('Room coins'));
        // $form->decimal('flowers', __('Flowers'));
        // $form->decimal('flowers_value', __('Flowers value'));
        // $form->decimal('gold', __('Gold'));
        // $form->switch('is_leader', __('Is leader'));
        // $form->switch('is_sign', __('Is sign'));
        // $form->switch('isOnline', __('IsOnline'));
        // $form->switch('status', __('Status'))->default(1);
        // $form->switch('is_points_first', __('Is points first'));
        // $form->datetime('locktime', __('Locktime'))->default(date('Y-m-d H:i:s'));
        // $form->number('online_time', __('Online time'));
        // $form->number('dress_1', __('Dress 1'));
        // $form->number('dress_2', __('Dress 2'));
        // $form->number('dress_3', __('Dress 3'));
        // $form->number('dress_4', __('Dress 4'));
        // $form->text('dress_5', __('Dress 5'));
        // $form->number('cp_card', __('Cp card'));
        // $form->number('keys_num', __('Keys num'));
        // $form->text('nickname', __('Nickname'));
        // $form->text('idno', __('Idno'));
        // $form->text('mykeep', __('Mykeep'));
        // $form->text('system', __('System'))->default('normal');
        // $form->text('channel', __('Channel'))->default('normal');
        // $form->text('img_1', __('Img 1'));
        // $form->text('img_2', __('Img 2'));
        // $form->text('img_3', __('Img 3'));
        // $form->number('points', __('Points'));
        // $form->text('login_ip', __('Login ip'));
        // $form->text('device_token', __('Device token'));
        // $form->number('scale', __('Scale'));
        // $form->switch('is_idcard', __('Is idcard'));
        // $form->number('country_id', __('Country id'));
        // $form->number('now_room_uid', __('Now room uid'));
        // $form->textarea('bio', __('Bio'));
        // $form->number('agency_id', __('Agency id'));
        // $form->number('family_id', __('Family id'));
        // $form->switch('is_host', __('Is host'));
        // $form->text('whatsapp', __('Whatsapp'));
        // $form->decimal('old_usd', __('Old usd'));
        // $form->decimal('target_usd', __('Target usd'));
        // $form->decimal('target_token_usd', __('Target token usd'));
        // $form->text('uuid', __('Uuid'));
        // $form->switch('is_gold_id', __('Is gold id'));
        // $form->text('chat_id', __('Chat id'));
        // $form->text('notification_id', __('Notification id'));
        // $form->number('vip', __('Vip'));
        // $form->number('sub_sender_level', __('Sub sender level'));
        // $form->number('sub_receiver_level', __('Sub receiver level'));
        // $form->number('sub_sender_num', __('Sub sender num'));
        // $form->number('sub_receiver_num', __('Sub receiver num'));
        // $form->decimal('salary', __('Salary'))->default(0.00);
        // $form->number('monthly_diamond_send', __('Monthly diamond send'));
        // $form->number('total_diamond_send', __('Total diamond send'));
        // $form->number('monthly_diamond_received', __('Monthly diamond received'));
        // $form->number('total_diamond_received', __('Total diamond received'));
        // $form->number('sender_level', __('Sender level'));
        // $form->number('received_level', __('Received level'));
        // $form->number('today_days', __('Today days'));
        // $form->number('monthly_days', __('Monthly days'));
        // $form->number('total_days', __('Total days'));
        // $form->textarea('credential', __('Credential'));
        // $form->number('type_user', __('Type user'));
        // $form->text('apple_id', __('Apple id'));
        $opsAgencyManger = [];
        foreach (DB::table('admin_users')->get() as $user){
            $opsAgencyManger[$user->id] = $user->name;
        }
        $form->select('agency_manger_id', __('Agency Manger Id'))->options($opsAgencyManger);

        $opsAgencyMangerUser = [];
        foreach (User::where('is_manger',0)->get() as $user){
            $opsAgencyMangerUser[$user->id] = $user->uuid.'_'.$user->name;
        }
        $form->select('agency_manger_id', __('Agency Manger Id'))->options($opsAgencyMangerUser);
        // $form->text('apple_id', __('Apple id'));
        $form->hidden('is_manger', __('Is manger'))->default(true);

        $form->saving(function ($form) {

            // dd(request()->all());
        }

        );
        return $form;
    }


    public function extendGrid($grid){
        $permission_name = $this->permission_name;

        if (!Admin::user()->can('*')) {
            if ( ! Admin ::user () -> can ( 'edit-' . $permission_name ) ) {
                $grid -> hiddenColumns = $this -> hiddenColumns;
            }
            if ( ! Admin ::user () -> can ( 'delete-' . $permission_name ) ) {
                $grid -> disableRowSelector ();
            }
            if ( ! Admin ::user () -> can ( 'create-' . $permission_name ) ) {
                $grid -> disableCreateButton ();
            }
            $grid -> actions (
                function ( $actions ) use ( $permission_name ) {

                    // The roles with this permission will not able to see the delete button in actions column.
                    if ( ! Admin ::user () -> can ( 'delete-' . $permission_name ) ) {
                        $actions -> disableDelete ();
                    }
                    if ( ! Admin ::user () -> can ( 'edit-' . $permission_name ) ) {
                        $actions -> disableEdit ();
                    }
                    if ( ! Admin ::user () -> can ( 'show-' . $permission_name ) ) {
                        $actions -> disableView ();
                    }

                }
            );
            if (! Admin ::user () -> can ( 'edit-' . $permission_name ) && ! Admin ::user () -> can ( 'delete-' . $permission_name ) && ! Admin ::user () -> can ( 'create-' . $permission_name )){
                $grid->disableActions ();
            }
        }



        $grid->export(function ($export) use ($grid){

            $export->filename($this->permission_name.'.csv');

            $export->except($this->hiddenColumns);

//            $export->only(['column3', 'column4' ...]);
//
            $export->originalValue($grid->columnNames);
//
//            $export->column('column_5', function ($value, $original) {
//                return $value;
//            });
        });

    }
}
