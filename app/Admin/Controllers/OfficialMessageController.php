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
                $filter->equal('user.uuid',__ ('uuid'));
            });
        });
        $grid->id(__ ('ID'));
        $grid->column('user.name',trans ('user id'))->display(function ($name) {
            $uid = @$this->user->uuid;
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
        $grid->title(trans('title'));
       
      
        $grid->content(__ ('content'));
        $grid->column('img',trans ('img'))->display(function ($img) {
            $defaultImage = asset("images/background_room.jpg");
            $path = getImagePath($img);
            if (!isImageExists(@$path)) {
                $path = $defaultImage;
            }
            $parsedUrl = parse_url($path);
            $correctUrl = isset($parsedUrl['host']) ? $path : url("/$path");

            return "
                    <img src='$correctUrl' style='width: 50px; height: 50px; border-radius: 5px; cursor: pointer;' onclick='openModal(\"$correctUrl\")' />

                    <div id='imageModal' class='modal' style='display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.7); text-align:center;'>
                        <span onclick='closeModal()' style='position:absolute; top:10px; right:20px; font-size:30px; color:white; cursor:pointer;'>&times;</span>
                        <img id='modalImage' style='display:block; margin:auto; max-width:90%; max-height:90%; margin-top:50px; border-radius:5px;' />
                    </div>

                    <script>
                        function openModal(src) {
                            let modal = document.getElementById('imageModal');
                            let modalImage = document.getElementById('modalImage');
                            modal.style.display = 'block';
                            modalImage.src = src;
                        }

                        function closeModal() {
                            document.getElementById('imageModal').style.display = 'none';
                        }

                        // Close modal when clicking outside the image
                        document.getElementById('imageModal').addEventListener('click', function(event) {
                            if (event.target === this) {
                                closeModal();
                            }
                        });
                    </script>
                ";
        });
        $grid->column('url', trans('url'))
        ->display(function ($value) {
            return "<span style='color: blue;'>$value</span>";
        });
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

