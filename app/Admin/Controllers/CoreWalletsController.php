<?php

namespace App\Admin\Controllers;

use App\Models\CoreWallets;
use App\Models\CoreWalletTransaction;
use Encore\Admin\Layout\Content;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Throwable;

use function Laravel\Prompts\error;

class CoreWalletsController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */


    public $permission_name = 'app-wallet';

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

        return parent::index($content
            ->title(__('Application wallet'))
            ->body(view('admin.core_wallets.index', [
                'coreWallets' => CoreWallets::get(),
                'icons' => $icons
            ])));
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

    protected function submitTransfer(Request $request)
    {
        $request->validate([
            'from_wallet_id' => 'required',
            'to_wallet_id' => 'required',
            'amount' => 'required|numeric|min:1',
        ]);

        $adminId = auth()->id();
        $fromWallet = CoreWallets::find($request->from_wallet_id);
        if ($fromWallet->coins < $request->to_wallet_id) {
            return throw error('plz check wallet coins');
        }
        $fromWallet->coins -= $request->amount;
        $fromWallet->save();
        $ToWallet = CoreWallets::find($request->to_wallet_id);
        $ToWallet->coins += $request->amount;
        $ToWallet->save();

        CoreWalletTransaction::create([
            'from_wallet' => $request->from_wallet_id,
            'to_wallet' => $request->to_wallet_id,
            'amount' => $request->amount,
            'admin_id' => $adminId,
        ]);
        return response()->json([
            'status' => 1,
            'message' => 'Done',
        ]);
    }
}
