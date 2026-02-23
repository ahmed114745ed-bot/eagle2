<?php

namespace Utd\Vip\Http\Controllers\Web;

use App\Admin\Controllers\MainController;
use App\Helpers\Common;
use App\Models\Config;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Utd\Vip\Entities\OVip;
use Utd\Vip\Selectables\Privileges;
use Utd\Vip\Services\VipAdminService;

class OVipController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'VIPs';

    public $permission_setting = 'ovip-settings';

    public $hiddenColumns = [];

    public function __construct()
    {
        $this->middleware(\Utd\Vip\Http\Middleware\CheckVipFeatureEnabled::class);
    }

    public function vipSettings(Content $content)
    {
        if (! Admin::user()->can('*')) {
            Permission::check('browse-'.$this->permission_setting);
        }

        $config = Config::pluck('value', 'name')->toArray();
        $config['enable_vip_auto'] = true;

        return $content->view('vip_settings', compact('config'));
    }

    public function index(Content $content)
    {
        return parent::index($content
            ->title(__('vip'))
            ->row(function ($row) {
                $row->column(12, $this->grid());
            }));
    }

    public function show($id, Content $content)
    {
        $oVip = OVip::findOrFail($id);

        return parent::show($id, $content->title(__('OVip'))->view('ovip_profile', compact('oVip')));
    }

    /**
     * Edit interface.
     *
     * @param  mixed  $id
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('vip'))
            ->body($this->form()->edit($id)));
    }

    public function editBackgroundImage($id, Content $content)
    {
        $oVip = OVip::findOrFail($id);

        return parent::edit($id, $content
            ->title(trans('vip'))
            ->body($this->backgroundImage($oVip)->edit($id)));
    }

    public function updateBackgroundImage($ovip_id)
    {
        $oVip = OVip::findOrFail($ovip_id);

        if (request()->hasFile('background_img')) {

            $image = Common::upload('images', request()->file('background_img'));

            $oVip->background_img = $image;
            $oVip->save();
        }

        admin_toastr(__('Saved successfully'), 'success');

        return redirect(admin_url('ovip'));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('vip'))
            ->body($this->form()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new OVip);
        $grid->model()->with('privilegs');
        $grid->id('ID');
        $grid->column('level', __('level'));
        $grid->column('name', __('name'));
        $grid->column('img', __('img'))->display(function ($path) {
            /** @var OVip $this */
            $defaultImage = asset('images/image.png');
            $url = getImagePath($path) ?? $defaultImage;
            if (! isImageExists($url)) {
                $url = $defaultImage;
            }

            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('price', __('price'))->display(function ($coin) {
            $icon = asset('images/coin.jpg');

            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>".number_format($coin)."</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });
        $grid->column('expire', __('expire'));

        if (Admin::user()->can('browse-'.'vip-gift') || Admin::user()->can('*')) {
            $grid->column(__('file'))->display(function () {
                $privilegeTypes = optional($this->privilegs)->pluck('en_name', 'type')->sortKeys();
                $type = $privilegeTypes?->keys()->first();
                $url1 = url('admin/ovip-gift/'.$this->id.'?type='.$type);

                $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>".__('setting').'</a>';

                return $button1;
            });
        }

        if (Admin::user()->can('edit-'.'vip-gift') || Admin::user()->can('*')) {
            $grid->column(__('Theme'))->display(function () {

                $url1 = url('admin/ovip-theme/'.$this->id);

                $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>".__('Theme').'</a>';

                return $button1;
            });
        }
        $this->extendGrid($grid);
        $grid->disableExport();
        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param  mixed  $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(OVip::findOrFail($id));
        $this->extendShow($show);

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new OVip);
        $this->disableFormTools($form);

        if (Session::has('show_alert_vip')) {
            $form->html('<script>
             $(document).ready(function () {
                 alert("___");
             });
         </script>');
        }
        $form->display(__('ID'));
        $form->number('level', __('level'))
            ->rules(function () use ($form) {
                return [
                    'required',
                    $form->isCreating()
                        ? Rule::unique('o_vips', 'level')
                        : Rule::unique('o_vips', 'level')->ignore($form->model()->id),
                ];
            });

        $form->text('name', __('name'));
        $form->image('image2', trans('img'))->name(fn ($file) => now()->timestamp.rand(0, 999).'.'.$file->guessExtension());

        $form->file('img', trans('svga'))->name(function ($file) {
            return 'svga_'.Str::random(6).'.'.$file->getClientOriginalExtension();
        });

        $form->currency('price', __('price'))->symbol('🪙')->rules('required|numeric|gt:0');

        if (Admin::user()->can('*')) {
            $form->number('expire', __('expire'))->rules('required|numeric|gt:0');
        } else {
            $form->number('expire', __('expire'))->max(30)->rules('required|numeric|gt:0');
        }
        $form->belongsToMany('privilegs', Privileges::class, __('privileges'))->rules('required|array|min:1');

        $form->saving(function (Form $form) {
            app(VipAdminService::class)->handleSaving($form);
        });

        return $form;
    }

    protected function backgroundImage($model = null)
    {
        if ($model === null) {
            $model = new OVip;
        }

        $form = new Form($model);

        // ✅ IMPORTANT: SET FORM ACTION HERE
        $form->setAction(admin_url('ovip-theme/'.$model->id));

        // Disable default tools (view, delete, etc.)
        $this->disableFormTools($form);

        // Background image upload
        $form->image('background_img', trans('background'))
            ->uniqueName();

        // After save
        $form->saved(function (Form $form) {
            admin_toastr(__('Background image updated successfully'), 'success');

            return redirect(admin_url('ovip'));
        });

        return $form;
    }
}
