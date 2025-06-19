<?php

namespace App\Admin\Controllers;

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


class OvipGiftTapController extends MainController
{

    use HasResourceActions;
    public $permission_name = 'vip-gift';

    public function index(Content $content)
    {
        $url = url('/admin/ovip'); // Define your button URL
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
            $ovip = OVip::where('level', request('level'));
        }



        return parent::index($content
            ->title(trans('Privileges'))
            ->row($buttonHTML)
            ->row(function (Row $row) use ($ovip) {
                $row->column(12, $this->tabsComponent($ovip?->privilegs, $ovip?->id, $ovip?->privilegs->first()?->type));
            })
            ->row(function (Row $row) use ($ovip) {
                $type = request('type');

                if (in_array($type, [13, 17, 14, 19, 16, 20, 9, 22, 15])) {
                    $vipPrivilege = VipPrivilege::where('type', $type)->first();
                    $image = getImagePath($vipPrivilege->img1);
                    switch ($type) {
                        case 13:
                            $text = __('hide user country');
                            break;
                        case 17:
                            $text = __('user ender room anonymous');
                            break;
                        case 14:
                            $text = __('user can send vip gift');
                            break;
                        case 19:
                            $text = __('hide visitors to client pages');
                            break;
                        case 16:
                            $text = __('hide room');
                            break;
                        case 20:
                            $text = __('last login');
                            break;
                        case 9:
                            $text = __('user can not kick out from room');
                            break;
                        case 22:
                            $text = __('user can upload Gif image');
                            break;
                        case 15:
                            $text = __('can not ban this user');
                            break;
                        default:
                            $text = null;
                    }
                    //$row->column(12, '<div style="text-align: center; font-size: 48px; font-weight: bold;">' . $text . '</div>');
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


        return $content;
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

        $grid->model()->where('level', $level)->where('get_type', 1)->where('type', $type)->where('is_active_for_vip', 1);

        $grid->id(__('ID'));
        if ($type == 18 ||  $type == 21) {
            $grid->column('color', __('Color'))->display(function ($color) {
                return "<div style='width: 30px; height: 30px; background-color: {$color}; border: 1px solid #ccc; border-radius: 4px;'></div>";
            });
        } else {
            $grid->column('name', __('name'))->display(function ($name) {

                return app()->getLocale() == 'ar' ? $name : $this->name_en;
            });

            $grid->column('price', __('price'));

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

                $editUrl = url("admin/ware-gifts/{$id}/edit");
                $deleteUrl = url("admin/ware-gifts/{$id}");
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

        $form->hidden('level')->value(request('level'));
        $form->hidden('type')->value(request('type'));
        $form->hidden('is_active_for_vip')->value(1);
        $form->hidden('get_type')->value(1);
        $form->hidden('enable')->value(1);
        $id = request()->route('ware_gift');
        $ware = Ware::find($id);
        if ((request('type') && (request('type') != 18 && request('type') != 21)) || ($form->isEditing() && $ware && ($ware->type != 18 && $ware->type != 21))) {

            $form->display('ID');
            $form->text('name', trans('name'));
            $form->text('name_en', trans('Name en'));
            $form->text('title', trans('title'));
            $form->text('title_en', trans('Title en'));


            $form->image('show_img', trans('img'))->name(function ($file) {
                return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
            })->default('1.png');
            $form->file('img2', trans('svg'))
                ->name(function ($file) {
                    return 'svga_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                });

            $form->keyValue('key_json', 'key_json');

            $form->select('image_type1', __('image_type'))->options(
                [
                    'svga' => __('svga'),
                    'alpha' => __('alpha'),
                    'mp4' => __('mp4'),
                    'vap' => __('vap'),
                    'png' => __('png'),

                ]
            )->attribute(['id' => 'image_type1']);

            $form->text('key', trans('key'));
            if (request('type') == 5 || ($form->isEditing() && $ware && ($ware->type == 5))) {
                $form->html('<h1>' . __('padding') . '</h1>');
                $form->decimal('top', __('top'))->default(0);
                $form->decimal('left', __('left'))->default(0);
                $form->decimal('right', __('right'))->default(0);
                $form->decimal('bottom', __('bottom'))->default(0);
            }
        }

        if (request('type') == 18 || request('type') == 21) $form->color('color', trans('color'));
        if (($form->isEditing() && $ware && ($ware->type == 18 || $ware->type == 21))) {

            if ($ware->type == 18 || $ware->type == 21)  $form->color('color', trans('color'));
        }
        if ((request('type') && (request('type') != 18 && request('type') != 21)) || ($form->isEditing() && $ware && ($ware->type != 18 && $ware->type != 21))) {

            $form->saving(function (Form $form) {
                //dd($form->isEditing(),$form->model()->show_img,$form->model()->img2,request('show_img'),request('img2'));
                if (($form->isEditing() && (!($form->model()->show_img) && ! ($form->model()->img2))) && (!request('show_img') && !request('img2'))) {
                    $error = new MessageBag([
                        'title'   => 'Error',
                        'message' => 'Please upload at least one image',
                    ]);

                    return back()->with(compact('error'));
                }

                if (($form->model()->show_img != null && $form->model()->show_img instanceof UploadedFile) || request('show_img')) {

                    $allowedExtensions = ['svga', 'mp4', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg', 'webp', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm',];

                    $ext = strtolower($form->show_img->guessExtension());

                    if (!in_array($ext, $allowedExtensions)) {
                        throw ValidationException::withMessages([
                            'show_img' => ['Invalid file type. Allowed extensions are: ' . implode(', ', $allowedExtensions)],
                        ]);
                    }

                    $form->image_type1 = $ext;
                }

                if ($form->model()->img2 instanceof UploadedFile || request('img2')) {

                    $allowedExtensions = ['svga', 'mp4', 'alpha', 'vap'];

                    $ext = strtolower($form->img2->guessExtension());
                    $originalExt = strtolower($form->img2->getClientOriginalExtension());

                    if ($ext === 'zz' && $originalExt === 'svga') {
                        $ext = 'svga';
                    }

                    if ($ext === 'mp4') {
                        $urlVideo = upload($form->img2);

                        $videoPath = getDriverUrl() . '/' . $urlVideo;

                        $wareId = $form->model()->id;

                        (new FfmpegService())->extract($videoPath, $wareId);

                        $imagePath = (config('app.env') != 'production' ? '' : 'test-') . "frames/" . $wareId . '.jpg';

                        $response = Http::attach(
                            'image',
                            Storage::disk('gcs')->get($imagePath),
                            $wareId . '.jpg'
                        )->post('https://utd-test.utdsoftware.com/api/analyze-media');

                        $responseData = $response->json();

                        if ($response->successful() && isset($responseData['data']['video_type'])) {
                            $ext = strtolower($responseData['data']['video_type']);
                        }
                    }

                    if (!in_array($ext, $allowedExtensions)) {
                        throw ValidationException::withMessages([
                            'img2' => ['Invalid file type. Allowed extensions are: ' . implode(', ', $allowedExtensions)],
                        ]);
                    } else {
                        $form->profile_frame_type = $ext;
                    }
                }
            });
        }
        $form->saving(function (Form $form) {

            $id = $form->model()->id;

            $exists = Ware::where('level', $form->model()->level)
                ->where('type', $form->type)->where('get_type', 1)->when(isset($id), function ($query) use ($id) {
                    $query->where('id', "!=", $id);
                })->exists();

            if ($exists) {
                $error = new \Illuminate\Support\MessageBag([
                    'title' => 'Error',
                    'message' => __('This level and type combination already exists'),
                ]);

                return back()->with(compact('error'));
            }
            if (request('type') != 18 && request('type') != 21) {
                $imageType1        = $form->input('image_type1')        ?? $form->model()->image_type;
                $profileFrameType  = $form->input('profile_frame_type') ?? $form->model()->image_type;

                if ($form->isCreating()) {
                    if (is_null($imageType1) && is_null($profileFrameType)) {
                        session()->flash('show_alert', 'Please choose an image type');
                        return back();
                    }
                }

                $form->model()->image_type = $imageType1 ?? $profileFrameType;
            }

            (new UserCounterServices)->eventUsers('ware');
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


    // private function tabsComponent($privileges, $level, $type)
    // {
    //     $content = new Row();

    //     // Fetch distinct privilege types and names
    //     if (app()->getLocale() == 'en') {

    //         $privilegeTypes = $privileges?->pluck('en_name', 'type')->sortKeys();
    //     } else {
    //         $privilegeTypes = $privileges?->pluck('name', 'type')->sortKeys();
    //     }

    //     $currentType = request()->get('type', $privilegeTypes?->keys()->first());
    //     $alert = false;
    //     if (!$type) {
    //         $alert = true;
    //     }
    //     $box = new Box(content: view('admin.grid.Form.privilegeTabs', [
    //         'types' => $privilegeTypes,
    //         'currentType' => $currentType,
    //         'alert' => $alert,
    //         'level' => $level
    //     ]));

    //     $content->column(12, $box);

    //     return $content;
    // }

    private function tabsComponent($privileges, $level, $type)
    {
        $content = new Row();

        // Fetch distinct privilege types and names
        if (app()->getLocale() == 'en') {
            $privilegeTypes = $privileges?->pluck('en_name', 'type')->sortKeys();
        } else {
            $privilegeTypes = $privileges?->pluck('name', 'type')->sortKeys();
        }

        $currentType = request()->get('type', $privilegeTypes?->keys()->first());

        $alert = !$type;

        // Inject JS to set type param on first load
        if (!request()->has('type') && $privilegeTypes->isNotEmpty()) {
            $firstType = $privilegeTypes->keys()->first();

            \Encore\Admin\Admin::script(<<<SCRIPT
                const url = new URL(window.location.href);
                url.searchParams.set('type', '$firstType');
                window.location.href = url.toString(); // Force reload with type
            SCRIPT);
        }

        $box = new Box(content: view('admin.grid.Form.privilegeTabs', [
            'types' => $privilegeTypes,
            'currentType' => $currentType,
            'alert' => $alert,
            'level' => $level
        ]));

        $content->column(12, $box);

        return $content;
    }
}
