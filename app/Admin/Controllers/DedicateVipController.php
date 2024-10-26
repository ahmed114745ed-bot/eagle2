<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\DedicateAction;
use App\Models\OVip;
use App\Http\Controllers\Controller;
use App\Models\VipPrivilege;
use App\Selectables\Privileges;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class DedicateVipController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'vips-dedicate';

    protected function grid()
    {
        $grid = new Grid(new OVip);
        $grid->model ()->orderByDesc('created_at');
        $grid->id('ID');
        $grid->column('name',__ ('name'));
        $grid->column('price',__ ('price'));
        $grid->column('img',__ ('img'))->image ('',150);
        $grid->column('img',__ ('img'))->display(function ($path){
            /** @var OVip $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('level',__ ('level'));
        $grid->column('expire',__ ('expire'));
        $grid->disableCreateButton ();
        $grid->actions (function ($actions){
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $actions->add(new DedicateAction());
        });
        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");
        return $grid;
    }

}
