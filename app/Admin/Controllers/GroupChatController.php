<?php

namespace App\Admin\Controllers;

use App\Models\GroupChat;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;

class GroupChatController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'group-chat';
    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return parent::index($content
            ->title("group Chat")
            ->row(function (Row $row) {
                $row->column(12, $this->grid2());
            })
            ->row(function ($row) {
                $row->column(12, $this->grid());
            }));
    }

    public function chat_settings(Content $content){
        return $content
        ->view('chat_settings');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid2()
    {
        $form = new Box();
        $form->view('admin.grid.common.group-chat');

        return $form;
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
        return parent::show($id,$content
            ->title("group Chat")
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
        return parent::edit($id,$content
            ->title("group Chat")
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
            ->title("group Chat")
            ->body($this->form()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GroupChat);
        $grid->model()->orderByDesc('id');
        $grid->quickSearch();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column('1/2', function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('user', function ($query) use ($input) {
                        $query->where('name', 'like', "%$input%")
                            ->orWhere('uuid', 'like', "%$input%");
                    });
                }, __('User'))->placeholder(__('Search by name or UUID '));
            });
        });
        $grid->id(__('ID'));
        $grid->column ('user.name',__ ('name'))->display (function ($recever){
            $name =  $this->user?->name ?? '';
             $uid = @$this->user?->uuid ?? 0;
             $path = @$this->user?->profile?->avatar;
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
        $grid->text(__('text'));

        $grid->column('created_at', __('Created at'))->sortable()->diffForHumans();
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
        $show = new Show(GroupChat::findOrFail($id));

        $show->id('ID');
        $show->text('text');
        $show->user_id('user_id');
        $show->created_at(trans('admin.created_at'));
        $show->updated_at(trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new GroupChat);

        $form->display('ID');
        $form->text('text', __('text'));
        $form->text('user_id', __('user_id'));
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));

        return $form;
    }
}
