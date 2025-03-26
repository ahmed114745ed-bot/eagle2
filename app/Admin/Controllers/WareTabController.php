<?php

namespace App\Admin\Controllers;

use App\Models\Ware;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Helpers\Common;
use Illuminate\Support\Facades\Session;
use App\Admin\Controllers\MainController;
use Modules\Public\Http\Services\UserCounterServices;
use Illuminate\Support\Str;



class WareTabController extends MainController
{
    public function index(Content $content)
    {
        session(['last_ware_type' => request()->get('type', 1)]);
        return parent::index($content
            ->title(trans('Products'))
            ->row(function (Row $row) {
                $row->column(12, $this->tabsComponent());
            })
            ->row(function (Row $row) {
                $row->column(12, $this->grid());
            }));
    }

    public function show($id, Content $content)
    {
        return $content
            ->title(trans('wares'))
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
            ->title(trans('wares'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('wares'))
            ->row(function (Row $row) {
                $row->column(12, $this->tabsComponentCreate());
            })
            ->row(function (Row $row) {
                $row->column(12, $this->form());
            });
          //  ->body($this->form());
    }

    protected function grid()
    {
        $type = request()->get('type', 1);
    
        $grid = new Grid(new Ware());
        $grid->model()->where('type', $type)->whereNot('get_type', 1);


        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('level', __('level'));
            });
        });

        $grid->id(__('ID'));
        $grid->column('name', __('name'))->editable();
        if (Admin::user()->can('edit_ware_price') || Admin::user()->can('*')) {
            $grid->column('price', __('price'))->editable();
            $grid->column('enable', __('enable'))->switch(Common::getSwitchStates());
        } else {
            $grid->column('price', __('price'));
        }

        $grid->column('show_img', __('show_img'))->image('', 30);
        $grid->column('img2', __('show_img'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('get_type', __('get_type'))->select(
            [
                4 => trans('purchase'),
                6 => trans('limited time purchase'),
            ]
        );

        $grid->title(__('title'));
        $states = [
            'on' => ['value' => 1, 'text' => 'open', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => 'close', 'color' => 'default'],
        ];
        //        $grid->score('score');
        $grid->level(__('level'));

        $grid->column('color', __('color'));
        $grid->expire(__('expire'));
        $grid->column('is_active_for_vip', __("active vip"))->switch($states);

        $grid->sort(__('sort'), __('sort'));
        $this->extendGrid($grid);
        $grid->disableExport();

        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");
        $grid->disableCreateButton();
        $grid->tools(function (Grid\Tools $tools) use ( $type) {
            $url =  url('/admin/ware-managements/create/' . $type); // Use Laravel route helper
            $add = __('add');
        
            $customButtonHTML = <<<HTML
                <a href="{$url}" class="btn btn-sm btn-success" style="margi    n-right: 10px;">
                    <i class="fa fa-plus"></i> {$add}
                </a>
            HTML;
        
            $tools->append($customButtonHTML);
        });
        return $grid;
    }


    private function tabsComponent()
    {
        $content = new Row();

        // Define your type mapping
        $typeMap = TYPE_WARE;

        $types = Ware::distinct()->pluck('type')->sort()->mapWithKeys(function ($type) use ($typeMap) {
            return [$type => $typeMap[$type] ?? "Type $type"];
        });

        $currentType = request()->get('type', $types->keys()->first());

        $box = new Box(content: view('admin.grid.Form.wareTables', [
            'types' => $types,
            'currentType' => $currentType
        ]));

        $content->column(12, $box);

        return $content;
    }

    private function tabsComponentCreate()
    {
        $content = new Row();

        // Define your type mapping
        $typeMap = TYPE_WARE;

        $types = Ware::distinct()->pluck('type')->sort()->mapWithKeys(function ($type) use ($typeMap) {
            return [$type => $typeMap[$type] ?? "Type $type"];
        });

        $currentType = request()->get('type', $types->keys()->first());

        $box = new Box(content: view('admin.grid.Form.wareCreate', [
            'types' => $types,
            'currentType' => $currentType
        ]));

        $content->column(12, $box);

        return $content;
    }

    protected function form()
    {
        $form = new Form(new Ware());
        $form->display('ID');
        $form->select('get_type', trans('get_type'))->options(
            translate(GET_TYPE_WARE)
        )->default(4);
        $form->hidden('type', __('type'))->value(request('type'))->attribute(['id' => 'type']);
        //        ->rules (function ($form){
        //            if (!$id = $form->model()->id) {
        //                return 'required';
        //            }
        //        });
        $form->text('name', trans('name'));
        $form->text('name_en', trans('Name en'));
        $form->text('title', trans('title'));
        $form->text('title_en', trans('Title en'));
        if (!$form->isEditing()) {
            if (Admin::user()->can('add_ware_price') || Admin::user()->can('*')) {
                $form->currency('price', __('price'));
                $form->switch('enable', trans('enable'))->states(Common::getSwitchStates());
            }
        }
        if ($form->isEditing()) {
            if (Admin::user()->can('edit_ware_price') || Admin::user()->can('*')) {
                $form->currency('price', __('price'));
                $form->switch('enable', trans('enable'))->states(Common::getSwitchStates());
            }
        }
        //        $form->number('score', trans('score'));
        $form->number('level', trans('level'));
        $states = [
            'on' => ['value' => 1, 'text' => 'open', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => 'close', 'color' => 'default'],
        ];
        $form->switch('is_active_for_vip', __("active vip"))->states($states);
        $form->number('exp', __('exp'));
        $form->image('show_img', trans('img'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        })->default('1.png');
        //        $form->image('img1', trans('img'));
        $form->file('img2', trans('svg'))->name(function ($file) {
            return 'svga_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        });
        $form->select('image_type1', __('image_type'))->options(
            [
                'svga' => __('svga'),
                'alpha' => __('alpha'),
                'mp4' => __('mp4'),
                'vap' => __('vap'),

            ]
        )->attribute(['id' => 'image_type1']);

        $form->select('profile_frame_type', __('image_type'))->options(
            [
                'svga' => __('svga'),
                'png' => __('png'),

            ]
        )->attribute(['id' => 'profile_frame']);

        $script = <<<SCRIPT
             $(document).ready(function() {
                 function toggleWinProbability() {
                     var type = $('#type').val();
                     if(type == '28') {
                         $('#profile_frame').closest('.form-group').show();
                          $('#image_type1').closest('.form-group').hide();
                     } else {
                         $('#profile_frame').closest('.form-group').hide();
                         $('#image_type1').closest('.form-group').show();
                         
                     }
                 }
                 toggleWinProbability();

                 $('#type').change(function() {
                     toggleWinProbability();
                 });
             });
             SCRIPT;
        Admin::script($script);

        if (Session::has('show_alert')) {
            $form->html('<script>
             $(document).ready(function () {
                 alert("الرجاء اختيار نوع  الصوره");
             });
         </script>');
        }
        //        $form->file('img3', trans('video'));
        $form->color('color', trans('color'));
        $form->number('expire', trans('expire(in days)'))->placeholder(trans('0 if permanent'));

        //        $form->number('sort', 'sort');
        $form->number('num', __('num'));

        $form->saving(function (Form $form) {
            $imageType1 = $form->input('image_type1');
            $profileFrameType = $form->input('profile_frame_type');
            $form->model()->image_type = $imageType1 ?? $profileFrameType;

            if (is_null($imageType1) && is_null($profileFrameType)) {

                session()->flash('show_alert', 'Your alert message');
                return redirect()->back();
            }


            (new UserCounterServices)->eventUsers('ware');
        });

        $form->saved(function (Form $form) {
           
            $type = $form->model()->type;
            $url = url('admin/ware-management') . '?type=' . $type;
            return redirect()->to($url);
        });
        return $form;
    }
}
