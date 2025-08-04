<div class="box-body no-padding">
    <div class="nav-scroll-container">
        <ul class="nav nav-pills">
            <li class="{{ request()->name == 'dash' || request()->name == null ? 'active' : '' }}">
                <a href="?name=" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('dash_repo') }}
                </a>
            </li>
            <li class="{{ request()->name == 'shipping-agency-activity' ? 'active' : '' }}">
                <a href="?name=shipping-agency-activity" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('app_repo') }}
                </a>
            </li>
            <li class="{{ request()->name == 'host' ? 'active' : '' }}">
                <a href="?name=host" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('charge host agent') }}
                </a>
            </li>
            <li class="{{ request()->name == 'stripe' ? 'active' : '' }}">
                <a href="?name=stripe" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('payment gateway') }}
                </a>
            </li>
            <li class="{{ request()->name == 'in-app-purchas' ? 'active' : '' }}">
                <a href="?name=in-app-purchas" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('Recharge for self') }}
                </a>
            </li>
            <li class="{{ request()->name == 'exchange' ? 'active' : '' }}">
                <a href="?name=exchange" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('Convert diamonds to coins') }}
                </a>
            </li>
        </ul>
    </div>
    
    <div class="col">
    <h4 class="details-title">{{ __('Details') }}</h4>

    @php
        // Determine the current request name
        $isDashboard = request()->name == 'dash' || request()->name == null;
        $isApp = request()->name == 'shipping-agency-activity';
        $isStripe = request()->name == 'stripe';
        $isStripeNew = request()->name == 'stripenew';
        $isInApp = request()->name == 'in-app-purchas';
        $isExchange =request()->name == 'exchange';
        $isHost =request()->name == 'host';

        // Anonymous function to get user based on UUID
        $getUserByUuid = function ($uuid) {
            return \App\Models\User::where('uuid', $uuid)->first() ?? \App\Models\ShippingAgency::where('id', $uuid)->first();
        };

        // Function to calculate receiver value based on conditions
        $calculateReceiverValue = function ($user, $request) use ($isDashboard, $isApp, $isStripe, $isStripeNew, $isInApp) {
            if ($isDashboard || $isApp) {
                return \App\Models\Charge::where('user_id', $user?->id)->sum('amount');
            } elseif ($isStripe) {
                return \App\Models\CoinLog::where('user_id', $user?->id)
                    ->whereNotIn('method', ['huawei_pay', 'google_pay', 'strip', 'apple_pay'])
                    ->sum('obtained_coins');
            } elseif ($isStripeNew) {
                return \App\Models\CoinLog::where('user_id', $user?->id)
                    ->where('method', 'strip')
                    ->sum('obtained_coins');
            } elseif ($isInApp) {
                return $request->name_for_url_shortcut
                    ? \App\Models\CoinLog::where('user_id', $user?->id)
                        ->where('method', $request->name_for_url_shortcut)
                        ->sum('obtained_coins')
                    : \App\Models\CoinLog::where('user_id', $user?->id)
                        ->whereIn('method', ['huawei_pay', 'google_pay', 'apple_pay'])
                        ->sum('obtained_coins');
            }

            return 0; // Default return value
        };

           if($isDashboard || $isStripe || $isHost)
           {
              $fields =  ['dollar' => __('total transfer dollars'), 'coins' => __('total transfer coins')];
           }elseif ($isExchange) {
             $fields =  ['diamonds' => __('total diamonds'), 'coins' => __('total coins')];
           }
           else{
              $fields = ['receiver' => __('Total recharge to recharge agencies'), 'sender' => __('Total recharge from recharge agencies')];
            
           }

       
    @endphp

    <div class="row my-1 form-Roles" style="overflow-x: auto;">
        <div class="d-flex">
            @foreach ($fields as $name => $label)
                <div class="col-md-3 flex-shrink-0">
                    <label class="form-label">{{ $label }}</label>
                    @php
                        $uuid = request($name)['uuid'] ?? '0';
                        $user = $getUserByUuid($uuid);
                        $value = 0;

                        if ($name === 'receiver') {
                            if($isApp)
                            {
                              $value = \App\Models\Charge::where('user_type', 'agency')->sum('amount');
                            }else {
                                 $value = $calculateReceiverValue($user, request());
                            }
                           
                        } elseif ($name === 'sender') {
                            if($isApp)
                            {
                              $value = \App\Models\Charge::where('charger_type', 'agency')->sum('amount');
                            }else {
                                $value = \App\Models\Charge::where('charger_id', $user?->id)->sum('amount');
                            }
                            
                        }  elseif ($name === 'dollar') {
                            if($isDashboard){
                                $value = \App\Models\Charge::where('charger_type', 'dash')->sum('usd');
                            }elseif ($isHost) {
                                $value = \App\Models\Charge::where('charger_type', 'host_agency')->sum('usd');
                            } else{
                                $value = DB::table('coin_logs')->join('coins', 'coin_logs.coin_id', '=', 'coins.id')->sum('coins.usd');
                            } 
                           
                        }elseif ($name === 'coins') {
                            if($isDashboard){
                             $value = \App\Models\Charge::where('charger_type', 'dash')->sum('amount');
                            }elseif ($isExchange) {
                              $value = \App\Models\ExchangeLog::sum('value');

                            }elseif ($isHost) {
                                $value = \App\Models\Charge::where('charger_type', 'host_agency')->sum('amount');
                            }
                            else{
                                $value = \App\Models\CoinLog::where('status', 1)->sum('obtained_coins');
                            }
                            
                        }elseif ($name === 'diamonds') {
                            $value = \App\Models\ExchangeLog::sum('diamonds');
                        }
                    @endphp
                    <input type="text" class="form-control" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}" readonly>
                </div>
            @endforeach
        </div>
    </div>
</div>

</div>

<style>
    .nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover{
        background-color: var(--primary-color);
    }

    .nav-pills>li.active>a, .nav-pills>li.active>a:hover, .nav-pills>li.active>a:focus{
        border-top-color: var(--primary-color);
    }

    .nav-scroll-container {
        overflow-x: auto; /* Allow horizontal scrolling */
        white-space: nowrap; /* Prevent wrapping to the next line */
        -webkit-overflow-scrolling: touch; /* Enable smooth scrolling on iOS */
    }

    .nav-pills {
        display: inline-flex; /* Display nav items in a single line */
        padding: 10px 0; /* Adjust padding as needed */
    }

    .nav-pills li {
        display: inline-block; /* Ensure list items display inline */
    }

    .details-section {
        padding: 20px;
    }

    .details-title {
        margin-bottom: 20px;
        font-weight: 500;
    }

</style>
