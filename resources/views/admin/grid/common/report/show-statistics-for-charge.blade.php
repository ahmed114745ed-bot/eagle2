<div class="col">

    @php
        $dash = request()->name == 'dash' || request()->name == null;
        $app = request()->name == 'app';
        $stripe = request()->name == 'stripe';
        $stripenew = request()->name == 'stripenew';
        $inApp = request()->name == 'in-app-purchas';

    @endphp
    @if($dash || $app)
        <div class="col my-1 form-Roles">
            <label class="form-label">{{ __('admin.receiver') }}</label>
            @php
                $user = \App\Models\User::where('uuid',@request('receiver')['uuid'] ?? '0')->first();
            @endphp
            <input type="text" class="form-control" name="receiver" id="receiver" value="{{ \App\Models\Charge::where('user_id', $user?->id)->sum('amount') }}">
        </div>
        <br>
        <div class=" col  my-1 form-Roles" >
            <label class="form-label">{{__('admin.sender')}}</label>

            @php
                $user = \App\Models\User::where('uuid',@request('sender')['uuid'] ?? '0')->first();
            @endphp

            <input type="text" class="form-control " id="sender" name="sender"  value="{{\App\Models\Charge::where('charger_id',$user?->id)->sum('amount')}}" >
        </div>
        <div class=" col  my-1 form-Roles" >
            <label class="form-label"> {{__('admin.gameCoins')}}</label>

            @php
                $user = \App\Models\User::where('uuid',@request('receiver')['uuid'] ?? '0')->first();

                 $result = \App\Models\CoinGameUser::select(
                    \DB::raw("SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) as sum_type_1"),
                    \DB::raw("SUM(CASE WHEN type = 0 THEN coins ELSE 0 END) as sum_type_0")
                )
                ->where('user_id', $user?->id)
                ->first();
                 $sumType1 = $result->sum_type_1;
                $sumType0 = $result->sum_type_0 ;

                // Calculate the difference
                $difference = $sumType1 - $sumType0 ?? 0;
            @endphp

            <input type="text" class="form-control" name="receiver" id="receiver" value="{{$difference}}">
        </div>

        <div class=" col  my-1 form-Roles" >
            <label class="form-label"> {{__('admin.luckyGiftCoin')}}</label>

            @php
                $user = \App\Models\User::where('uuid',@request('receiver')['uuid'] ?? '0')->first();

                 $result = \App\Models\UserLuckyGift::select(
                    \DB::raw("SUM(CASE WHEN type = 1 THEN value ELSE 0 END) as sum_type_1"),
                    \DB::raw("SUM(CASE WHEN type = 0 THEN value ELSE 0 END) as sum_type_0")
                )
                ->where('user_id', $user?->id)
                ->first();
                 $sumType1 = $result->sum_type_1;
                $sumType0 = $result->sum_type_0 ;

                // Calculate the difference
                $difference = $sumType1 - $sumType0 ?? 0;
            @endphp

            <input type="text" class="form-control" name="receiver" id="receiver" value="{{$difference}}">
        </div>
    @elseif($stripe)
        <div class="col my-1 form-Roles">
            <label class="form-label">{{ __('admin.receiver') }}</label>
            @php
                $user = \App\Models\User::where('uuid', @request('user')['uuid'] ?? '0')->first();
            @endphp
            <input type="text" class="form-control" name="receiver" id="receiver" value="{{ \App\Models\CoinLog::where('user_id', $user?->id)->where('method', '!=', 'huawei_pay')->where('method', '!=', 'google_pay')->where('method', '!=', 'strip')->where('method', '!=', 'apple_pay')->sum('obtained_coins') }}">
        </div>
    @elseif($stripenew)
        <div class="col my-1 form-Roles">
            <label class="form-label">{{ __('admin.receiver') }}</label>
            @php
                $user = \App\Models\User::where('uuid', @request('user')['uuid'] ?? '0')->first();
            @endphp
            <input type="text" class="form-control" name="receiver" id="receiver" value="{{ \App\Models\CoinLog::where('user_id', $user?->id)->where("method","strip")->sum('obtained_coins') }}">
        </div>
    @elseif($inApp)
        <div class="col my-1 form-Roles">
            <label class="form-label">{{ __('admin.receiver') }}</label>
            @php
                $user = \App\Models\User::where('uuid', @request('user')['uuid'] ?? '0')->first();
            @endphp
            @if(@request('name_for_url_shortcut') !=null)
                <input type="text" class="form-control" name="receiver" id="receiver" value="{{ \App\Models\CoinLog::where('user_id', $user?->id)->where("method",@request('name_for_url_shortcut')??0)->sum('obtained_coins') }}">
            @else
                <input type="text" class="form-control" name="receiver" id="receiver" value="{{ \App\Models\CoinLog::where('user_id', $user?->id)->whereIn('method', ['huawei_pay','google_pay', 'apple_pay'])->sum('obtained_coins') }}">
            @endif
        </div>
    @endif

</div>
