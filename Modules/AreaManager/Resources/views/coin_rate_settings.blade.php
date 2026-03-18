<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">{{ __('Coin Rate Settings') }}</h3>
            </div>
            <form action="{{ route('areaManager.coin-rate-settings.store') }}" method="POST" class="form-horizontal">
                @csrf
                <div class="box-body">
                    <div class="form-group {{ $errors->has('rate') ? 'has-error' : '' }}">
                        <label for="rate" class="col-sm-2 control-label">{{ __('Custom Coin Rate') }}</label>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <span class="input-group-addon">1 USD =</span>
                                <input type="number" name="rate" id="rate" class="form-control" 
                                       value="{{ $customRate ?? $appRate }}" step="0.01" required>
                                <span class="input-group-addon">{{ __('Coins') }}</span>
                            </div>
                            @if($errors->has('rate'))
                                <span class="help-block">{{ $errors->first('rate') }}</span>
                            @endif
                            <p class="help-block">{{ __('Default App Rate: 1 USD = :rate Coins', ['rate' => $appRate]) }}</p>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
