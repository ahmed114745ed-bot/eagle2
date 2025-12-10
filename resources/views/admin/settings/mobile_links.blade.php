<div id="mobileLinks" class="settings-section">
    <div class="form">
        <div class="row mt-4">
            <div class="col-md-6 mb-3 ms-0 me-auto" style="margin-top: 40px;">
                <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="current_tab" value="">
                    <div class="card exp-card-cont p-3 shadow">
                        <div class="card-header exp-card d-flex justify-content-between align-items-center">
                            <h4 class="m-0">{{ __('Android link') }}</h4>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">{{ __('link') }}</label>
                                    <input type="text" name="android_link" value="{{ $settings['android_link'] ?? '' }}"
                                           class="form-control">
                                </div>
                            </div>

                            <div class="col-12 d-flex gap-3 mt-3">
                                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-md-6 mb-3 ms-0 me-auto" style="margin-top: 40px;">
                <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card exp-card-cont p-3 shadow">
                        <div class="card-header exp-card d-flex justify-content-between align-items-center">
                            <h4 class="m-0">{{ __('ios link') }}</h4>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">{{ __('link') }}</label>
                                    <input type="text" name="ios_link" value="{{ $settings['ios_link'] ?? '' }}"
                                           class="form-control">
                                </div>
                            </div>

                            <div class="col-12 d-flex gap-3 mt-3">
                                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-md-6 mb-3 ms-0 me-auto" style="margin-top: 40px;">
                <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="current_tab" value="">
                    <div class="card exp-card-cont p-3 shadow">
                        <div class="card-header exp-card d-flex justify-content-between align-items-center">
                            <h4 class="m-0">{{ __('huawei link') }}</h4>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">{{ __('link') }}</label>
                                    <input type="text" name="huawei_link" value="{{ $settings['huawei_link'] ?? '' }}"
                                           class="form-control">
                                </div>
                            </div>

                            <div class="col-12 d-flex gap-3 mt-3">
                                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
