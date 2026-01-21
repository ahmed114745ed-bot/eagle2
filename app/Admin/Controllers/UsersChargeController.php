<?php

namespace App\Admin\Controllers;

use App\Jobs\SendChargeNotificationJob;
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
        $countryID = session('filter_country_id');

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
            ->when($countryID, fn($q) => $q->where('country_id', $countryID))
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
        $month = 9;
        $year = 2025;
        $reason = self::reason;

        $chunkSize = 500; // adjust based on memory/performance

        $processedCount = 0;

        UserSallary::with('user:id,di,notification_id,is_logout')
            ->where('month', $month)
            ->where('year', $year)
            ->where('remaining_diamond', '!=', 0)
            ->select(['id', 'user_id', 'remaining_diamond'])
            ->chunk($chunkSize, function ($userSalaries) use (&$processedCount, $reason) {

                // Get all charged user IDs in this chunk only
                $chargedUserIds = Charge::whereIn('user_id', $userSalaries->pluck('user_id'))
                    ->where('reason_en', $reason)
                    ->pluck('user_id')
                    ->toArray();

                $charges = [];

                foreach ($userSalaries as $userSalary) {
                    $user = $userSalary->user;

                    if (!$user || in_array($user->id, $chargedUserIds)) {
                        continue;
                    }

                    $coin = $userSalary->remaining_diamond * 0.5;
                    $amountBefore = $user->di;

                    // Log coins
                    UserCoinLogHelper::logByType(
                        $user->id,
                        $coin,
                        $amountBefore,
                        UserCoinLogType::ADMIN_CHARGES,
                    );

                    // Update user balance
                    $user->increment('di', $coin);

                    $charges[] = [
                        'charger_id'      => 1,
                        'charger_type'    => 'dash',
                        'user_id'         => $user->id,
                        'agency_id'       => null,
                        'user_type'       => 'user',
                        'amount'          => $coin,
                        'usd'             => 0,
                        'balance_before'  => $amountBefore,
                        'reason_en'       => $reason,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];

                    // Dispatch queued job for notification
                    SendChargeNotificationJob::dispatch(
                        $user,
                        'Coins Added',
                        "You have received {$coin} coins from admin.",
                        ['coins' => $coin]
                    )->onQueue('notifications');
                }

                // Bulk insert charges for this chunk
                if (!empty($charges)) {
                    Charge::insert($charges);
                    $processedCount += count($charges);
                }
            });

        return response()->json([
            'message' => 'User charge process completed successfully.',
            'count'   => $processedCount,
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
        return Charge::where('user_id', $userId)->where('reason_en', self::reason)->exists();
    }
}
