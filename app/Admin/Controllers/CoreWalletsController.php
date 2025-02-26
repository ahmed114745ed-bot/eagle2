<?php

namespace App\Admin\Controllers;

use App\Models\CoreWallets;
use Encore\Admin\Layout\Content;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Row;
use Illuminate\Support\HtmlString;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;

class CoreWalletsController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */


    public $permission_name = 'core-wallets';

    // public function index(Content $content)
    // {
    //     return parent::index($content
    //         ->title(trans('Application wallet'))
    //         ->row(function (Row $row) {
    //             $row->column(12, view('admin.grid.common.CoreWallet'));
    //         })
    //         // ->row(function (Row $row) {
    //         //     $row->column(12, $this->grid());
    //         // })
    //     );
    // }




    public function index(Content $content)
    {
        $coreWallets = \App\Models\CoreWallets::whereIn('name', ['app_wallet', 'owner_wallet', 'game_wallet', 'lucky_box'])->get();

        $icons = [
            'app_wallet' => 'fa-solid fa-coins',       // Coins icon
            'owner_wallet' => 'fa-solid fa-user-tie',  // Business user icon
            'game_wallet' => 'fa-solid fa-dice',       // Dice for gaming
            'lucky_box' => 'fa-solid fa-box-open'      // Open gift box
        ];

        $form = '<div class="container mt-4">';
        $form .= '<div class="row justify-content-center g-4">'; // Added Bootstrap gutter space

        foreach ($coreWallets as $index => $wallet) {
            $icon = $icons[$wallet->name] ?? 'fa-solid fa-wallet';

            $form .= '<div class="col-md-5 col-lg-5 mb-4 px-3">'; // Added padding for space
            $form .= '<div class="card shadow-lg position-relative border-0" 
          style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg,rgb(211, 211, 183),rgb(202, 211, 193)); transition: transform 0.3s ease-in-out; margin-bottom: 20px;">';


            // Card hover effect
            $form .= '<style>
                .card:hover { transform: scale(1.05); }
              </style>';

            // Edit Button
            $form .= '<a  href="' . admin_url('core-wallets/' . $wallet->id . '/edit') . '" 
            class="btn btn-primary bg-primary position-absolute top-0 end-0 m-2 rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
            style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; color:rgb(255, 255, 255); font-size: 2rem;"
            data-bs-toggle="tooltip" data-bs-placement="top" title="' . __('Edit') . '">
            <span class="fa-solid">' . __('Edit') . '</span> 
            </a>';

            // Card Body
            $form .= '<div class="card-body text-center p-4">';

            // Icon at the top
            $form .= '<div class="mb-3">';
            $form .= '<i class="' . $icon . ' text-primary fs-1"></i>';
            $form .= '</div>';

            // Wallet Name & Coins
            $form .= '<h5 class="fw-bold mb-2" style="color: #000000;">' . ucfirst($wallet->name) . '</h5>';
            $form .= '<p class="fs-5 fw-semibold" style="color: #000000;">' . __('Coins') . ' : ' . number_format($wallet->coins) . '</p>';


            $form .= '</div>'; // End card-body

            // Last Updated in Bottom-Left Corner
            $form .= '<div class="position-absolute bottom-0 start-0 p-6 m-5">';
            $form .= '<h5 class="text-muted">' . $wallet->update_for_human . '</h5>';
            $form .= '</div>';

            $form .= '</div>'; // End card
            $form .= '</div>'; // End col
        }

        $form .= '</div>'; // End row
        $form .= '</div>'; // End container

        return parent::index($content
            ->title(trans('Application Wallet'))
            ->body(new HtmlString($form)));
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
            ->title(trans('level-intervals'))
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
            ->title(trans('level-intervals'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('level-intervals'))
            ->body($this->form()));
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CoreWallets());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('coins', __('coins'));
        $grid->column('update_for_human', __('Updated at'));

        return $grid;
    }

    protected function grid2()
    {
        $form = new Box();

        $form->view('admin.grid.common.CoreWallet');

        return $form;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CoreWallets::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('coins', __('Coins'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CoreWallets());

        $form->text('name', __('Name'));
        $form->number('coins', __('Coins'));

        return $form;
    }
}
