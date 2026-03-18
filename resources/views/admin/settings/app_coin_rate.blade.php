<div id="coinRateSettings" class="coin-rate-settings settings-section">
    <h3>{{ __('Coin Rate Settings') }}</h3>
    <form action="{{ route('admin.saveSettings') }}" method="POST" class="settings-form">
        @csrf
        <div class="form row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="app_coin_rate">{{ __('App Coin Rate') }}</label>
                    <div class="input-group">
                        <span class="input-group-addon">1 USD =</span>
                        <input type="number" name="app_coin_rate" id="app_coin_rate" class="form-control"
                               value="{{ $appCoinRate }}" placeholder="{{ __('Coins') }}" required>
                        <span class="input-group-addon">{{ __('Coins') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex gap-3 mt-4">
            <button type="submit" class="btn btn-primary btn-save" style="margin: 0px 20px !important ; display: inline !important;">
                <i class="fas fa-save"></i> {{ __('Save') }}
            </button>
        </div>
    </form>
</div>
