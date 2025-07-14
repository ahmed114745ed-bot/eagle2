<?php

namespace Modules\Vip\Http\Controllers\web;

use App\Admin\Controllers\MainController;
use App\Models\OVip;
use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Support\Str;
use App\Models\VipPrivilege;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Reals\Http\Services\FfmpegService;
use Encore\Admin\Controllers\HasResourceActions;
use Modules\Public\Http\Services\UserCounterServices;
use Modules\Vip\Services\WareSaveService;


class OvipGiftTapController extends MainController
{

    use HasResourceActions;
    public $permission_name = 'vip-gift';

    public function index(Content $content)
    {
        $url = url('/admin/ovip'); 
        $back = __('back');

        $buttonHTML = <<<HTML
        <a href="{$url}" class="btn btn-sm btn-success" style="margin-bottom: 20px;">
            <i class="fa fa-arrow-left"></i> {$back}
        </a>
        HTML;

        $ovip = null;
        if (request('ovip_id')) {
            $ovip = OVip::find(request('ovip_id'));
        } elseif (request('level')) {
            $ovip = OVip::where('level', request('level'))->first(); 
        }

        return parent::index($content
            ->title(trans('Privileges'))
            ->row($buttonHTML)
            ->row(function (Row $row) use ($ovip) {
                $row->column(12, $this->tabsComponent($ovip?->privilegs, $ovip?->id, $ovip?->privilegs->first()?->type));
            })
            ->row(function (Row $row) use ($ovip) {
                $type = request('type');

                $privilegeTypesInfo = [
                    13 => __('hide user country'),
                    17 => __('user ender room anonymous'),
                    14 => __('user can send vip gift'),
                    19 => __('hide visitors to client pages'),
                    16 => __('hide room'),
                    20 => __('last login'),
                    9  => __('user can not kick out from room'),
                    22 => __('user can upload Gif image'),
                    15 => __('can not ban this user'),
                ];
                
                if (isset($privilegeTypesInfo[$type])) {
                    $vipPrivilege = VipPrivilege::where('type', $type)->first();
                    $image = getImagePath($vipPrivilege->img1 ?? '');
                    $text = $privilegeTypesInfo[$type];
                
                    $row->column(12, '
                        <div style="display: flex; align-items: center; justify-content: center; gap: 20px;">
                            <img src="' . $image . '" alt="VIP Image" style="max-height: 60px;">
                            <div style="font-size: 48px; font-weight: bold;">' . $text . '</div>
                        </div>
                    ');
                } else {
                    $row->column(12, $this->gridDynamic($ovip?->level, $ovip?->privilegs->first()?->type));
                }
            }));


    }
    public function create(Content $content)
    {
        return parent::create($content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body($this->form()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('gift'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {

        return parent::edit($id, $content
            ->title(trans('gift'))
            ->body($this->form()->edit($id)));
    }

    protected function gridDynamic($level, $firstType)
    {

        $type = request()->get('type', $firstType);
        $grid = new Grid(new Ware);

        $baseQuery = Ware::query()
        ->where('level', $level)
        ->where('get_type', 1)
        ->where('type', $type)
        ->where('is_active_for_vip', 1);
    
        $grid->model()->setModel($baseQuery->getModel()); 
        $grid->model()->setQuery($baseQuery->toBase());  
        
        $count = (clone $baseQuery)->count();

        $grid->id(__('ID'));
        if ($type == 18 ||  $type == 21) {
            $grid->column('color', __('Color'))->display(function ($color) {
                return "<div style='width: 30px; height: 30px; background-color: {$color}; border: 1px solid #ccc; border-radius: 4px;'></div>";
            });
        } else {
            $grid->column('name', __('name'))->display(function ($name) {

                return app()->getLocale() == 'ar' ? $name : $this->name_en;
            });

            $grid->column('show_img', __('show_img'))->display(function ($path) {
                /** @var Ware $this */
                $defaultImage = asset("images/image.png");
                $url = getImagePath($path) ?? $defaultImage;
                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }
                return handleShowImageWithTypes($this->id, $url, 101, 50);
            });
            $grid->column('img2', __('show_img'))->display(function ($path) {
                /** @var Ware $this */
                $defaultImage = asset("images/image.png");
                $url = getImagePath($path) ?? $defaultImage;
                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }
                return handleShowImageWithTypes($this->id, $url, 101, 50);
            });


            $grid->title(__('title'))->display(function ($name) {

                return app()->getLocale() == 'ar' ? $name : $this->title_en;
            });;
        }

        if (Admin::user()->can('delete-' . $this->permission_name) || Admin::user()->can('*') || Admin::user()->can('edit-' . $this->permission_name)) {
            $permission = $this->permission_name;
            $grid->column('actions', __('Actions'))->display(function () use ($type, $permission) {

                $id = $this->id;

                $editUrl = admin_url("ware-gifts/{$id}/edit");
                $deleteUrl = admin_url("ware-gifts/{$id}");
                $csrf = csrf_token();

                $editText = __('admin.edit');
                $deleteText = __('admin.delete');
                $confirmText = __('Are you sure?');

                $editBtn = '';
                $deleteBtn = '';

                // Check permission for EDIT button
                if (\Admin::user()->can('edit-' . $permission) || \Admin::user()->can('*')) {
                    $editBtn = <<<HTML
            <a href="{$editUrl}" class="btn btn-xs btn-primary" style="margin-right: 5px">
                <i class="fa fa-edit"></i> {$editText}
            </a>
        HTML;
                }

                // Check permission for DELETE button
                if (\Admin::user()->can('delete-' . $permission) || \Admin::user()->can('*')) {
                    $deleteBtn = <<<HTML
            <form action="{$deleteUrl}" method="POST" style="display:inline-block;" onsubmit="return confirm('{$confirmText}')">
                <input type="hidden" name="_token" value="{$csrf}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-xs btn-danger">
                    <i class="fa fa-trash"></i> {$deleteText}
                </button>
            </form>
        HTML;
                }

                return $editBtn . $deleteBtn;
            })->style('min-width:120px')->setAttributes(['style' => 'text-align:center']);
        }

        $grid->disableActions();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
        });
        $grid->disableCreateButton();
        $this->extendGrid($grid);
        $grid->disableExport();
        if ($firstType && (Admin::user()->can('create-' . $this->permission_name) || Admin::user()->can('*'))) {

            if ($count < 1){
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
            }

        }

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
    }


    protected array $allowedImageExts = [
        'svga', 'mp4', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg', 'webp', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm'
    ];

    protected array $allowedImg2Exts = ['svga', 'mp4', 'alpha', 'vap', 'png'];

    protected function form()
    {
        $form = new Form(new Ware());
        $this->disableFormTools($form);

        $form->hidden('level')->value(request('level'));
        $form->hidden('type')->value(request('type'));
        $form->hidden('is_active_for_vip')->value(1);
        $form->hidden('get_type')->value(1);
        $form->hidden('enable')->value(1);

        $type = request('type');
        $isColorType = in_array($type, [18, 21]);
        $ware = $form->model();

        if (!$isColorType) {
            $form->display('ID');
            $form->text('name', trans('name'));
            $form->text('name_en', trans('Name en'));
            $form->text('title', trans('title'));
            $form->text('title_en', trans('Title en'));

            $form->image('show_img', trans('img'))->name(fn($file) => now()->timestamp . rand(0, 999) . '.' . $file->guessExtension())->default('1.png');
            $form->file('img2', trans('svg'))->name(fn($file) => 'svga_' . Str::random(6) . '.' . $file->getClientOriginalExtension());

            $form->keyValue('key_json', 'key_json');

            if ($form->isEditing()) {
                $form->select('image_type1', __('image_type'))->options([
                    'svga' => __('svga'),
                    'alpha' => __('alpha'),
                    'mp4' => __('mp4'),
                    'vap' => __('vap'),
                    'png' => __('png'),
                ])->attribute(['id' => 'image_type1']);
            }

            $form->text('key', trans('key'));

            if ($type == 5 || ($form->isEditing() && $ware->type == 5)) {
                $form->html('<h1>' . __('padding') . '</h1>');
                $form->decimal('top', __('top'))->default(20);
                $form->decimal('left', __('left'))->default(15);
                $form->decimal('right', __('right'))->default(15);
                $form->decimal('bottom', __('bottom'))->default(15);
            }
        }

        if ($isColorType) {
            $form->color('color', trans('color'));
        }

        $form->saving(function (Form $form) {
            (new WareSaveService())->handle($form);

        });

        $form->saved(function (Form $form) {
            $level = $form->model()->level;
            $type = $form->model()->type;
            $url = url('admin/ovip-gift/' . $form->model()->vip->id) . '?type=' . $type;
            return redirect()->to($url);
        });

        return $form;
    }




    private function tabsComponent($privileges, $level, $type)
    {
        $content = new Row();
    
        $privilegeTypes = app()->getLocale() === 'en'
            ? $privileges?->pluck('en_name', 'type')->sortKeys()
            : $privileges?->pluck('name', 'type')->sortKeys();
    
        $hasTypes = $privilegeTypes->isNotEmpty();
    
        $currentType = request()->get('type', $privilegeTypes?->keys()->first());
    
        $alert = !$type;
    
        if (!$type && $hasTypes && !request()->has('type')) {
            $firstType = $privilegeTypes->keys()->first();
    
            \Encore\Admin\Admin::script(<<<'SCRIPT'
                const url = new URL(window.location.href);
                url.searchParams.set('type', '{$firstType}');
                window.location.href = url.toString(); // Reload with type
            SCRIPT);
        }
    
        $box = new Box(
            content: view('admin.grid.Form.privilegeTabs', [
                'types' => $privilegeTypes,
                'currentType' => $currentType,
                'alert' => $alert,
                'level' => $level,
            ])
        );
    
        $content->column(12, $box);
    
        return $content;
    }
    
}
