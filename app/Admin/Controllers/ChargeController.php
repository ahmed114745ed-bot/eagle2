<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\Charge;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use App\Admin\Actions\ChargeAction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Controllers\HasResourceActions;

class ChargeController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'charge';


    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        if (! \Encore\Admin\Facades\Admin::user()->can('*')) {
            Permission::check('browse-' . $this->permission_name);
        }
        return $content
            ->title(trans('charges'))
            ->body($this->grid());
            // ->row(function ($row) {
            //     $row->column(10, $this->grid());
            //     $row->column(2, view('admin.grid.users.actions'));
            // });
    }




    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Charge);
        $grid->model()->orderByDesc('id');
        $grid->id(__('ID'));
        $grid->filter(function ($filter) {
            // إلغاء الفلاتر الافتراضية
            $filter->disableIdFilter();

            // فلتر "من تاريخ إلى تاريخ" على عمود created_at
            $filter->between('created_at', __('Filter by date'))->date();


            $filter->column('1/2', function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input; // Retrieve the selected value
                    $query->where('user_type', $input);
                }, __('User Type'))->select([
                    'dashdash' => __(trans('Users')),
                    'dash' => __(trans('Agency')),
                ]);
            });



            $filter->expand();
        });


        $grid->column('user_id', __('User'))->display(function ($userId) {
            if ($this->user_type == "dash") {
                $agency = \App\Models\Agency::find($this->agency_id);
                $img = $agency ? getDriverUrl() . '/' . $agency->img : null;
                $id = $agency->id ?? 0;
                $type = "agency";
            } else {
                $user = \App\Models\User::find($userId);
                $img = getDriverUrl() . '/' . @$user->profile?->avatar ?? '';
                $id = @$user->uuid ?? 0;
                $type = "user";
            }
            return "<img src='$img' style='width: 50px; height: 50px; border-radius: 50%;' /> <br> uid: #{$id} <br> type: #{$type}";
        });
        $grid->column('admin.name', __('creator'))
            ->display(function ($name) {
                if ($this->user_type != "dash") {
                    $name = $this->admin->name ?? '';
                    $path = $this->admin->avatar ?? null;
                    $defaultImage = asset("images/businessman-icon.jpg");
                    $url = getImagePath($path) ?? $defaultImage;

                    // Check if the image exists
                    if (!isImageExists($url)) {
                        $url = $defaultImage;
                    }

                    $image = handleShowImageWithTypes($this->id, $url, 40, 40);

                    // Validate admin existence before accessing id
                    $showUrl = '#'; // Default to prevent broken links
                    if ($this->admin && $this->admin->id) {
                        $showUrl = url("admin/auth/users/{$this->admin->id}");
                    }

                    return "
                     <div style='display: flex; align-items: center; gap: 10px;'>
                         <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                             $image
                             <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                         </a>
                     </div>
                     ";
                } else {
                    $user = $this->sender;
                    $img = getDriverUrl() . '/' . @$user->profile?->avatar ?? '';
                    $id = @$user->uuid ?? 0;
                    $type = "user";
                    return "<img src='$img' style='width: 50px; height: 50px; border-radius: 50%;' /> <br> uid: #{$id} <br> type: #{$type}";
                }
            });
        // $grid->column('user_type', __('User Type'))->using([
        //     'app' => __('app'),
        //     'dash' => __('office')
        // ]);

        $grid->column('amount', __('Coins'))->display(function ($usd) {

            $image = asset('images/coin.png'); // تأكد من أن الصورة موجودة

            return "<div style='display: flex; align-items: center; gap: 5px;'>
                        <span>{$usd}</span>
                        <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });

        $grid->column('balance_before', __('balance before'))->display(function ($usd) {

            $image = asset('images/coin.png'); // تأكد من أن الصورة موجودة

            return "<div style='display: flex; align-items: center; gap: 5px;'>
                        <span>{$usd}</span>
                        <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });

        // $grid->amount(__('coins'));
        $grid->column('created_at', trans('admin.created_at'));

        $grid->tools(function (Grid\Tools $tools) {
            $buttons = '<span style="display: inline-flex; gap: 10px;">'
                . (new ChargeAction())->render()
                . '</span>';

            $tools->append($buttons);
        });

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableExport();

        $this->extendGrid($grid);

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
        $show = new Show(Charge::findOrFail($id));

        //        $show->id('ID');
        //        $show->charger_id('charger_id');
        //        $show->charger_type('charger_type');
        //        $show->user_id('user_id');
        //        $show->user_type('user_type');
        //        $show->amount('amount');
        //        $show->amount_type('amount_type');
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
        $form = new Form(new Charge);

        //        $form->display('ID');
        //        $form->text('charger_id', 'charger_id');
        //        $form->text('charger_type', 'charger_type');
        //        $form->text('user_id', 'user_id');
        //        $form->text('user_type', 'user_type');
        //        $form->text('amount', 'amount');
        //        $form->text('amount_type', 'amount_type');
        //        $form->display(trans('admin.created_at'));
        //        $form->display(trans('admin.updated_at'));

        return $form;
    }
}
