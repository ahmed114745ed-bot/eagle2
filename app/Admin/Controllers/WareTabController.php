<?php

namespace App\Admin\Controllers;

use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use App\Helpers\Common;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Session;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\HasResourceActions;
use Illuminate\Validation\ValidationException;
use Modules\Public\Http\Services\UserCounterServices;
use Modules\Reals\Http\Services\FfmpegService;


class WareTabController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'store';
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
        return parent::show($id, $content
            ->title(trans('wares'))
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
        // Get the warehouse first to ensure it exists
        // $ware = Ware::findOrFail($id);

        // // Get type from request or fall back to warehouse's type
        // $currentType = request('type', $ware->type);

        return parent::edit($id, $content
            ->title(trans('wares'))
            // ->row(function (Row $row) use ($id, $currentType) {
            //     $row->column(12, $this->tabsComponentEdit($id, $currentType));
            // })
            ->row(function (Row $row) use ($id) {
                $row->column(12, $this->form($id)->edit($id));
            }));
    }

    public function create(Content $content)
    {
        return  parent::create($content
            ->title(trans('wares'))
            ->row(function (Row $row) {
                $row->column(12, $this->tabsComponentCreate());
            })
            ->row(function (Row $row) {
                $row->column(12, $this->form());
            }));
        //  ->body($this->form());
    }

    protected function grid()
    {
        $type = request()->get('type', 4);
        $grid = new Grid(new Ware());
        //  $types = [6, 4, 5];
        $grid->model()->where('type',  $type)->whereNot('get_type', 1);


        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
        });

        $grid->id(__('ID'));
        $grid->column('name', __('name'))->editable();
        if (Admin::user()->can('edit_ware_price') || Admin::user()->can('*')) {
            $grid->column('price', __('price'))->editable();
            $grid->column('enable', __('enable'))->switch(Common::getSwitchStates());
        } else {
            $grid->column('price', __('price'));
        }

        $grid->column('show_img', __('show_img'))->display(function ($path) {
            /** @var Ware $this */
            $defaultImage = asset("images/image.png");

            $url = getImagePath($path) ?? $defaultImage;
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('img2', __('show_img'))->display(function ($path) {
            /** @var Ware $this */
            $defaultImage = asset("images/image.png");

            $url = getImagePath($path) ?? $defaultImage;
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
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
        $grid->tools(function (Grid\Tools $tools) use ($type) {
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
        $typeMap = SELECTED_USED_WARE;

        $types =  collect($typeMap);
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

        // $types = Ware::whereIn('type', array_keys($typeMap))->distinct()->pluck('type')->sort()->mapWithKeys(function ($type) use ($typeMap) {
        //     return [$type => $typeMap[$type] ?? "Type $type"];
        // });

        $types = collect($typeMap);


        $currentType = request()->get('type', $types->keys()->first());

        $box = new Box(content: view('admin.grid.Form.wareCreate', [
            'types' => $types,
            'currentType' => $currentType
        ]));

        $content->column(12, $box);

        return $content;
    }

    public function update($id)
    {
        $request = request();

        $toggleFields = ['enable', 'is_active_for_vip'];

        $editableField = collect($request->except(['_token', '_method', '_edit_inline']))->keys()->first();

        if ($request->ajax() && $request->has('_edit_inline') && in_array($editableField, $toggleFields)) {
            $field = array_key_first($request->all());

            if (in_array($field, $toggleFields)) {
                $model = Ware::findOrFail($id);
                $model->$field = $request->input($field);
                $model->save();

                return response()->json([
                    'status' => true,
                    'message' => __('Updated successfully')
                ]);
            }
        }

        return parent::update($id);
    }



    protected function form($id = null)
    {
        $form = new Form(new Ware());
        $form->display('ID');

        $form->select('get_type', trans('get_type'))->options(
            translate(GET_TYPE_WARE)
        )->default(4);
        if (\Str::contains(request()->fullUrl(), 'edit')) {

            $wareType = Ware::find($id)->type;

            $form->hidden('type', __('type'))->value($wareType)->attribute(['id' => 'type']);
        }
        else {
            if (request('type')){
                $form->hidden('type', __('type'))->value(request('type'))->attribute(['id' => 'type']);
            }
        }
        if (!$form->isEditing()) {
            if (request('type')){
                $form->hidden('type', __('type'))->value(request('type'))->attribute(['id' => 'type']);
            }
        }
        $form->text('name', trans('name'));
        $form->text('name_en', trans('Name en'));
        $form->text('title', trans('title'));
        $form->text('title_en', trans('Title en'));
        $form->currency('price', __('price'));
                $form->switch('enable', trans('enable'))->states(Common::getSwitchStates());
        // // if (!$form->isEditing()) {
        // //     if (Admin::user()->can('add_ware_price') || Admin::user()->can('*')) {
        // //         $form->currency('price', __('price'));
        // //         $form->switch('enable', trans('enable'))->states(Common::getSwitchStates());
        // //     }
        // // }
        // if ($form->isEditing()) {

        //     if (Admin::user()->can('edit_ware_price') || Admin::user()->can('*')) {

        //     }
        // }
        //        $form->number('score', trans('score'));
        $form->number('level', trans('level'));
        $states = [
            'on' => ['value' => 1, 'text' => 'open', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => 'close', 'color' => 'default'],
        ];
        $form->switch('is_active_for_vip', __("active vip"))->states($states);
        $form->number('exp', __('exp'));


        //        $form->image('img1', trans('img'));
        $form->image('show_img', trans('img'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        })->default('1.png');
        $form->file('img2', trans('svg'))
            ->name(function ($file) {
                return 'svga_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            });


        if ($form->isEditing()) {
            $form->select('image_type1', __('image_type'))->options(
                [
                    'svga' => __('svga'),
                    'alpha' => __('alpha'),
                    'mp4' => __('mp4'),
                    'vap' => __('vap'),
                    'png' => __('png'),

                ]
            )->attribute(['id' => 'image_type1']);

            // $form->select('profile_frame_type', __('image_type'))->options(
            //     [
            //         'svga' => __('svga'),
            //         'png' => __('png'),

            //     ]
            // )->attribute(['id' => 'profile_frame']);
        }



        $form->text('key', trans('key'));
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

        if ($form->isEditing()){
            if (Session::has('show_alert')) {
                $form->html('<script>
                 $(document).ready(function () {
                     alert("الرجاء اختيار نوع  الصوره");
                 });
             </script>');
            }
        }

        //        $form->file('img3', trans('video'));
        $form->color('color', trans('color'));
        $form->number('expire', trans('expire(in days)'))->placeholder(trans('0 if permanent'));

        //        $form->number('sort', 'sort');
        $form->number('num', __('num'));

        if (request('type') == 18) $form->color('color', trans('color'));
        if (request('type') == 5) {
            $form->html('<h1>' . __('padding') . '</h1>');
            $form->decimal('top', __('top'))->default(0);
            $form->decimal('left', __('left'))->default(0);
            $form->decimal('right', __('right'))->default(0);
            $form->decimal('bottom', __('bottom'))->default(0);
        }
        if (request('type') != 18) {
            $form->saving(function (Form $form) {

                if (!$form->show_img && !$form->img2) {
                    $error = new MessageBag([
                        'title'   => 'Error',
                        'message' => 'Please upload at least one image',
                    ]);

                    return back()->with(compact('error'));
                }

                if ($form->show_img instanceof UploadedFile) {
                    $allowedExtensions = ['svga', 'mp4', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg', 'webp', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm',];

                    $ext = strtolower($form->show_img->guessExtension());

                    if (!in_array($ext, $allowedExtensions)) {
                        throw ValidationException::withMessages([
                            'show_img' => ['Invalid file type. Allowed extensions are: ' . implode(', ', $allowedExtensions)],
                        ]);
                    }

                    $form->image_type1 = $ext;
                }

                if ($form->img2 instanceof UploadedFile) {

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
                        $form->input('detected_profile_frame_type', $ext);
                        $form->profile_frame_type = $ext;
                    }
                }
            });
        }
        $form->saving(function (Form $form) {
            $isEditing = $form->isEditing();

            $imageType1 = $form->input('image_type1');
            $profileFrameType = $form->input('profile_frame_type') ?? $form->input('detected_profile_frame_type');

            $final = $imageType1 ?? $profileFrameType;

            if ($isEditing && is_null($final)) {
                session()->flash('show_alert', 'الرجاء اختيار نوع الصوره');
                return redirect()->back();
            }

            $form->model()->image_type = $final;
        });



        $form->saved(function (Form $form) {

            $type = $form->model()->type;
            $url = url('admin/ware-management') . '?type=' . $type;
            return redirect()->to($url);
        });
        return $form;
    }




    private function tabsComponentEdit($id, $currentType)
    {
        $content = new Row();

        // Define your type mapping
        $typeMap = TYPE_WARE;

        $types = Ware::whereIn('type', array_keys($typeMap))
            ->distinct()
            ->pluck('type')
            ->sort()
            ->mapWithKeys(function ($type) use ($typeMap) {
                return [$type => $typeMap[$type] ?? "Type $type"];
            });

        $box = new Box(content: view('admin.grid.Form.wareEdit', [
            'types' => $types,
            'currentType' => $currentType,
            'wareId' => $id
        ]));

        $content->column(12, $box);

        return $content;
    }
}
