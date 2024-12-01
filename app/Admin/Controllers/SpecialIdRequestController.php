<?php

namespace App\Admin\Controllers;

use App\Models\Pack;
use App\Models\User;
use App\Models\Ware;
use App\Models\Agency;
use App\Models\Config;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Layout\Content;
use App\Models\AgencyJoinRequest;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Actions\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\SpecialId\Entities\UserWare;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\HasResourceActions;
use Modules\Public\Http\Services\UpgradeLevelServices;

class SpecialIdRequestController extends MainController
{

    public $permission_name = 'special-id-request';

     public function index(Content $content)
    {
        return $content
            ->title(trans('special-id-request'))
            ->body($this->grid());
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
        return $content
            ->title(trans('special-id-request'))
            ->body($this->detail($id));
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
        return $content
            ->title(trans('special-id-request'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('special-id-request'))
            ->body($this->form());
    }

    protected function grid()
    {
        $grid = new Grid(new UserWare());
        $grid->model ()->where ('disable',0)->orderByDesc ('id');

        $grid->id('ID');
        $grid->column('ware.value',__("special_id"));

        $grid->column('user.name',__ ('user'));
        $grid->column('user.uuid',__ ('user id'));
        $grid->column('disable',__ ('status'))->display (function (){
            return ($this->disable ? __('Enabled') : __('Disabled')) ;
        });


        $grid->disableCreateButton ();

        $grid->disableExport ();

        return $grid;
    }


    protected function detail($id)
    {
        $show = new Show(StoreLog::findOrFail($id));

        $show->id('ID');
        $show->user_id('user_id');
        $show->get_nums('get_nums');
        $show->get_type('get_type');
        $show->now_nums('now_nums');
        $show->adduser('adduser');
        $show->symbol('symbol');
        $show->types('types');
        $show->union_id('union_id');
        $show->family_id('family_id');
        $show->created_at(trans('admin.created_at'));
        $show->updated_at(trans('admin.updated_at'));

        return $show;
    }
    protected function form()
    {

        $form = new Form(new UserWare);
        $form->display('ID');
        $form->hidden('user_id');
        $form->hidden('ware_id');
        $form->select('disable', 'status')->options (
            [
                0=>__('pending'),
                1=>__ ('accepted'),
                2=>__ ('denied')
            ]
        );


        $form->saved(function (Form $form) {
            $ware_user_id = $form->model()->id;
            $user_ware=UserWare::find($ware_user_id);
            $ware_id=$user_ware->ware_id;
            $user_id = $user_ware->user_id;
            $status = request('disable');

            if ($status == 2) {
                $status = 0;
            }
            $form->disable=$status;
            $user=User::query()->find($user_id);
            $total_price = Config::query()->where('name','upload_special_id_price')->first()?->value ?? 0;

            if ($user_id && $status == 1 ) {
                \DB::table('user_ware')
                    ->where('ware_id', $ware_id)
                    ->where('user_id', $user_id)
                    ->update(['disable' => $status]);
                $ware=Ware::query()->find($ware_id);
                $ware->update([
                    'enable'=>1
                ]);
                try {
                    $arr['user_id']   = $user->id;
                    $arr['type']      = $ware->type;
                    $arr['get_type']  = $ware->get_type;
                    $arr['target_id'] = $ware->id;
                    $arr['num']       = 1; //$qty;
                    $arr['expire']    = $ware->expire ? time() + ($ware->expire * 86400) : 0;
                    $arr['is_read']   = 1;
                    $arr['use_num']   = $ware->num;
                    $arr['price']     = $total_price;
                    $newPack=Pack::query()->create($arr);
                    DB::commit();
                    (new UpgradeLevelServices())->purchaseItem($user, $ware->exp);
                } catch (\Exception $exception) {
                    DB::rollBack();
                }
            }else{
                $user->increment('di', $total_price);
            }

        });


        return $form;
    }
}
