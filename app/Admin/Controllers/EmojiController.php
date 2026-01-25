<?php

namespace App\Admin\Controllers;

use App\Models\Emoji;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\EmojiCategory;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\App;
use App\Admin\Actions\Grid\MoveGroupEmoji;
use App\Admin\Actions\MoveEmojiCategoryAction;
use Encore\Admin\Controllers\HasResourceActions;

class EmojiController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'emoji';
    protected $filterId;

    public function __construct()
    {
        $this->filterId = request('filter');
    }
    public function index(Content $content)
    {
        return $content
            ->title(trans('Emojis'))
            ->body($this->grid());
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
            ->title(trans('Emojis'))
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
            ->title(trans('Emojis'))
            ->body($this->form()->edit($id)));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Emojis'))
            ->body($this->form()));
    }



    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Emoji);

        // Simple columns only
        $grid->column('id', 'ID');
        $grid->column('name', 'Name');
        $grid->column('enable', 'Enable');

        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableActions();
        
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
        $show = new Show(Emoji::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('pid', 'pid');
        $show->field('name', 'name');
        $show->field('emoji', 'emoji');
        $show->field('t_length', 't_length');
        $show->field('enable', 'enable');
        $show->field('sort', 'sort');
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
        $form = new Form(new Emoji);
        $this->disableFormTools($form);
       

        $form->display(__('ID'));
        if (!$form->isEditing()) {
            $form->hidden('emoji_category_id', __('type'))->default(request('filter'));
        }
        $form->select('pid', __('pid'))->options(function () {
            $ops = [0 => 'root'];
            $ps = Emoji::query()->where('enable', 1)->where('pid', 0)->where('id', '!=', $this->id)->get();
            foreach ($ps as $p) {
                $ops[$p->id] = $p->name;
            }
            return $ops;
        });
        $form->text('name', __('name'));
        $form->text('name_en', __('name_en'));
        $form->file('emoji', __('emoji'));
        $form->select('image_type', __('image_type'))->options(
            [
                'svga' => __('svga'),
                'alpha' => __('alpha'),
                'mp4' => __('mp4'),
                'vap' => __('vap'),
                 'png' => __('image:(jpg, jpeg, png,gif, bmp, tiff, svg, webp, mov, avi, wmv, flv, mkv, webm)'),
            ]
        )->required();
        $form->number('t_length', __('t_length'));
        $form->switch('enable', __('enable'))->states(Common::getSwitchStates());
       $form->number('sort', __('sort'));

        $form->saved(function (Form $form) {
            $model = $form->model();
            $type = $form->model()->emoji_category_id;
            $url = url('admin/emojis') . '?filter=' . $type;
            return redirect()->to($url);
        });

        return $form;
    }


    public function gitImage()
    {
        $gifts = Emoji::whereNotNull('emoji')->get();
        foreach ($gifts as $gift) {
            $ImageType =     pathinfo($gift->emoji, PATHINFO_EXTENSION);
            $gift->image_type = $ImageType == 'alpha' ? 'mp4' : $ImageType;
            $gift->save();
        }
        return $gifts;
    }
}
