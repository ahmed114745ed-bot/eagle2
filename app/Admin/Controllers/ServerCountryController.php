<?php

namespace App\Admin\Controllers;

use App;
use App\Admin\Actions\MakeServerAction;
use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\Country;
use App\Models\Server;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\HasResourceActions;

class ServerCountryController extends MainController
{
    protected $title = 'server country';
    use HasResourceActions;
    public $permission_name = 'server-country';

    protected function grid()
    {
        $grid = new Grid(new Server());
        $grid->column('server_name',__("server name"));
        $grid->column('domain',__("domain"));
        $grid->column('short_name',__("short name"));
        $grid->column('bucket_name',__("storage name"));
        $grid->column('status', __('status'))->display(function() {
            if ($this->default == 1) {
                return '<span style="color: red;">●</span>';
            } else {
                return '<span style="color: blue;">●</span>';
            }
        });
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->actions(function ($actions){
            $actions->add(new MakeServerAction());
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
        $show = new Show(Ware::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('get_type', __('Get type'));
        $show->field('type', __('Type'));
        $show->field('name', __('Name'));
        $show->field('title', __('Title'));
        $show->field('price', __('Price'));
        $show->field('score', __('Score'));
        $show->field('level', __('Level'));
        $show->field('show_img', __('Show img'));
        $show->field('img1', __('Img1'));
        $show->field('img2', __('Img2'));
        $show->field('img3', __('Img3'));
        $show->field('color', __('Color'));
        $show->field('expire', __('Expire'));
        $show->field('enable', __('Enable'));
        $show->field('sort', __('Sort'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('num', __('Num'));
        $show->field('is_active_for_vip', __('Is active for vip'));
        $show->field('name_en', __('Name en'));
        $show->field('title_en', __('Title en'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Server());

        $form->display('ID');
        $form->text('server_name',__("server name"));
        $form->image('img', __('img'))->removable();
        $form->image('login_background', __('login background'))->removable();
        $form->image('splash_background', __('splash background'))->removable();
        $state = [
            'on' => ['value' => 1, 'text' => 'open', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => 'close', 'color' => 'default'],
        ];

        $form->switch('status', __("status"))->states($state);
        $form->text('short_name',__("short name"));
        $form->text('bucket_name',__("storage name"));
        $form->text('domain',__("domain"));
        $form->text('description_ar',__("description ar"));
        $form->text('description_en',__("description en"));
    //     $form->hasMany('serverCountries', function (Form\NestedForm $form) {
    //         $form->select('country_id', trans('country'))->options(function () {
    //             $ops       = [null => __('no country')];
    //             $countries = Country::all();
    //             foreach ($countries as $country) {
    //                 $ops[$country->id] = App::isLocale('en') ? $country->e_name : $country->name;
    //             }
    //             return $ops;
    //         });
    //    });
        return $form;
    }
}
