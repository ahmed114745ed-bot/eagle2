<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\Config;
use App\Models\GameWallet;
use Illuminate\Http\Request;
use App\Models\GameChargeHistory;
use Encore\Admin\Controllers\AdminController;

class GameChargeHistoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'GameChargeHistory';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GameChargeHistory());
        
        $grid->model()->orderByDesc('id');
        $grid->column('id', __('Id'));
        $grid->column('value', __('Value'));
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });

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
        $show = new Show(GameChargeHistory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('value', __('Value'));
        $show->field('admin_id', __('Admin id'));
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
        $form = new Form(new GameChargeHistory());

        $form->number('value', __('Value'));

        $form->saving(function (Form $form) {

            $balance  = $form->input('value') * config("app.one_coins") * 2;
            $gameWallet = GameWallet::whereMonth("created_at",date("m"))->whereYear("created_at",date("Y"))->first();
            if ($gameWallet) {
                $gameWallet->balance += $balance;
                $gameWallet->save();
            }else{
                GameWallet::create([
                    'balance' => $balance,
                ]);
            }
        });
        return $form;
    }


    public function chickLogin(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        // Define your credentials
        $validUsername = config("app.balance_user_name"); 
        $validPassword = config("app.balance_password");
        // Check if the provided credentials are correct
        if ($username == $validUsername && $password == $validPassword) {
           
            // Store a session variable to indicate the user is authenticated
            session(['auth' => true]);

            // Redirect to the route the user initially wanted to access
            return redirect()->intended('admin/game-charge-histories');
        } else {
            return redirect()->route('admin/auth')->withErrors(['Invalid credentials. Please try again.']);
        }
    }
}
