<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\Admin;
use App\Models\Ticket;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin as AdminScript;
use Illuminate\Support\Facades\Storage;

class TicketController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'ticket';

    public function index(Content $content)
    {
        return $content
            ->title(trans('Tickets'))
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
            ->title(trans('Tickets'))
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
            ->title(trans('Tickets'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('Tickets'))
            ->body($this->form());
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Ticket);

//        $grid->model ()->where ('status',1);


        $grid->id(__ ('ID_tiket'));
        // $grid->user_id( __ ('ID'));
        $grid->column('user_id', __('User Info'))->display(function () {
            $user = $this->user;
            if (!$user) return '-';

            $name = $user->name;
            $uuid = $user->uuid;
            $phone = $user->phone ?: '-';
            $defaultImage = asset("images/businessman-icon.jpg");
            $avatarPath = @$user->avatar;
            $avatar = getImagePath($avatarPath) ?? $defaultImage;

            if (!isImageExists($avatar)) {
                $avatar = $defaultImage;
            }

            $userUrl = admin_url('users/' . $user->id);

            return "<div style='display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; background: var(--bg-color);'>
                        <img src='$avatar' alt='User Avatar' style='width: 40px; height: 40px; border-radius: 50%;'>
                        <div>
                            <a href='$userUrl' style='color: var(--primary-color); font-weight: bold; text-decoration: none;'>$name</a><br>
                            <span style='color: var(--uuid-color); font-size: smaller;'>UUID: $uuid</span><br>
                            <span style='color: var(--phone-color); font-size: smaller;'>📞 $phone</span>
                        </div>
                    </div>";
        });

        // $grid->column('contact_num',__ ('contact'));
        // $grid->column('problem',__ ('problem'));
        // $grid->column('description',__ ('description'))->limit(15);
        $grid->column('contact_num', __('contact'))->display(function ($description) {
            $limitedDescription = mb_substr($description, 0, 40) . (strlen($description) > 20 ? '...' : '');
            return "<a href='#' class='view-description' data-description=\"" . htmlentities($description) . "\">$limitedDescription</a>";
        });
        $grid->column('problem', __('problem'))->display(function ($description) {
            $limitedDescription = mb_substr($description, 0, 20) . (strlen($description) > 40 ? '...' : '');
            return "<a href='#' class='view-description' data-description=\"" . htmlentities($description) . "\">$limitedDescription</a>";
        });
        $grid->column('description', __('Description'))->display(function ($description) {
            $limitedDescription = mb_substr($description, 0, 40) . (strlen($description) > 40 ? '...' : '');
            return "<a href='#' class='view-description' data-description=\"" . htmlentities($description) . "\">$limitedDescription</a>";
        });
        AdminScript::script("
            $(document).ready(function () {
                $('.view-description').click(function (e) {
                    e.preventDefault();

                    var description = $(this).data('description');

                    $('#modalDescriptionTitle').text('Full Description');
                    $('#modalDescriptionContent').text(description);

                    $('#descriptionModal').modal('show');
                });

                $('.view-image').click(function (e) {
                    e.preventDefault();
                    var imgSrc = $(this).data('img');
                    $('#modalImageContent').attr('src', imgSrc);
                    $('#imageModal').modal('show');
                });
            }); ");

            $grid->column('img', __('img'))->display(function ($img) {

                $defaultImage = asset('images/image.png');
                $url = getImagePath($img) ?? $defaultImage;

                // Check if the image exists
                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }
                return $url;
            })->image('', 30);
        $grid->column('status',__ ('status'))->switch (Common::getSwitchStates ());
//        $grid->admin_id('admin_id');
//        $grid->created_at(trans('admin.created_at'));
//        $grid->updated_at(trans('admin.updated_at'));
        $this->extendGrid ($grid);
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
        $show = new Show(Ticket::findOrFail($id));

        $show->id('ID');
//        $show->user_id('user_id');
        $show->field('contact_num',__ ('contact'));
        $show->field('problem',__ ('problem'));
        $show->field('description',__ ('description'));
        $show->field('img',__ ('img'))->image ('',80);
        $show->field('status',__ ('status'))->using ([0=>"closed",1=>"open"]);
//        $show->admin_id('admin_id');
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
        $form = new Form(new Ticket);

        $form->display('ID');
//        $form->text('user_id', 'user_id');
        $form->text('contact_num', __('contact'));
        $form->text('problem', __('problem'));
        $form->textarea('description', __('description'));
        $form->image('img', __('img'));
        $form->switch('status', __('status'))->states (Common::getSwitchStates ());
//        $form->text('admin_id', 'admin_id');
//        $form->display(trans('admin.created_at'));
//        $form->display(trans('admin.updated_at'));

        return $form;
    }
}
