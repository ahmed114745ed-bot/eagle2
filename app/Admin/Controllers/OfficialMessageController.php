<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use App\Models\OfficialMessageAdmin;
use Encore\Admin\Controllers\HasResourceActions;
use App\Models\OfficialMessageAdmin as OfficialMessage;

class OfficialMessageController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'official-messages';

    public function index(Content $content)
    {
        return $content
            ->title(trans('Official messages'))
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
        return $content
            ->title(trans('official-messages'))
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
            ->title(trans('official-messages'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('official-messages'))
            ->body($this->form());
    }



    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OfficialMessage);
        $grid->model ()->where ('type',2);
        $grid->filter (function (Grid\Filter $filter){
            $filter->expand ();
            $filter->column(1/2, function ($filter) {
                $filter->equal('user_id',__ ('uuid'));
            });
        });
        $grid->id(__ ('ID'));
        $grid->title(trans('title'));
        $grid->column('img',trans ('img'))->image ('',30);
        $grid->column('user_id',trans ('user id'));
        $grid->content(__ ('content'));
//        $grid->column('type',trans ('type'))->select (
//            [
//                1=>trans('system message'),
//                2=>trans('system announcement')
//            ]
//        );
        $grid->url( __ ('url'));
        $grid->created_at(trans('admin.created_at'));
        $grid->disableExport();

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
        $show = new Show(OfficialMessage::findOrFail($id));

//        $show->id('ID');
//        $show->title(trans('title'));
//        $show->img('img');
//        $show->user_id('user_id');
//        $show->content('content');
//        $show->type('type');
//        $show->url('url');
        $this->extendShow ($show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function search(Request $request)
    {
        $search = $request->get('q');

        $users = User::where('uuid', 'like', "%$search%")->get();

        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id' => $user->uuid,
                'text' => $user->uuid . '--' . ($user->nicename ?: $user->name),
            ];
        }

        return response()->json($results);
    }

    protected function form()
    {
        $form = new Form(new OfficialMessageAdmin);

        $form->display('ID');
        $form->text('title', __ ('title'))->rules('nullable|max:255');;
        $form->image('img', __ ('img'));
        $form->select('user_id', __('user'))->options(function ($search) {
            $ops = [0 => __('all')];
            return $ops;
        })->options('/api/search/users2')->ajax('/api/search/users2', 'id', 'name','uuid');
        $form->text('content', __ ('content'));
        $form->select('type', __ ('type'))->options (
            [
                2=>trans(__ ('official message'))
            ]
        )->default (2);
        $form->text('url', __ ('url'));


        return $form;
    }
}

