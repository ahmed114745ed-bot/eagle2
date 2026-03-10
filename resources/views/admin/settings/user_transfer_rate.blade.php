<div id="userTransferRateSettings" class="user-transfer-rate-settings settings-section">
    <h3>{{ __('User Transfer Rate Settings') }}</h3>
    <form action="{{ route('admin.saveSettings') }}" method="POST" class="settings-form">
        @csrf
        <div class="form row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="user_transfer_rate_enabled">{{ __('Enable User Transfer Rate') }}</label>
                    <input type="hidden" name="user_transfer_rate_enabled" value="0">
                    <input type="checkbox" name="user_transfer_rate_enabled" value="1"
                           data-bootstrap-switch {{ $userTransferRateEnabled ? 'checked' : '' }}>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="user_transfer_coin_rate">{{ __('User Transfer Coin Rate') }}</label>
                    <div class="input-group">
                        <span class="input-group-addon">1 USD =</span>
                        <input type="number" name="user_transfer_coin_rate" id="user_transfer_coin_rate" class="form-control"
                               value="{{ $userTransferCoinRate }}" placeholder="{{ __('Coins') }}">
                        <span class="input-group-addon">{{ __('Coins') }}</span>
                    </div>
                    <p class="help-block">{{ __('If disabled, App Coin Rate will be used.') }}</p>
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
