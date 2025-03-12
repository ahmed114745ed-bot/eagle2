<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\Family;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\FamilyUser;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use App\Services\AppFeatureService;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;

class FamilyController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'family';
    public $hiddenColumns = [];

    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("families");
    }

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('families'))
            ->body($this->grid()));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('families'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('families'))
            ->body($this->form()));
    }
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('families'))
            ->body($this->detail($id)));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Family);

        $grid->id(__('ID'));
        $grid->column('image', __('family'))->display(function ($image) {
            $path = @$image;
            $name = $this->name;
            $defaultImage = asset("images/family.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                  <strong>$name</strong><br>
            </div>
        ";
        });
      
        $grid->column('owner.name', __('owner'))->display(function ($name) {
            $uid = @$this->owner->uuid;
            $path = @$this->owner?->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                <div>
                    <strong>$name</strong><br>
                    <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                </div>
            </div>
        ";
        });
        $grid->column('num', __('number of people'));
       
       
        $this->extendGrid($grid);
        $grid->disableExport();
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
        $show = new Show(Family::findOrFail($id));

        $show->id(__('ID'));
        //        $show->is_success('is_success');
        $show->image(__('image'));
        $show->name(__('name'));
        $show->introduce(__('introduce'));
        $show->notice(__('notice'));
        $show->num(__('number of people'));
        $show->user_id(__('user id'));
        $show->speakswitch(__('speak switch'));
        $show->status(__('status'));
        //        $show->update_user_id('update_user_id');
        //        $show->suctime('suctime');
        //        $show->start_time('start_time');
        //        $show->created_at(trans('admin.created_at'));
        //        $show->updated_at(trans('admin.updated_at'));
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
        $form = new Form(new Family);

        $form->display('ID');
        $form->text('name', __('name'))->rules('required');
        $form->text('introduce', __('introduce'))->rules('required');
        $form->text('notice', __('notice'))->rules('required');
        $form->image('image', __('image'));
        $form->text('num', __('number of people'))->rules('required|integer|max:10000')->default('20');
        $form->select('user_id', __('user id'))->options(function ($value) {
            $ops2 = [];
            foreach (User::Where('id', $value)->get() as $user) {
                $ops2[$user->id] = $user->uuid . '_' . $user->name;
            }
            return $ops2;
        })->ajax('/api/search/users4', 'id', 'name')->rules('required');
        $form->hidden('is_success', 'is_success')->default(1)->rules('required');

        $form->saving(function (Form $form) {
            $oldOwnerFamily = $form->model()->user_id;
            $newOwnerFamily = request()->user_id;
            if ($form->model()->exists && ($oldOwnerFamily != $newOwnerFamily)) {
                User::where('id', $form->model()->user_id)->update(['family_id' => 0]);
                FamilyUser::where([
                    'user_id' => $form->model()->user_id,
                    'family_id' => $form->model()->id,
                    'user_type' => 2,
                    'status' => 1,
                ])->delete();
            }
        });
        $form->saved(function (Form $form) {
            $checkFamilyUser = FamilyUser::where([
                'user_id' => $form->model()->user_id,
                'family_id' => $form->model()->id,
                'user_type' => 2,
                'status' => 1,
            ])->exists();
            if (!$checkFamilyUser) {
                User::where('id', $form->model()->user_id)->update(['family_id' => $form->model()->id]);
                FamilyUser::create([
                    'user_id' => $form->model()->user_id,
                    'family_id' => $form->model()->id,
                    'user_type' => 2,
                    'status' => 1,
                ]);
            }
        });

        return $form;
    }
}
