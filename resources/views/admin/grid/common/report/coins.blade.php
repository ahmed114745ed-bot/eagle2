<div class="box box-solid">

    <div class="box-header with-border">
        <h3 class="box-title">الحقول</h3>

        <div class="box-tools">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked">
            <li class="{{ request()->name == 'lucky_gift' || request()->name == null ? 'active' : '' }}"><a href="?name=lucky_gift"
                    class="charge_action"><i class="fa fa-arrow-right text-red"></i>{{ __('lucky_gift') }}</a>
            </li>
            <li class="{{ request()->name == 'games' ? 'active' : '' }}"><a href="?name=games" class="charge_action"><i
                        class="fa fa-arrow-right text-red"></i>{{ __('games') }}</a>
            </li>
            <li class="{{ request()->name == 'shipping_host' ? 'active' : '' }}">
                <a href="?name=shipping_host" class="charge_action"><i
                        class="fa fa-arrow-right text-red"></i>{{ __('shipping_host') }}</a>
            </li>

        </ul>
    </div>

</div>

<div class="col">

    @if(request()->name == 'lucky_gift' || request()->name == null)
        <div class="col my-1 form-Roles">
            @php
                $subQuery = \App\Models\UserLuckyGift::query()
                                 ->selectRaw(
                                        'MIN(user_lucky_gifts.created_at) as earliest_created_at, ' .
                                        'SUM(user_lucky_gifts.number) as total_number, ' .
                                        'user_lucky_gifts.gift_id, ' .
                                        'user_lucky_gifts.user_id, ' .
                                        'MAX(users.name) as user_name, ' . // Aggregated using MAX
                                        'MAX(gifts.img) as gift_img, ' . // Aggregated using MAX
                                        'MAX(gifts.name) as gift_name, ' . // Aggregated using MAX
                                        'user_lucky_gifts.gift_price, ' .
                                        'SUM(CASE WHEN user_lucky_gifts.type = 1 THEN user_lucky_gifts.number ELSE 0 END) as total_number_win'
                                    )
                                    ->leftJoin('users', 'user_lucky_gifts.user_id', '=', 'users.id')
                                    ->leftJoin('gifts', 'user_lucky_gifts.gift_id', '=', 'gifts.id')
                                    ->groupBy(
                                        'user_lucky_gifts.gift_id',
                                        'user_lucky_gifts.user_id',
                                        'user_lucky_gifts.gift_price',
                                    );


                if (request('from_date') != null && request('to_date') != null){
                    $fromDate = \App\Helpers\UserCommon::arabicToEnglishNumbers(request('from_date'));
                    $toDate = \App\Helpers\UserCommon::arabicToEnglishNumbers(request('to_date'));
                    $subQuery=$subQuery->whereDate('user_lucky_gifts.created_at', '>=', $fromDate)->whereDate('user_lucky_gifts.created_at', '<=',$toDate);
                }
                if(request('Uid')){
                    $subQuery=$subQuery->where('users.uuid', request('Uid'));
                }
                $totalNumberWin = \DB::query()->fromSub($subQuery, 'subquery')
                                 ->selectRaw('SUM(total_number_win * gift_price) as grand_total_number_win,SUM(total_number * gift_price) as grand_total_number_cost')
                                 ->first();
            @endphp
            <label class="form-label">مجموع ال cost</label>
            <input type="text" class="form-control" name="receiver" id="receiver" value="{{@$totalNumberWin->grand_total_number_cost}}">
        </div>
        <br>
        <div class=" col  my-1 form-Roles" >
            <label class="form-label">مجموع ال win</label>
            <input type="text" class="form-control " id="sender" name="sender"  value="{{@$totalNumberWin->grand_total_number_win}}" >
        </div>
        <br>
        <div class=" col  my-1 form-Roles" >
            <label class="form-label">حاصل الفرق</label>
            <input type="text" class="form-control " id="sender" name="sender"  value="{{ @$totalNumberWin->grand_total_number_win - @$totalNumberWin->grand_total_number_cost }}" >
        </div>
    @elseif(request()->name == 'games')
            <div class="col my-1 form-Roles">
                @php
                    $subQuery = \App\Models\CoinGameUser::query()
                                        ->selectRaw('MIN(coin_game_users.created_at) as earliest_created_at, coin_game_users.game_id, coin_game_users.user_id, MAX(users.name) as user_name, MAX(games.name) as game_name, SUM(CASE WHEN coin_game_users.type = 1 THEN coin_game_users.coins ELSE 0 END) as total_coins_win, SUM(CASE WHEN coin_game_users.type = 0 THEN coin_game_users.coins ELSE 0 END) as total_coins_lose')
                                        ->leftJoin('users', 'coin_game_users.user_id', '=', 'users.id')
                                        ->leftJoin('games', 'coin_game_users.game_id', '=', 'games.id')
                                        ->groupBy('coin_game_users.game_id', 'coin_game_users.user_id');


                    if (request('from_date') != null && request('to_date') != null){
                        $fromDate = \App\Helpers\UserCommon::arabicToEnglishNumbers(request('from_date'));
                        $toDate = \App\Helpers\UserCommon::arabicToEnglishNumbers(request('to_date'));
                        $subQuery=$subQuery->whereDate('coin_game_users.created_at', '>=', $fromDate)->whereDate('coin_game_users.created_at', '<=',$toDate);
                    }
                    if(request('Uid')){
                        $subQuery=$subQuery->where('users.uuid', request('Uid'));
                    }
                    $totalNumberWin = \DB::query()->fromSub($subQuery, 'subquery')
                                     ->selectRaw('SUM(total_coins_lose ) as total_lose,SUM(total_coins_win) as total_win')
                                     ->first();
                @endphp
                <label class="form-label">مجموع ال lose</label>
                <input type="text" class="form-control" name="receiver" id="receiver" value="{{@$totalNumberWin->total_lose}}">
            </div>
            <br>
            <div class=" col  my-1 form-Roles" >
                <label class="form-label">مجموع ال win</label>
                <input type="text" class="form-control " id="sender" name="sender"  value="{{@$totalNumberWin->total_win}}" >
            </div>
            <br>
            <div class=" col  my-1 form-Roles" >
                <label class="form-label">حاصل الفرق</label>
                <input type="text" class="form-control " id="sender" name="sender"  value="{{ @$totalNumberWin->total_lose - @$totalNumberWin->total_win }}" >
            </div>
    @elseif(request()->name == 'shipping_host')
        <div class="col my-1 form-Roles">
            @php
                $subQuery = \App\Models\Charge::query()
                                    ->selectRaw('MIN(charges.created_at) as earliest_created_at,charges.user_id, MAX(users.name) as user_name, sum(CASE WHEN charges.user_type = "app" THEN charges.amount ELSE 0 END) as total_coins_from_user, sum(CASE WHEN charges.user_type != "app" AND charges.user_type != "dash" THEN charges.amount ELSE 0 END) as total_coins_shipping')
                                    ->leftJoin('users', 'charges.user_id', '=', 'users.id')
                                    ->groupBy( 'charges.user_id');


                if (request('from_date') != null && request('to_date') != null){
                    $fromDate = \App\Helpers\UserCommon::arabicToEnglishNumbers(request('from_date'));
                    $toDate = \App\Helpers\UserCommon::arabicToEnglishNumbers(request('to_date'));
                    $subQuery=$subQuery->whereDate('charges.created_at', '>=', $fromDate)->whereDate('charges.created_at', '<=',$toDate);
                }
                if(request('Uid')){
                    $subQuery=$subQuery->where('users.uuid', request('Uid'));
                }
                $totalNumberWin = \DB::query()->fromSub($subQuery, 'subquery')
                                 ->selectRaw('SUM(total_coins_shipping ) as total_shipping,SUM(total_coins_from_user) as total_user')
                                 ->first();
            @endphp
            <label class="form-label">مجموع ال total_shipping</label>
            <input type="text" class="form-control" name="receiver" id="receiver" value="{{@$totalNumberWin->total_shipping}}">
        </div>
        <br>
        <div class=" col  my-1 form-Roles" >
            <label class="form-label">مجموع ال total_user</label>
            <input type="text" class="form-control " id="sender" name="sender"  value="{{@$totalNumberWin->total_user}}" >
        </div>
    @endif
</div>
