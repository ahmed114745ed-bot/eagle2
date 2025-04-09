<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\VipPrivilege;
use Encore\Admin\Layout\Content;
use App\Services\AppFeatureService;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;

class VipPrivilegeController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'vip-privilege';
    public $hiddenColumns = [

    ];

    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("vips");
    }

    public function index(Content $content)
    {
        return $content
            ->title(trans('vip_privilege'))
            ->body($this->grid());
    }

    public function show($id, Content $content)
    {
        return $content
            ->title(trans('vip_privilege'))
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
            ->title(trans('vip_privilege'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('vip_privilege'))
            ->body($this->form());
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
 protected function grid()
    {
        $grid = new Grid(new VipPrivilege);

        $grid->id(__('admin.ID'));
        $grid->column('name',__('name'));
        $grid->column('en_name',__('en_name'));
        $grid->column('title',__ ('title'));
        $grid->column('type');
        $grid->column('img1',__ ('img'))->image ('',30);
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $this->extendGrid ($grid);
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
        $show = new Show(VipPrivilege::findOrFail($id));

//        $show->id('ID');
//        $show->name('name');
//        $show->title('title');
//        $show->img1('img1');
//        $show->img2('img2');
//        $show->created_at(trans('admin.created_at'));
//        $show->updated_at(trans('admin.updated_at'));
        $this->extendShow ($show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new VipPrivilege);

        // $form->display(__('admin.ID'));
        $form->text('name', __('name'));
        $form->text('en_name', __('en_name'));
        $form->text('title', __('title'));
        $form->select('type', __('type'))->options (
            [
                // 1=>trans ('Gemstone'),=========
                // 3=>trans ('Card Scroll'),
                4=>trans ('Avatar Frame'),
                5=>trans ('Bubble Frame'),
                6=>trans ('Entering Special Effects'),
                // 7=>trans ('Microphone Aperture'),============
                8=>trans ('Badge'),
                9=>trans ('NoKick'),
                10=>trans ('Icon'),
                // 11=>trans ('intro animation'),======
                12=>trans ('wapel'),
                13=>trans ('hide country'),
                14=>trans ('vip gifts'),
                15=>trans ('no pan'),
                16=>trans ('hidden room'),
                17=>trans ('anonymous man'),
                18=>trans ('colored name'),
                19=>trans ('profile visitors hide in'),
                20=>trans ('last login'),
                21=>trans ('sound effect'),
                22=>trans('upload GIF image'),
                28=> trans('profile frame')            
            ]
        );
        $form->file('img1', __('admin.img'));
        $form->file('img2', __('admin.img2'));
//        $form->display(trans('admin.created_at'));
//        $form->display(trans('admin.updated_at'));

        return $form;
    }
}
