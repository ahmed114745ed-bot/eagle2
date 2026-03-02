<div id="gamesSettings" class="settings-section">

    <div class="form">
        <label class="d-block">{{ __('Games Settings:') }}</label>

        <div class="row mt-4">
            <div class="col-md-6 mb-3 ms-0 me-auto">
                <form action="{{ url('admin/game-provider-setting') }}" method="POST" class="settings-form">
                    @csrf
                     <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                    <input type="hidden" name="provider_code" value="bytesun">
                    <input type="hidden" name="provider_name" value="Bytesun">
                    <div class="card p-3 shadow" style="height: 495px;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="m-0"style="color: white;">{{ __('Bytesun') }}</h4>
                             <div class="d-flex align-items-center">
                                    <input type="hidden" name="active" value="0">
                                    <input type="checkbox" id="bytesunRadio"
                                           class="custom-payment-radio libraryRealTime"
                                           name="active" value="1"
                                        {{ @$bytesunSettings?->is_active == 1  ? 'checked' : '' }}>
                                    <label for="bytesunRadio" class="switch"></label>
                                </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tencent_app_id">{{ __('app key') }}:</label>
                                        <input type="text" id="tencent_app_id" name="app_key"
                                               placeholder="app_key"
                                               value="{{ $bytesunSettings->app_key ?? '' }}" class="form-control" required>
                                    </div>
                                </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                    </div>
                </form>
            </div>

            <div class="col-md-6 mb-3 ms-0 me-auto">
                <form action="{{ url('admin/game-provider-setting') }}" method="POST" class="settings-form">
                    @csrf
                     <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                    <input type="hidden" name="provider_code" value="quantum_nexus">
                    <input type="hidden" name="provider_name" value="Quantum Nexus">
                    <div class="card p-3 shadow" style="height: 495px;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="m-0"style="color: white;">{{ __('Quantum Nexus') }}</h4>
                             <div class="d-flex align-items-center">
                                    <input type="hidden" name="active" value="0">
                                    <input type="checkbox" id="quantumNexusRadio"
                                           class="custom-payment-radio libraryRealTime"
                                           name="active" value="1"
                                        {{ @$quantumNexusSettings?->is_active == 1  ? 'checked' : '' }}>
                                    <label for="quantumNexusRadio" class="switch"></label>
                                </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="quantum_nexus_app_id">{{ __('app key') }}:</label>
                                        <input type="text" id="quantum_nexus_app_id" name="app_key"
                                               placeholder="app_key"
                                               value="{{ $quantumNexusSettings->app_key ?? '' }}" class="form-control" required>
                                    </div>
                                </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
