<?php

namespace App\Admin\Controllers;

use Modules\Vip\Entities\OVip;
use App\Models\Ware;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\HasResourceActions;

class DedicateVipController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'gift-VIP';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('vips dedicate'))
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
        $grid->model()
            ->with([
                'waresOvip:id,level,name,show_img,img2'
            ])->orderByDesc('created_at');
        $grid->id('ID');
        $grid->column('name', __('name'));
        $grid->column('price', __('price'));
        $grid->column('img', __('img'))->display(function ($path) {
            /** @var OVip $this */
            $defaultImage = asset("images/image.png");
            $url = getImagePath($path) ?? $defaultImage;
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $grid->column('ware', __('wares'))->expand(function () {

            $wares = $this->waresOvip->map(function ($ware) {
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
        if (Admin::user()->can('gift-switch-' . $this->permission_name) || Admin::user()->can('*')) {
            $grid->column('return', __('dedicate'))->display(function () {
                return (new \App\Admin\Actions\VipDedicateAction($this->id))->render();
            });
        }
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableRowSelector();
        Admin::style("
    /* Remove horizontal scroll and padding from main containers */
    .box-body {
        overflow-x: hidden !important;
        padding: 0 !important;
    }

    /* Fix the grid table */
    .grid-table {
        width: 100% !important;
        margin: 0 !important;
    }

    /* Remove responsive table wrapper effects */
    .table-responsive {
        overflow-x: visible !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
    }

    /* Fix main content area */
    .content {
        padding: 5px !important;
    }

    .content .box {
        margin: 0 !important;
    }

    /* Fix row and container padding */
    .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .col-md-12 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    /* Ensure table takes full width */
    .table {
        width: 100% !important;
        table-layout: fixed;
    }

    /* Hide horizontal scrollbar */
    body {
        overflow-x: hidden;
    }

    .wrapper {
        overflow-x: hidden;
    }
");

        Admin::script("
    function removeTableResponsive() {
        if (window.innerWidth >= 1024) {
            $('.table-responsive').removeClass('table-responsive');
            // Also remove any inline styles that might cause issues
            $('.box-body').css('overflow-x', 'hidden');
        }
    }

    removeTableResponsive();

    $(document).on('click', '.grid-expand', function() {
        setTimeout(removeTableResponsive, 100);
    });

    $(window).resize(removeTableResponsive);
");
        return $grid;
    }
}
