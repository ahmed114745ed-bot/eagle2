<?php

namespace App\Admin\Controllers;

use App\Models\OVip;
use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use App\Helpers\Common;
use App\Models\VipPrivilege;
use Illuminate\Support\Str;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Support\Facades\Session;
use Encore\Admin\Controllers\HasResourceActions;
use Modules\Public\Http\Services\UserCounterServices;
use Encore\Admin\Widgets\Box;


class OvipGiftTapController extends MainController
{

    use HasResourceActions;
    public $permission_name = 'ovip-gift';

    public function index(Content $content)
    {
        $url = url('/admin/ovip'); // Define your button URL
        $back = __('back');
        $buttonHTML = <<<HTML
    <a href="{$url}" class="btn btn-sm btn-success" style="margin-bottom: 20px;">
        <i class="fa fa-arrow-left"></i> {$back}
    </a>
    HTML;
        if (request('ovip_id')) {
            $ovip = OVip::find(request('ovip_id'));
        } elseif (request('level')) {
            $ovip = OVip::where('level', request('level'));
        }

        return parent::index($content
            ->title(trans('Privileges'))
            ->row($buttonHTML)
            ->row(function (Row $row) use ($ovip) {
                $row->column(12, $this->tabsComponent($ovip?->privilegs));
            })
            ->row(function (Row $row) use ($ovip) {
                $row->column(12, $this->gridDynamic($ovip?->level, $ovip?->privilegs->first()?->type));
            }));


        // Dynamically add rows for each level
        // foreach ($ovip->privilegs as $privileg) {
        //     $content = $content
        //         ->header(trans('admin.index'))

        //         ->row($buttonHTML);
        //     $content->row($this->gridDynamic($ovip->level, $privileg->type));
        // }

        return $content;
    }
    public function create(Content $content)
    {
        return $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body($this->form());
    }

    public function edit($id, Content $content)
    {

        return $content
            ->title(trans('gift'))
            ->body($this->form()->edit($id));
    }

    protected function gridDynamic($level, $firstType)
    {


        $type = request()->get('type', $firstType);
        $grid = new Grid(new Ware);
        $grid->model()->where('level', $level)->where('get_type', 1)->where('type', $type)->where('is_active_for_vip', 1);

        $grid->id(__('ID'));
        $grid->column('name', __('name'));

        $grid->column('price', __('price'));

        $grid->column('show_img', __('show_img'))->image('', 30);
        $grid->column('img2', __('show_img'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });


        $grid->title(__('title'));
        $grid->expire(__('expire'));


        $grid->column('actions', __('Actions'))->display(function () use ($type) {
            $id = $this->id;

            $editUrl = url("admin/ware-gifts/{$id}/edit");
            $deleteUrl = url("admin/ware-gifts/{$id}");
            $csrf = csrf_token();

            $editText = __('admin.edit');
            $deleteText = __('admin.delete');
            $confirmText = __('Are you sure?');

            return <<<HTML
                <a href="{$editUrl}" class="btn btn-xs btn-primary" style="margin-right: 5px">
                    <i class="fa fa-edit"></i> {$editText}
                </a>
                <form action="{$deleteUrl}" method="POST" style="display:inline-block;" onsubmit="return confirm('{$confirmText}')">
                    <input type="hidden" name="_token" value="{$csrf}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-xs btn-danger">
                        <i class="fa fa-trash"></i> {$deleteText}
                    </button>
                </form>
            HTML;
        })->style('min-width:120px')->setAttributes(['style' => 'text-align:center']);


        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
        });
        $grid->disableCreateButton();
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->tools(function (Grid\Tools $tools) use ($level, $type,) {
            $level = $level ?? request('level');
            $url =    url('admin/ware-gift/' . $level . '/' . $type);
            $add = __('add');

            $customButtonHTML = <<<HTML

            <a href="{$url}" class="btn btn-sm btn-success" style="margin-right: 10px;">
                    <i class="fa fa-plus"></i> {$add}
                </a>

            HTML;
            $tools->append($customButtonHTML);
        });

        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");
        return $grid;
    }
    public function destroy($id)
    {
        $ware = Ware::where('id', $id)->first();
        $ovip = Ovip::where('level', $ware->level)->first();
        $type = $ware->type;
        $ware->delete();
        $url = url('admin/ovip-gift/' . $ovip->id) . '?type=' . $type;
        return redirect()->to($url);
        return $this->form()->destroy($id);
    }


    protected function form()
    {
        $form = new Form(new Ware());
         $form->setAction(admin_url("ware-gift/store". "/" . request('level') . "/" . request('type')));

        $form->hidden('level')->value(request('level'));
        $form->hidden('type')->value(request('type'));
        $form->hidden('is_active_for_vip')->value(1);
        $form->hidden('get_type')->value(1);

        $form->display('ID');
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

        $form->select('profile_frame_type', __('profile_frame_type'))->options(
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

            $id = $form->model()->id;

            $exists = Ware::where('level', $form->level)
                ->where('type', $form->type)->when(isset($id), function ($query) use ($id) {
                    $query->where('id', "!=", $id);
                })->exists();
            if ($exists) {
                $error = new \Illuminate\Support\MessageBag([
                    'title' => 'Error',
                    'message' => __('This level and type combination already exists'),
                ]);

                // return back()->with(compact('error'));
            }
           
            $imageType1 = $form->input('image_type1');
            $profileFrameType = $form->input('profile_frame_type');
            $form->model()->image_type = $imageType1 ?? $profileFrameType;

            if (is_null($imageType1) && is_null($profileFrameType)) {

                session()->flash('show_alert', 'Your alert message');
                // return redirect()->back();
            }

         
            // (new UserCounterServices)->eventUsers('ware');
        });

        $form->saved(function (Form $form) {
            $level = $form->model()->level;

            $type = $form->model()->type; // Get the saved model's ID
            
            $ovip = Ovip::where('level', $level)->first();
            $url = url('admin/ovip-gift/' . $ovip->id) . '?type=' . $type;
         
            return redirect()->to($url);
        });


        return $form;
    }


    private function tabsComponent($privileges)
    {
        $content = new Row();

        // Fetch distinct privilege types and names
        if ($privileges) {
            if (app()->getLocale() == 'en') {
                $privilegeTypes = $privileges->pluck('en_name', 'type')->sortKeys();
            } else {
                $privilegeTypes = $privileges->pluck('name', 'type')->sortKeys();
            }
        } else {
            $privilegeTypes = collect(); 
        }

        // Default to the first type if none is selected
        $currentType = request()->get('type', $privilegeTypes->keys()->first());

        $box = new Box(content: view('admin.grid.Form.privilegeTabs', [
            'types' => $privilegeTypes,
            'currentType' => $currentType
        ]));

        $content->column(12, $box);

        return $content;
    }
}
