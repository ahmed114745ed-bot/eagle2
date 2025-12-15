<?php

namespace App\Admin\Controllers;

use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Selectables\OVips;
use App\Selectables\Badges;
use Encore\Admin\Widgets\Table;
use App\Selectables\WaresByType;
use Modules\Badge\Entities\Badge;
use App\Models\SuperPackageReward;
use Encore\Admin\Facades\Admin;
use App\Admin\Controllers\MainController;
use Encore\Admin\Layout\Content;




class SuperPackageController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SuperPackageReward';
    public $permission_name = 'super-package-reward';


    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Super Package reward'))
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
            ->title(trans('Super Package reward'))
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
            ->title(trans('Super Package reward'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Super Package reward'))
            ->body($this->form()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SuperPackageReward());
        $grid->model()->select('id', 'title')->with([
            'packageRewards.ware:id,name,img2,show_img',
            'packageRewards.vip:id,name,img',
            'packageRewards.badge:id,name,image',
            'packageRewards:id,super_package_id,type,target,expire,quantity',
        ]);

        $grid->column('id', __('Id'));
        $grid->column('title', __('title'));


        $grid->column('members', __('rewards'))->expand(function ($model) {

            $members = $model->packageRewards->map(function ($reward) {

                $gift = '';
                $path = '';

                switch ($reward->type) {
                    case 'ware':
                        $gift = optional($reward->ware)->name;
                        $path = optional($reward->ware)->img2
                            ?? optional($reward->ware)->show_img;
                        break;

                    case 'vip':
                        $gift = optional($reward->vip)->name;
                        $path = optional($reward->vip)->img;
                        break;

                    case 'badge':
                        $gift = optional($reward->badge)->name;
                        $path = optional($reward->badge)->image;
                        break;

                    case 'coin':
                        $gift = $reward->target;
                        $path = 'coin.png';
                        break;

                    case 'achievement':
                        $gift = "<img src='" . getDriverUrl() . "/{$reward->target}' width='80'>";
                        $path = $reward->target;
                        break;
                }

                $image = handleShowImageWithTypes(
                    $reward->id,
                    getImagePath($path),
                    50,
                    50
                );

                return [
                    'id'       => $reward->id,
                    'type'     => $reward->type,
                    'gift'     => $gift,
                    'image'    => $image,
                    'quantity' => $reward->expire,
                    'expire'   => $reward->quantity,
                ];
            });

            return new Table(
                ['ID', __('type'), __('gift'), __('image'), __('quantity'), __('expire')],
                $members->toArray()
            );
        });



        Admin::script("
        $('.rtlSvga').each(function() {
            var id = $(this).attr('id');
            var url = $(this).data('url');
            var player = new SVGA.Player('#' + id);
            var parser = new SVGA.Parser();
            parser.load(url, function(videoItem) {
                player.setVideoItem(videoItem);
                player.startAnimation();
            });
        });
    ");


        if (Admin::user()->can('dedicate-switch-' . $this->permission_name) || Admin::user()->can('*')) {
            $grid->column('return', __('dedicate'))->display(function () {

                return (new \App\Admin\Actions\DedicateSuperPackageRewardAction($this->id))->render();
            });
        }
        Admin::script("
        if (window.innerWidth >= 1024) {
            $('.table-responsive').removeClass('table-responsive');
            }
        ");
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableEdit();
        });
        $this->extendGrid($grid);
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */


    /**
     * Make a form builder.
     *
     * @return Form
     */


    protected function form()
    {
        $form = new Form(new SuperPackageReward());

        $form->text('title', __('title'))->required();

        $form->fieldset(__('Wares'), function (Form $form) {
            $this->addWareField($form);
            $form->number('expire_ware', __('expire'));
            $form->number('quantity_ware', __('number'))->min(0);
        });

        $form->fieldset(__('Badges'), function (Form $form) {
            $this->addBadgeField($form);
            $form->number('expire_badge', __('expire'));
            $form->number('quantity_badge', __('number'))->min(0);
        });

        $form->fieldset(__('vips'), function (Form $form) {
            $form->belongsToMany('vips', OVips::class, trans('vips'));
            $form->number('expire_vip', __('expire'));
            $form->number('quantity_vip', __('number'))->min(0);
        });

        $form->fieldset(__('Coins'), function (Form $form) {
            $form->number('coins', __('Coins'))->min(0);
        });

        $form->fieldset(__('Achievement'), function (Form $form) {
            $form->image('achievement', __('Image'))->name(function ($file) {
                return now()->timestamp . '.' . $file->guessExtension();
            })->disk('gcs');
            $form->number('expire_achievement', __('expire'));
        });

        Admin::script('
            $(".collapse.in").removeClass("in"); // Bootstrap 3
            $(".collapse.show").removeClass("show"); // Bootstrap 4/5
        ');

        return $form;
    }


    protected function addWareField(Form $form)
    {
        $prefix = 'wares';
        $form->belongsToMany('wares', WaresByType::class, __('Wares'), function ($form) use ($prefix) {
            $form->setElementName($prefix . 'wares')
                ->select('id', __('wares'))
                ->options(function ($id) {
                    if (!$id) return [];
                    $ware = Ware::find($id);
                    return $ware ? [$ware->id => "{$ware->name}_{$ware->id}"] : [];
                })
                ->attribute([
                    'data-image-select' => 1,
                    'data-load-url' => admin_url('wares-by-id')
                ]);

            $form->html('<div id="ware-image-preview" style="margin-top:10px;"></div>');

            $this->addWareJs();
        });
    }

    protected function addBadgeField(Form $form)
    {
        $prefix = 'badges';
        $form->belongsToMany('badges', Badges::class, __('Badges'), function ($form) use ($prefix) {
            $form->setElementName($prefix . 'badges')
                ->select('id', __('badges'))
                ->options(function ($id) {
                    if (!$id) return [];
                    $ware = Badge::find($id);
                    return $ware ? [$ware->id => "{$ware->name}_{$ware->id}"] : [];
                })
                ->attribute([
                    'data-image-select' => 1,
                    'data-load-url' => admin_url('wares-by-id')
                ]);

            $form->html('<div id="ware-image-preview" style="margin-top:10px;"></div>');

            $this->addWareJs();
        });
    }
}
