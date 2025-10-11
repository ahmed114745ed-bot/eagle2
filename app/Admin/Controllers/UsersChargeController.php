<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\Charge;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\Setting;
use App\Helpers\UserCommon;
use App\Models\UserSallary;
use App\Enums\UserCoinLogType;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use App\Helpers\UserCoinLogHelper;
use App\Facades\CustomNotification;
use Illuminate\Support\Facades\Auth;
use App\Admin\Actions\UsersChargeAction;
use Encore\Admin\Controllers\HasResourceActions;

class UsersChargeController extends MainController
{
    use HasResourceActions;

    const reason = 'return-coins-9-2025';
    public $permission_name = 'charge-to-user';


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
    }




    /**
     * Make a grid builder.
     *
     * @return Grid
     */

    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->disableRowSelector();

        $grid->filter(function (Grid\Filter $filter) {

            $filter->expand();

            $filter->disableIdFilter();
            $filter->equal('ID', __('ID'));

            $filter->where(function ($query) {
                $query->where('name', 'like', "%{$this->input}%");
            }, __('name'));

            $filter->where(function ($query) {

                $query->where('uuid', 'like', "%{$this->input}%");
            }, __('uuid'));
        });

        $grid->model()
            ->select('id', 'name', 'uuid', 'coins', 'di')
            ->with('profile')
            ->orderByDesc('id');

        $grid->id(__('ID'));



        $grid->column('name', trans('owner'))
            ->display(function ($name) {
                $uid = @$this->uuid;
                $path = @$this->profile?->avatar;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;

                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }

                $image = handleShowImageWithTypes($this->id, $url, 40, 40);
                $showUrl = $this ? url("admin/users/{$this->id}") : 0;
                return "
                    <div style='display: flex; align-items: center; gap: 10px;'>
                        $image
                        <div>
                           <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                             <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                            </a>
                            <span style='color: #aaa; font-size: smaller;'>UUID: $uid</span>
                        </div>
                    </div>";
            });

        $grid->column('di', __('coins'))->display(function ($coin) {
            $icon = asset('images/coin.jpg');
            $coin = (float) $coin;
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });
        // $grid->column('di', __('coins'))->display(function ($coin) {
        //     $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار
        //     $coin = (float) $coin;
        //     return "
        //         <div style='display: flex; align-items: center; gap: 5px;'>
        //             <span>" . number_format($coin) . "</span>
        //             <img src='{$icon}' alt='Coin' width='20' height='20'>

        //         </div>
        //     ";
        // });
        // $grid->column('di', __('coins'))->display(function ($coin) {
        //     $shippingCoins = \Cache::rememberForever('shipping_coins', function () {
        //         $setting =   Setting::where('key', 'shipping_coins')->first();
        //         return $setting?->value;
        //     });

        //     if ($shippingCoins) {
        //         $dollars = $this->coins / $shippingCoins;
        //         $numberFormatDollars = number_format($dollars);
        //     } else {
        //         $numberFormatDollars = __('please set agency coins in configs');
        //     }

        //     $icon = asset('images/coins.jpg');
        //     return "
        //         <div style='display: flex; align-items: center; gap: 5px;'>
        //             <span>" . $numberFormatDollars . "</span>
        //             <img src='{$icon}' alt='Coin' width='20' height='20'>

        //         </div>
        //     ";
        // });
        if (\Encore\Admin\Facades\Admin::user()->can('add-switch-' . $this->permission_name) || \Encore\Admin\Facades\Admin::user()->can('*') || \Encore\Admin\Facades\Admin::user()->can('history-switch-' . $this->permission_name)) {
            $grid->column('actions', __('Actions'))
                ->display(function () {

                    return (new UsersChargeAction())->setUserId($this->id)->render();
                })
                ->style('white-space: nowrap; width: 100px;');
        }

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableActions();

        return $grid;
    }

    public function chargeUser()
    {
        $userSalaries = UserSallary::where('month', 9)->with('user')->where('year', 2025)->where('remaining_diamond', '!=', 0)->get();
        foreach ($userSalaries as $userSalary) {
            $coin = $userSalary->remaining_diamond * 0.5;

            $chargedUser = $userSalary->user;
            if ($chargedUser && !$this->recentlyCharged($chargedUser->id)) {
                $user = $chargedUser;
                $amountBefore =  $chargedUser->di;

                UserCoinLogHelper::logByType(
                    $chargedUser->id,
                    $coin,
                    $amountBefore,
                    UserCoinLogType::ADMIN_CHARGES,
                );
                // increment coins
                $chargedUser->increment(['di' => $coin]);

                $this->createChargeRecord($user, $coin, $coin, 0);

                $title =  'Coins Added';

                $body = 'You have received :coins coins from admin.';


                try {
                    CustomNotification::charges($user, $title, $body, ['coins' => $coin]);
                } catch (\Exception $e) {

                }
            }


        }

        return response()->json([
            'message' => 'User charge process completed successfully.',
            'count' => $userSalaries->count(),
        ]);
    }

    private function createChargeRecord(User $user, $amount, $coins = 0, $usdAmount)
    {
        $charge = new Charge();
        $charge->charger_id = 1;
        $charge->charger_type =  'dash';
        $charge->user_id = $user->id;
        $charge->agency_id =   null;
        $charge->user_type = 'user';
        $charge->amount = $coins;
        $charge->usd = $usdAmount;
        $charge->balance_before =  $user->di  - $coins;
        $charge->reason_en = self::reason;
        $charge->save();

//        UserCommon::UserEarnedInvitation($user->id, $coins, $charge->id);
    }

    private function recentlyCharged(int $userId)
    {
        return Charge::where('user_id', $userId)->where('reason_en',self::reason )->exists();
    }
}
