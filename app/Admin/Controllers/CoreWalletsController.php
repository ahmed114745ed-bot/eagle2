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
        $coreWallets = CoreWallets::get();
        $icons = [
            'app_wallet' => 'fa-solid fa-coins',
            'owner_wallet' => 'fa-solid fa-user-tie',
            'game_wallet' => 'fa-solid fa-dice',
            'lucky_box' => 'fa-solid fa-box-open',
            'host_agency' => 'fa-solid fa-building',
            'agency' => 'fa-solid fa-briefcase',
            'lucky_gifts' => 'fa-solid fa-gift',
            'chinese_games' => 'fa-solid fa-dragon',
            'games' => 'fa-solid fa-gamepad',
            'shipping_agents' => 'fa-solid fa-truck',
            'payment_gateways' => 'fa-solid fa-credit-card',
            'mall' => 'fa-solid fa-store',
            'vip' => 'fa-solid fa-crown',
            'ads' => 'fa-solid fa-rectangle-ad'
        ];

        $form = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';

        $form .= '<div class="container mt-4">';
        $form .= '<div class="row justify-content-center g-4 wallet_div">';

        foreach ($coreWallets as $index => $wallet) {
            $icon = $icons[$wallet->name] ?? 'fa-solid fa-wallet';

            $form .= '<div class="col-md-5 col-lg-5 mb-4 px-3 wallet_posation">'; // Added padding for space
            $form .= '<div class="card shadow-lg position-relative border-0"
          style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg,rgb(211, 211, 183),rgb(202, 211, 193)); transition: transform 0.3s ease-in-out; margin-bottom: 20px;">';


            // Card hover effect
            $form .= '<style>
    .card:hover { transform: scale(1.05); }
  </style>';

            // Edit Button - Circle taking all card edges
            $form .= '<a href="' . admin_url('core-wallets/' . $wallet->id . '/edit') . '"
        class="btn position-absolute top-0 start-0 w-100 h-100 rounded-circle shadow-lg d-flex align-items-center justify-content-center"
        style="
            background: rgba(255, 255, 255, 0.8);
            color: #333;
            transition: transform 0.2s ease-in-out, background 0.2s ease-in-out;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 2px solid #fff;
            border-radius: 50%;
        "
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="' . __('Edit') . '">
        <i class="fas fa-pen"></i>
    </a>';

            // Add hover effect with CSS
            $form .= '<style>
        .btn:hover {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 1);
        }
    </style>';

            // Card Body
            $form .= '<div class="card-body text-center p-4">';

            // Icon at the top - Fixed icon display
            $form .= '<div class="mb-3">';
            $form .= '<i class="' . $icon . ' text-primary" style="font-size: 2.5rem;"></i>';
            $form .= '</div>';

            // Wallet Name & Coins
            $form .= '<h5 class="fw-bold mb-2" style="color: #000000;">' . ucfirst(str_replace('_', ' ', $wallet->name)) . '</h5>';
            $form .= '<p class="fs-5 fw-semibold" style="color: #000000;">' . __('Coins') . ' : ' . number_format($wallet->coins) . '</p>';

            $form .= '</div>'; // End card-body

            // Last Updated in Bottom-Left Corner
            $form .= '<div class="position-absolute bottom-0 start-0 p-6 m-5">';
            $form .= '<h5 class="text-muted" style="padding-right: 5px;">' . $wallet->update_for_human . '</h5>';
            $form .= '</div>';

            $form .= '</div>'; // End card
            $form .= '</div>'; // End col
        }

        $form .= '</div>'; // End row
        $form .= '</div>'; // End container

        return parent::index($content
            ->title(trans('Application wallet'))
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
