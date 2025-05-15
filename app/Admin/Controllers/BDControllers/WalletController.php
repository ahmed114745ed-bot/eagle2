<?php

namespace App\Admin\Controllers\BDControllers;

use App\Admin\Controllers\MainController;
use App\Helpers\Common;
use App\Models\Admin;
use App\Models\Agency;
use App\Models\Charge;
use App\Http\Controllers\Controller;
use App\Models\BDSallary;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\WalletTransaction;
use App\Services\BDChargeService;
use App\Services\WalletService;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Wallet\Enum\WalletEnum;
use Modules\Wallet\Services\CheckAvailableBalance;
use Modules\Wallet\Services\CheckSystemConfigs;
use Modules\Wallet\Services\CheckUserExistence;
class WalletController extends MainController
{
    use HasResourceActions;

    protected $title = "Charges";

    public $permission_name = "browse-get-salary-bd";

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        $netvalue = UserWallet::where('user_id', Auth::user()->app_id)
        ->selectRaw('SUM(value) as total_value, SUM(cut_amount) as total_cut')
        ->first();

    $finalvalue = ($netvalue->total_value ?? 0) - ($netvalue->total_cut ?? 0);

    return $content
        ->header(trans('admin.index'))
        ->description(trans('admin.description'))

        ->row(function ($row) use ($finalvalue) {
            // الكارت سيتم تضمينه من Blade View
            $row->column(12, view('admin.grid.bd.wallet', ['finalSalary' => $finalvalue]));
        })

