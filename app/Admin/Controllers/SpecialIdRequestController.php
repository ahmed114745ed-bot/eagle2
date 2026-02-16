<?php

namespace App\Admin\Controllers;

use App\Models\Pack;
use App\Models\User;
use App\Models\Ware;
use App\Models\Config;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;;

use Modules\SpecialId\Entities\UserWare;
use App\Admin\Controllers\MainController;
use Modules\Public\Http\Services\UpgradeLevelServices;


class SpecialIdRequestController extends MainController
{

    public $permission_name = 'special-uuid-requests';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Special uuid requests'))
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
            ->title(trans('Special uuid requests'))
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
            ->title(trans('Special uuid requests'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Special uuid requests'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new UserWare());
        $countryID = session('filter_country_id');
        $grid->model()
            ->with([
                'user',
                'ware',
                'user.profile',
                'user.packs' => fn($q) => $q->whereIn('type', [25])->where('is_used', true)->with('ware:id,value'),

            ])
            ->when($countryID, fn($q) => $q->whereHas('user', fn($q) => $q->where('country_id', $countryID)))
            ->where('disable', 0)->orderByDesc('id');

        $grid->id('ID');
        $grid->column('ware.value', __("special_id"));

        $grid->column('user.name', __('User'))->display(function () {
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($this->user->profile?->avatar) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            return '
                <div style="display: flex; align-items: center; gap: 10px;">
                    <img src="' . $url . '" alt="User Image" style="width: 40px; height: 40px;">
                    <div>
                        <a href="/admin/users/' . $this->user_id . '" style="text-decoration: none; color:rgb(253, 253, 253); font-weight: bold;">' . $this->user->name . '</a>
                        <div style="font-size: 12px; color: #fff;">' . 'Uuid: ' . $this->user->uuid . '</div>
                    </div>
                </div>
            ';
        });
        $grid->column('user.phone', __('Phone'));



        $grid->column('disable', __('Status'))->display(function () {
            if ($this->disable) {
                return '<span style="background-color: #28a745; color: #fff; padding: 5px 10px; border-radius: 5px; font-size: 12px;">' . __('Enabled') . '</span>';
            } else {
                return '<span style="background-color: #dc3545; color: #fff; padding: 5px 10px; border-radius: 5px; font-size: 12px;">' . __('Disabled') . '</span>';
            }
        });


        $grid->disableCreateButton();

        $grid->disableExport();

        $grid->disableActions();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        return $grid;
    }

    protected function form()
    {

        $form = new Form(new UserWare);
        $form->display('ID');
        $form->hidden('user_id');
        $form->hidden('ware_id');
        $form->select('disable', 'status')->options(
            [
                0 => __('pending'),
                1 => __('accepted'),
                2 => __('denied')
            ]
        );


        $form->saved(function (Form $form) {
            $ware_user_id = $form->model()->id;
            $user_ware = UserWare::find($ware_user_id);
            $ware_id = $user_ware->ware_id;
            $user_id = $user_ware->user_id;
            $status = request('disable');

            if ($status == 2) {
                $status = 0;
            }
            $form->disable = $status;
            $user = User::query()->find($user_id);
            $total_price = Config::query()->where('name', 'upload_special_id_price')->first()?->value ?? 0;

            if ($user_id && $status == 1) {
                \DB::table('user_ware')
                    ->where('ware_id', $ware_id)
                    ->where('user_id', $user_id)
                    ->update(['disable' => $status]);
                $ware = Ware::query()->find($ware_id);
                $ware->update([
                    'enable' => 1
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
                    $arr['receive_type'] = 'special-id-form';

                    $newPack = Pack::query()->create($arr);
                    DB::commit();
                    (new UpgradeLevelServices())->purchaseItem($user, $ware->exp);
                } catch (\Exception $exception) {
                    DB::rollBack();
                }
            } else {
                $user->increment('di', $total_price);
            }
        });


        return $form;
    }
}
