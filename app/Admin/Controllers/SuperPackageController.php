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

        $grid->column('id', __('Id'));
        $grid->column('title', __('title'));

        $grid->column('members', __('rewards'))->expand(function ($model) {
            $members = $model->packageRewards()->get()->map(function ($memper) {
                $giftName = '';
                $imageUrl = '';
        
                switch ($memper->type) {
                    case 'ware':
                        $giftName = $memper->ware->name ?? '';
                        $imageUrl = getImagePath($memper->ware->img2 ?? $memper->ware->show_img ?? '');
                        break;
                    case 'vip':
                        $giftName = $memper->vip->name ?? '';
                        $imageUrl = getImagePath($memper->vip->img ?? '');
                        break;
                    case 'badge':
                        $giftName = $memper->badge->name ?? '';
                        $imageUrl = getImagePath($memper->badge->image ?? '');
                        break;
                    case 'coins':
                        $giftName = $memper->target;
                        $imageUrl = getImagePath('coin.png');
                        break;
                    case 'achievement':
                        $giftName = $memper->target;
                        $imageUrl = getDriverUrl() . '/' . $memper->target;
                        break;
                }
        
                $imageHtml = "<img src='{$imageUrl}' width='50' height='50' />";
        
                return [
                    'id' => $memper->id,
                    'type' => $memper->type,
                    'gift' => $giftName,
                    'image' => $imageHtml,
                    'quantity' => $memper->quantity,
                    'expire' => $memper->expire,
                ];
            });
        
            return new Table(
                ['ID', __('type'), __('gift'), __('image'), __('quantity'), __('expire')],
                $members->toArray()
            );
        });
        if (Admin::user()->can('dedicate-switch-' . $this->permission_name) || Admin::user()->can('*')) {
            $grid->column('return', __('dedicate'))->display(function () {
               
                return (new \App\Admin\Actions\DedicateSuperPackageRewardAction($this->id))->render();
            });
        }
        $grid->disableExport();
        $this->extendGrid($grid);
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
        $show = new Show(SuperPackageReward::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    // protected function form()
    // {
    //     $form = new Form(new SuperPackageReward());

    //     $form->text('title', __('Title'));
    //     $form->select('type', trans('type'))->options(["ware" => __('ware'), "badge" => __('badge'), "vip" => __('vip'), "coins" => __('coins'), "achievement" => __('achievement')])
    //         ->when("ware", function () use ($form) {
    //             $this->addWareField($form);
    //         })
    //         ->when("badge", function () use ($form) {
    //             $this->addBadgeField($form);
    //         })
    //         ->when("vip", function () use ($form) {
    //             $form->select('target2', trans('vips'))->options(function () {
    //                 $vips = OVip::query()->select('id', 'name')->get();
    //                 foreach ($vips as  $vip) {
    //                     $ops[$vip->id] = $vip->name;
    //                 }
    //                 return $ops;
    //             });
    //         })
    //         ->when("coins", function () use ($form) {
    //             $form->number("target3", __("coins"));
    //         })->when("achievement", function () use ($form) {
    //             $form->image("target4", __('image'))->name(function ($file) {
    //                 return now()->timestamp . '.' . $file->guessExtension();
    //             })->disk('gcs');
    //         });

    //     return $form;
    // }


    protected function form()
    {
        $form = new Form(new SuperPackageReward());
    
        $form->tab(__('data'), function (Form $form) {
            $form->text('title', __('title'))->required();
        });

        $form->tab(__('Ware'), function (Form $form) {
            $this->addWareField($form);
            $form->number('expire_ware', __('expire'));
            $form->number('quantity_ware', __('number'))->min(0);
        });
        
        $form->tab(__('Badge'), function (Form $form) {
            $this->addBadgeField($form);
            $form->number('expire_badge', __('expire'));
            $form->number('quantity_badge', __('number'))->min(0);
        });
    
        $form->tab(__('VIP'), function (Form $form) {
            $form->belongsToMany('vips', OVips::class, trans('vips'));
            $form->number('expire_vip', __('expire'));
            $form->number('quantity_vip', __('number'))->min(0);
        });
    
        $form->tab(__('Coins'), function (Form $form) {
            $form->number('coins', __('Coins'))->min(0);
        });
    
        $form->tab(__('Achievement'), function (Form $form) {
            $form->image('achievement', __('Image'))->name(function ($file) {
                return now()->timestamp . '.' . $file->guessExtension();
            })->disk('gcs');
            $form->number('expire_achievement', __('expire'));
        });
    
        return $form;
    }


    protected function addWareField(Form $form)
    {
        $prefix = 'wares';
        $form->belongsToMany('wares', WaresByType::class, __('Ware'), function ($form) use ($prefix) {
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
