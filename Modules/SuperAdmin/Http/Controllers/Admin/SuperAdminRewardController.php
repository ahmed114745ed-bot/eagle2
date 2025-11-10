<?php

namespace Modules\SuperAdmin\Http\Controllers\Admin;

use App\Models\Ware;
use Encore\Admin\Grid;
use Modules\SuperAdmin\Actions\Admin\DedicateSuperAdminRewardAction;
use Modules\Vip\Entities\OVip;
use Encore\Admin\Layout\Content;
use Modules\Badge\Entities\Badge;
use App\Admin\Controllers\MainController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\HtmlString;
use Encore\Admin\Widgets\Table;
use App\Models\SuperPackageReward;


class SuperAdminRewardController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SuperAdminReward';

    public $permission_name = 'super-admin-reward';
    public function index(Content $content)
    {

        if (!request()->has('type')) {
            return redirect()->to(url()->current() . '?type=vip');
        }

        session(['last_ware_type' => request()->get('type', 'vip')]);
        return parent::index($content
            ->title(trans('Super Admin Reward'))
            ->row(function (Row $row) {
                $row->column(12, $this->grid2());
            })
            ->row(function (Row $row) {
                $row->column(12, $this->tabsComponent());
            })
            ->row(function ($row) {
                $row->column(12, $this->grid());
            }));
    }

    private function tabsComponent()
    {
        $content = new Row();

        $types =  ['vip', 'ware', 'badge', 'package'];
        $currentType = request()->get('type', 'vip');

        $box = new Box(content: view('admin.grid.Form.rewardTabs', [
            'types' => $types,
            'currentType' => $currentType
        ]));

        $content->column(12, $box);

        return $content;
    }

    protected function grid2()
    {
        return (new Box(
            title: __('admin.description'),
            content: view('admin.grid.superadmin.description'),
        ));
    }



    protected function grid()
    {
        $type = request('type');

        if ($type == 'vip') {
            $grid = new Grid(new OVip());
            $this->vip($grid);
        } elseif ($type == 'badge') {
            $grid = new Grid(new Badge());
            $this->badge($grid);
        } elseif ($type == 'ware') {
            $grid = new Grid(new Ware());
            $this->ware($grid);
        } elseif ($type == 'package') {
            $grid = new Grid(new SuperPackageReward());
            $this->package($grid);
        } else {
            // Optional: handle invalid type
            $grid = new Grid(new OVip());
        }
        if ($type == 'package') {
            if (Admin::user()->can('dedicate-switch-' . $this->permission_name) || Admin::user()->can('*')) {
                $grid->column('return', __('dedicate'))->display(function () {

                    return (new \App\Admin\Actions\DedicateSuperPackageRewardAction($this->id))->render();
                });
            }
        } else {
            if (Admin::user()->can('dedicate-switch-' . $this->permission_name) || Admin::user()->can('*')) {
                $grid->column('return', __('dedicate'))->display(function () {
                    $type = request('type');
                    return (new DedicateSuperAdminRewardAction($this->id, $type))->render();
                });
            }
        }


        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->disableActions();
        $grid->disableCreateButton();

        if (Admin::user()->can($this->permission_name . '-history') || Admin::user()->can('*')) {
            $grid->tools(function (Grid\Tools $tools) {
                $url = '/admin/super-admin-reward-history' . "?type=" . request('type');
                $button = '<a href="' . $url . '" class="btn btn-sm btn-success"><i class="fa fa-go"></i>&nbsp;&nbsp;' . __("admin.history") . '</a>';
                $tools->append($button);
            });
        }

        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");

        return $grid;
    }


    protected function ware($grid)
    {
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    if ($this->input !== null) {
                        $query->where('type', $this->input);
                    }
                }, __('Type'), 'type-ware')->select(getTranslatedWare());
            });
        });

        $grid->model()->whereIn('type', [4, 5, 6, 28]);
        $grid->column('name', __('name'))->sortable();
        $grid->column('show_img', __('show_img'))->image('', 30);
        $grid->column('img2', __('show_img'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
    }
    protected function package($grid)
    {
        $grid->column('title', __('package'));
        // $grid->column('members', __('rewards'))->expand(function ($model) {
        //     $mempers = $model->packageRewards()
        //         ->get() // 👈 fetch the related records first
        //         ->map(function ($memper) {
        //             $gifts = '';
        //             $path = '';

        //             if ($memper->type == "ware") {
        //                 $gifts = @$memper->ware->name ?? '';
        //                 $path = @$memper->ware->img2 ?? (@$memper->ware->show_img ?? "");
        //             } elseif ($memper->type == "vip") {
        //                 $gifts = @$memper->vip->name ?? '';
        //                 $path = @$memper->vip->img ?? '';
        //             } elseif ($memper->type == "badge") {
        //                 $gifts = @$memper->badge->name ?? '';
        //                 $path = @$memper->badge->image ?? '';
        //             } elseif ($memper->type == "coins") {
        //                 $gifts = @$memper->target;
        //                 $path = 'coin.png';
        //             } elseif ($memper->type == "achievement") {
        //                 $value = getDriverUrl() . '/' . @$memper->target;
        //                 $gifts = "<img src='$value' width='80' height='80'>";
        //                 $path = $memper->target;
        //             }

        //             $url = getImagePath($path);
        //             $image = handleShowImageWithTypes($this->id, $url, 50, 50);

        //             return [
        //                 'id'    => $memper->id,
        //                 'type'  => $memper->type,
        //                 'gift'  => $gifts,
        //                 'image' => $image,
        //                 'quantity' => $memper->expire,
        //                 'expire'  => $memper->quantity,

        //             ];
        //         });

        //     return new Table(
        //         ['ID', __('type'), __('gift'), __('image'), __('quantity'), __('expire')],
        //         $mempers->toArray()
        //     );
        // });



        $grid->column('members', __('Rewards'))->expand(function ($model) {

            $members = $model->packageRewards()->get()->map(function ($memper) {
                $gifts = '';
                $path = '';
                $url = '';

                switch ($memper->type) {
                    case 'ware':
                        $gifts = @$memper->ware->name ?? '';
                        $path = @$memper->ware->img2 ?? @$memper->ware->show_img ?? '';
                        break;

                    case 'vip':
                        $gifts = @$memper->vip->name ?? '';
                        $path = @$memper->vip->img ?? '';
                        break;

                    case 'badge':
                        $gifts = @$memper->badge->name ?? '';
                        $path = @$memper->badge->image ?? '';
                        break;

                    case 'coins':
                        $gifts = $memper->target ?? '';
                        $path = 'coin.png';
                        break;

                    case 'achievement':
                        $gifts = "<img src='" . getDriverUrl() . '/' . @$memper->target . "' width='80' height='80'>";
                        $path = $memper->target;
                        break;
                }

                // Build the full URL
                $url = getImagePath($path);

                // Handle different types
                $imageType = getFileExtension($url);
                if ($imageType === 'svga' || $imageType === 'zz') {
                    // Generate unique div ID for each SVGA
                    $divId = 'svga_' . $memper->id;
                    $image = new HtmlString(
                        "<div class='rtlSvga' id='{$divId}' data-url='{$url}' style='width:50px;height:50px;'></div>"
                    );
                } elseif ($imageType === 'mp4') {
                    $image = new HtmlString(
                        "<video width='50' height='50' controls autoplay muted loop>
                    <source src='{$url}' type='video/mp4'>
                    <source src='{$url}' type='video/webm'>
                    Your browser does not support the video tag.
                 </video>"
                    );
                } else {
                    // Normal images
                    $image = new HtmlString("<img src='{$url}' width='50' height='50' style='border-radius:50%; object-fit:cover;'/>");
                }

                return [
                    'id'       => $memper->id,
                    'type'     => $memper->type,
                    'gift'     => $gifts,
                    'image'    => $image,
                    'quantity' => $memper->quantity ?? 0,
                    'expire'   => $memper->expire ?? 0,
                ];
            });

            return new Table(
                ['ID', __('Type'), __('Gift'), __('Image'), __('Quantity'), __('Expire')],
                $members->toArray()
            );
        });

        Admin::script(<<<JS
            function initSvgaPlayers() {
                document.querySelectorAll('.rtlSvga').forEach(el => {
                    const url = el.dataset.url;
                    if (!url) return;

                    const player = new SVGA.Player(el);
                    const parser = new SVGA.Parser();
                    parser.load(url, function(videoItem) {
                        player.setVideoItem(videoItem);
                        player.startAnimation();
                    });
                });
            }

            // Initial load
            document.addEventListener('DOMContentLoaded', initSvgaPlayers);

            // Re-run after PJAX updates (Laravel Admin tables)
            $(document).on('pjax:success', initSvgaPlayers);
            JS
            );
    }
    protected function badge($grid)
    {
        $grid->model()->orderBy('priority', 'desc');
        $grid->column('name', __('name'))->sortable();
        $grid->column('priority', __('Priority'))->sortable();
        $grid->column('image', __('image'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->filter(function ($filter) {
            $filter->like('name', 'Name');
            $filter->equal('priority', 'Priority');
        });
    }

    protected function vip($grid)
    {
        $grid->column('level', __('level'))->sortable('o_vips.level');
        $grid->column('name', __('name'))->sortable('o_vips.name');
        $grid->column('img', __('img'))->display(function ($path) {
            /** @var OVip $this */
            $defaultImage = asset("images/image.png");
            $url = getImagePath($path) ?? $defaultImage;
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
    }
}
