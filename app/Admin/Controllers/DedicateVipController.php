<?php

namespace App\Admin\Controllers;

use App\Models\OVip;
use App\Models\Ware;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Layout\Content;
use App\Admin\Actions\DedicateAction;
use Encore\Admin\Controllers\HasResourceActions;

class DedicateVipController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'vips-dedicate';

    public function index(Content $content)
    {
        return $content
            ->title(trans('vips dedicate'))
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
            ->title(trans('vips dedicate'))
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
            ->title(trans('vips dedicate'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('vips dedicate'))
            ->body($this->form());
    }


    protected function grid()
    {
        $grid = new Grid(new OVip);
        $grid->model()->orderByDesc('created_at');
        $grid->id('ID');
        $grid->column('name', __('name'));
        $grid->column('price', __('price'));
        $grid->column('img', __('img'))->display(function ($path) {
            /** @var OVip $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('ware', __('wares'))->expand(function ($model) {

            $wares = Ware::query()->where('get_type', 1)->where('enable', 1)->where('level', $this->level)->where('is_active_for_vip', 1)->get()->map(function ($ware) {
                $showaImage = $ware->show_img
                    ? '<img src="' . getImagePath($ware->show_img) . '" style="max-width:50px;max-height:50px;" />' // تأكد من تعديل المسار حسب مكان تخزين الصور
                    : 'No Image';
                $imgPath = getImagePath($ware->img2);

                $img = $ware->img2
                    ? handleShowImageWithTypes($ware->id, $imgPath, 50, 50) // تأكد من تعديل المسار حسب مكان تخزين الصور
                    : 'No Image';

                return    [
                    'id' => $ware->id,
                    'name' => $ware->name,
                    'show_img' => $showaImage,
                    'img2' => $img,
                ];
            });

            return new Table(
                [
                    'ID',
                    __('name'),
                    __('show_img'),
                    __('show_img'),
                ],
                $wares->toArray()
            );
        });
        $grid->column('level', __('level'));
        $grid->column('expire', __('expire'));
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
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