        ->row(function ($row) {
            $row->column(12, $this->grid());
        });
    }



    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new \App\Models\WalletTransaction());
    
        $currentUserId = \Auth::user()->app_id;
        $grid->model()->where('user_id', $currentUserId);
    
        $grid->column('id', __('Id'));
    
        $grid->column('user.name', __('User'))->display(function () {
            $name = $this->user?->name ?? '';
            $uid = $this->user?->uuid ?? '';
            $path = $this->user?->profile?->avatar ?? null;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;
    
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
    
            $image = handleShowImageWithTypes($this->user->id ?? 0, $url, 40, 40);
            $showUrl = $this->user ? url("admin/users/{$this->user->id}") : "#";
    
            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='color: #aaa; font-size: smaller;'>UUID: $uid</span>
                    </div>
                </div>
            ";
        });
    
        $grid->column('type', __('Type'))->display(function ($type) {
            $types = [
                'add' => __('Add'),
                'cut' => __('Cut'),
                'pending' => __('Pending'),
            ];
            return $types[$type] ?? __('Unknown');
        });
    
        $grid->column('value', __('Value'));
    
        $grid->column('description_data', __('Description'))->display(function () {
            $description = $this->description ?? '';
            $data = json_decode($this->description_data, true) ?? [];
    
            switch ($description) {
                case 'target_achieved':
                    $targetId = $data['target_id'] ?? null;
                    $target = \App\Models\Target::find($targetId);
                    if ($target) {
                        $targetUrl = url("admin/targets/{$target->id}");
                        return "
                            <div>
                                <a href='{$targetUrl}'>
                                    <span>" . __('Target') . ": {$target->diamonds}</span>
                                </a>
                            </div>
                        ";
                    }
                    return __('Target not found');
    
                case 'transfer_to_user':
                    $userId = $data['receiver_id'] ?? null;
                    $user = \App\Models\User::find($userId);
                    if ($user) {
                        $path = $user->profile?->avatar ?? null;
                        $defaultImage = asset("images/businessman-icon.jpg");
                        $url = getImagePath($path) ?? $defaultImage;
                        if (!isImageExists($url)) {
                            $url = $defaultImage;
                        }
                        $image = handleShowImageWithTypes($user->id ?? 0, $url, 40, 40);
                        $showUrl = url("admin/users/{$user->id}");
    
                        return "
                            <div style='display: flex; align-items: center; gap: 10px;'>
                                $image
                                <div>
                                   <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                                     <span style='text-decoration: underline; cursor: pointer;'>{$user->name}</span>
                                    </a>
                                    <span style='color: #aaa; font-size: smaller;'>" . __('UUID:') . " {$user->uuid}</span>
                                </div>
                            </div>
                        ";
                    }
                    return __('User not found');
    
                case 'transfer_to_agency':
                    $agencyId = $data['agency_id'] ?? null;
                    $agency = \App\Models\Agency::find($agencyId);
                    if ($agency) {
                        $path = $agency->image ?? null;
                        $defaultImage = asset("images/businessman-icon.jpg");
                        $url = getImagePath($path) ?? $defaultImage;
                        $image = handleShowImageWithTypes($agency->id ?? 0, $url, 40, 40);
                        $agencyUrl = url("admin/agencies/{$agency->id}");
                        return "
                            <div style='display: flex; align-items: center; gap: 10px;'>
                                $image
                                <div>
                                    <a href='{$agencyUrl}'>
                                        <span>" . __('Agency:') . " {$agency->name}</span>
                                    </a>
                                    <span>" . __('id:') . " {$agency->id}</span>
                                </div>
                            </div>
                        ";
                    }
                    return __('Agency not found');
    
                default:
                    return json_encode($data);
            }
        });
    
        $grid->column('created_at', __('Created at'))->display(function ($created_at) {
            return \Carbon\Carbon::parse($created_at)->format('Y-m-d H:i');
        });
    
        $grid->disableCreateButton();
    
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
        $show = new Show(Charge::find($id));


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
        $form = new Form(new Charge);



        return $form;
    }
    public function transfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);
    
        $appID = Auth::user()->app_id;
    
        $netData = BDSallary::where('bd_id', $appID)
            ->selectRaw('SUM(sallary) as total_sallary, SUM(cut_amount) as total_cut')
            ->first();
    
        $finalSalary = ($netData->total_sallary ?? 0) - ($netData->total_cut ?? 0);
    
        if ($request->amount > $finalSalary) {
            admin_toastr(__('not_enough_balance'), 'error');
            return back();
        }
    
        $remaining = $request->amount;
        $salaries = BDSallary::where('bd_id', $appID)
            ->whereRaw('sallary > cut_amount')
            ->orderBy('id') 
            ->get();
    
        foreach ($salaries as $salary) {
            $available = $salary->sallary - $salary->cut_amount;
    
            if ($available <= 0) {
                continue;
            }
    
            $cut = min($available, $remaining);
            $salary->cut_amount += $cut;
            $salary->save();
    
            $remaining -= $cut;
    
            if ($remaining <= 0) {
                break;
            }
        }
    
        WalletService::storeTransaction(
            $appID,
            'add',
            $request->amount,
            'user_transaction',
            'transfer_to_wallet',
            []
        );
    
        admin_toastr(__('transferred_successfully'), 'success');
        return back();
    }

    
    public function charge(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'target_id' => 'nullable',    
            'target_type' => 'required|string',
        ]);
    
        $types = [
            'user' => [$this, 'chargeToUser'],
            'agency' => [$this, 'chargeToAgency']
        ];
    
        $type = $request->input('target_type');
     
        if (!array_key_exists($type, $types)) {
            admin_toastr('نوع الوجهة غير موجود', 'error');
            return back();
        }
       
        try {
            
            $data = call_user_func($types[$type], $request->all());
            admin_toastr('تم الشحن بنجاح', 'success');
            return back();
        } catch (\Throwable $e) {
            admin_toastr('حدث خطأ أثناء الشحن: ' . $e->getMessage(), 'error');
            return back();
        }
    }

    public function chargeToUser(array $data)
    {   
        $appID =Auth::user()->app_id;
        $sender = User::find($appID);
        $amount = $data['amount'];
        $receiverId = $data['target_id'] ?? null;
        
        $wallet = UserWallet::where('user_id', $sender->id)->first();
        if (!$wallet || ($wallet->value - $wallet->cut_amount) < $amount) {
            throw new \Exception(__('balance not enough'));
        }
    
      
        $receiver = User::find($receiverId);
        if (!$receiver) {
            throw new \Exception(__('this user not found'));
        }
    
        if ($sender->transfer_salary == 1) {
            throw new \Exception(__('api_responses.freeze_transfer_charger'));
        }
        if ($receiver->transfer_salary == 1) {
            throw new \Exception(__('api_responses.freeze_transfer_receiver'));
        }
        $rate = Common::getCoinsValue('user_coins');
        if (!$rate) {
            throw new \Exception(__('please set usd_value_in_coins in configs'));
        }
    
        $coins = $amount * $rate;
 
        return $this->startTransaction($wallet, $receiver, $sender, $amount, $coins, 'user');
    }

    public function startTransaction(UserWallet $userWallet, User $receiver,User $sender, int $amount, int $coins,string $receiverType)
    {
        DB::beginTransaction();
        try {
            $userWallet->increment('cut_amount', $amount);
            $receiver->increment('coins', $coins);

            WalletTransaction::create([
                'user_id' => $sender->id,
                'type' => 'cut',
                'transactions_type' => 'user_transaction',
                'value' => $amount,
            ]);

            $data = [
                'charger_id' => $sender->id,
                'charger_type' => 'user',
                'user_id' => $receiver->id,
                'agency_id' => $receiver->id,
                'user_type' => $receiverType,
                'amount' => $coins,
                'amount_type' => 2,
                "usd" =>  $amount ?? 0,
                'is_used_transferred' => 1,
            ];

            Charge::create($data);

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    public function chargeToAgency(array $data)
    {
        $user = Auth::user();
        $from = User::find($user->app_id);
        $usd = $data['amount'] ?? null;
        $toId = $data['target_id'] ?? null;

        if (settings()->get("stop_charge", 0)) {
        
            throw new \Exception(__('api_responses.freez_charge'));
        }

        if (!is_numeric($usd) || $usd <= 0 || fmod($usd, 1) != 0) {
            throw new \Exception(__('api_responses.This value is not allowed'));

        }

        $to = Common::searchAgency($toId);
        if (!$to || $to->is_frozen == 1) {
            throw new \Exception(__('api_responses.freez_charge'));

        }

        $rate = Common::getCoinsValue('shipping_coins');
        if (!$rate) {
            throw new \Exception(__('api_responses.please set usd_value_in_coins in configs'));

        }
        $coins = $usd * $rate;
    
    
        $wallet = UserWallet::where('user_id', $from->id)->first();
        if (!$wallet || ($wallet->value - $wallet->cut_amount) < $usd) {
            throw new \Exception(__('balance not enough'));
        }
    
       
            $this->performAgencyCharge($from, $to, $coins, $usd);
            return 1;
       
    }
    
    private function performAgencyCharge(User $fromUser, Agency $toAgency, $coins, $usd)
    {
        
    
   
    
        $toAgency->increment('coins', $coins);
    
        WalletService::storeTransaction(
            $fromUser->id,
            'cut',
            $usd,
            'user_transaction',
            'transfer_to_agency',
            ['agency_id' => $toAgency->id]
        );
        
        $data = [
            'charger_id' => $fromUser->id,
            'charger_type' => 'bd',
            'user_id' => $toAgency->id,
            'agency_id' => $toAgency->id,
            'user_type' => 'agency',
            'amount' => $coins,
            'amount_type' => 2,
            'usd' => $usd,
            'is_used_transferred' => false,
        ];

        Charge::create($data);
      
        return true;
    }
    



}



   
