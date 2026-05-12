<div id="realTimeSetting" class="settings-section">
    <form>
        @csrf
        <div class="form">
            <label class="d-block">{{ __('Sound & Video System Setting:') }}</label>
            <div class="row mt-4">
                <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                </form>

              

                           <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}"
                                method="POST">
                                @csrf
                              <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                                <!-- Zego Fields -->
                                <div class="col-md-6 mb-3 ms-0 me-auto">
                                    <div class="card p-9-px shadow real-time-card-height"  >
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h4 class="m-0 color-white">{{ __('UTD VOICE') }}</h4>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="form-group">
                                                    <label
                                                        for="zego_server_secret">{{ __('UTD App ID') }}:</label>
                                                    <input type="text" id="zego_server_secret"
                                                        name="utd_app_id" placeholder="{{ __('server_secret') }}"
                                                        value="{{ $utd_app_id }}" class="form-control"
                                                        required>
                                                </div>
                                            </div>

                                            <div class="col-md-10">
                                                <div class="form-group">
                                                    <label
                                                        for="zego_server_secret">{{ __('UTD API Key') }}:</label>
                                                    <input type="text" id="zego_server_secret_key"
                                                        name="utd_api_key" placeholder="{{ __('server_secret_key') }}"
                                                        value="{{ $utd_api_key }}" class="form-control"
                                                        required>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit"
                                            class="btn btn-primary mt-3 btn-save btn0bottom">{{ __('save') }}</button>
                                    </div>
                                </div>
                            </form>

                <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                    <div class="col-md-6 mb-3 ms-0 me-auto">
                        <div class="card p-9-px shadow real-time-card-height">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="m-0 color-white">{{ __('UTD-STREAM') }}</h4>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="utd_stream_app_id">{{ __('admin.app_id') }}:</label>
                                        <input type="text" id="utd_stream_app_id" name="utd_stream_app_id"
                                               placeholder="App ID"
                                               value="{{ $utd_stream_app_id }}" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="utd_stream_server_secret">{{ __('admin.server_secret') }}:</label>
                                        <input type="text" id="utd_stream_server_secret" name="utd_stream_server_secret"
                                               placeholder="Server Secret"
                                               value="{{ $utd_stream_server_secret }}" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="utd_stream_callback_secret">Callback Secret:</label>
                                        <input type="text" id="utd_stream_callback_secret" name="utd_stream_callback_secret"
                                               placeholder="Callback Secret"
                                               value="{{ $utd_stream_callback_secret ?? '' }}" class="form-control" required>
                                        <small class="form-text text-muted">{{ __('Used to verify webhook signatures') }}</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="utd_stream_webhook_url">Webhook URL:</label>
                                        <div style="display: flex; gap: 5px; align-items: center;">
                                            <input type="text" id="utd_stream_webhook_url"
                                                   value="{{ url('/api/utd-stream-webhook') }}"
                                                   class="form-control"
                                                   readonly
                                                   style="background-color: #f5f5f5; cursor: not-allowed; flex: 1;">
                                            <button class="btn btn-outline-secondary"
                                                    type="button"
                                                    onclick="copyWebhookUrl(event)"
                                                    title="Copy"
                                                    style="padding: 8px 12px;">
                                                <i class="fa fa-copy" style="font-size: 16px;"></i>
                                            </button>
                                        </div>
                                        <small class="form-text text-muted">{{ __('This URL is used by UTD-STREAM to send webhook events') }}</small>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3 btn-save btn0bottom">{{ __('save') }}</button>
                        </div>
                    </div>
                </form>

                <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                    <div class="col-md-6 mb-3 ms-0 me-auto">
                        <div class="card p-9-px shadow real-time-card-height">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="m-0 color-white">{{ __('admin.Zego') }}</h4>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="hidden" name="provider" value="zego">
                                        <label for="zego_server_secret">{{ __('admin.server_secret') }}:</label>
                                        <input type="text" id="zego_server_secret" name="zego_server_secret"
                                               placeholder="server_secret" value="{{ $zego_server_secret }}"
                                               class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="zego_app_id">{{ __('admin.app_id') }}:</label>
                                        <input type="text" id="zego_app_id" name="zego_app_id" placeholder="app_id"
                                               value="{{ $zego_app_id }}" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="app_sign">{{ __('admin.app_sign') }}:</label>
                                        <input type="text" id="app_sign" name="app_sign" placeholder="app_sign"
                                               value="{{ $app_sign }}" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group d-flex align-items-center gap-2">
                                        <label style="visibility: hidden;">.</label>
                                        <input type="hidden" name="zego_filter_enabled" value="0">
                                        <input type="checkbox" name="zego_filter_enabled" value="1" data-bootstrap-switch
                                            {{ $zego_filter_enabled ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <script>
                                    function initZegoSwitch() {
                                        $('input[data-bootstrap-switch]').each(function () {
                                            $(this).bootstrapSwitch('state', $(this).prop('checked'), true);
                                        });
                                    }

                                    $(document).ready(initZegoSwitch);
                                    $(document).on('pjax:success', initZegoSwitch);
                                </script>
                            </div>

                            <button type="submit" class="btn btn-primary mt-3 btn-save btn0bottom">{{ __('save') }}</button>
                        </div>
                    </div>
                </form>

        
            </div>
        </div>
    </form>

    <form action="{{ route('admin.update-agora-zego') }}" method="POST">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
        <div class="form">
            <label class="d-block">{{ __('Sound System Setting:') }}</label>
            <div class="row mt-4">
             

                <div class="col-md-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('admin.Zego') }}</h4>
                        <div class="d-flex align-items-center">
                            <input type="radio" id="zegoSoundRadio" class="custom-radio libraryRealTime"
                                   name="sound_library"
                                   value="1" {{ $soundLibrary == '1' ? 'checked' : '' }}>
                            <label for="zegoSoundRadio" class="switch"></label>
                        </div>
                    </div>
                </div>

                

            

                <div class="col-md-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('UTD VOICE') }}</h4>
                        <div class="d-flex align-items-center">
                            <input type="radio" id="utdZegoSoundRadio" class="custom-radio libraryRealTime"
                                   name="sound_library"
                                   value="3" {{ $soundLibrary == '3' ? 'checked' : '' }}>
                            <label for="utdZegoSoundRadio" class="switch"></label>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('UTD-STREAM') }}</h4>
                        <div class="d-flex align-items-center">
                            <input type="radio" id="utdStreamSoundRadio" class="custom-radio libraryRealTime"
                                   name="sound_library"
                                   value="4" {{ $soundLibrary == '4' ? 'checked' : '' }}>
                            <label for="utdStreamSoundRadio" class="switch"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form action="{{ route('admin.update-agora-zego') }}" method="POST">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
        <div class="form">
            <label class="d-block">{{ __('Video System Setting:') }}</label>
            <div class="row mt-4">
          

                <div class="col-md-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('admin.Zego') }}</h4>
                        <div class="d-flex align-items-center">
                            <input type="radio" id="zegoVideoRadio" class="custom-radio libraryRealTime"
                                   name="video_library"
                                   value="1" {{ $videoLibrary == '1' ? 'checked' : '' }}>
                            <label for="zegoVideoRadio" class="switch"></label>
                        </div>
                    </div>
                </div>

            

                <div class="col-md-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('UTD VOICE') }}</h4>
                        <div class="d-flex align-items-center">
                            <input type="radio" id="utdZegoVideoRadio" class="custom-radio libraryRealTime"
                                   name="video_library"
                                   value="3" {{ $videoLibrary == '3' ? 'checked' : '' }}>
                            <label for="utdZegoVideoRadio" class="switch"></label>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('UTD-STREAM') }}</h4>
                        <div class="d-flex align-items-center">
                            <input type="radio" id="utdStreamVideoRadio" class="custom-radio libraryRealTime"
                                   name="video_library"
                                   value="4" {{ $videoLibrary == '4' ? 'checked' : '' }}>
                            <label for="utdStreamVideoRadio" class="switch"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form action="{{ route('admin.update-agora-zego') }}" method="POST">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
        <div class="form">
            <label class="d-block">{{ __('Live System Setting:') }}</label>
            <div class="row mt-4">
                <div class="col-md-4 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('admin.RTC') }}</h4>
                        <div class="d-flex align-items-center">
                            <input type="radio" id="rtcLiveRadio" class="custom-radio libraryRealTime"
                                   name="live_library"
                                   value="0" {{ $liveLibrary == '0' ? 'checked' : '' }}>
                            <label for="rtcLiveRadio" class="switch"></label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('admin.CDN') }}</h4>
                        <div class="d-flex align-items-center">
                            <input type="radio" id="cdnLiveRadio" class="custom-radio libraryRealTime"
                                   name="live_library"
                                   value="1" {{ $liveLibrary == '1' ? 'checked' : '' }}>
                            <label for="cdnLiveRadio" class="switch"></label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('admin.L3') }}</h4>
                        <div class="d-flex align-items-center">
                            <input type="radio" id="l3LiveRadio" class="custom-radio libraryRealTime"
                                   name="live_library"
                                   value="2" {{ $liveLibrary == '2' ? 'checked' : '' }}>
                            <label for="l3LiveRadio" class="switch"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="autoPreviewForm" action="{{ route('admin.update-agora-zego') }}" method="POST">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
        <div class="form">
            <label class="d-block">{{ __('admin.is_preview') }}</label>
            <div class="row mt-4">
                <div class="col-md-4 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('admin.is_preview') }}</h4>
                        <div class="d-flex align-items-center">
                            <input type="hidden" name="is_auto_preview" value="0">
                            <input type="checkbox" name="is_auto_preview" value="1" data-bootstrap-switch
                                {{ $is_auto_preview ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        function initIsPreviewSwitch() {
            const $switch = $('input[name="is_auto_preview"][data-bootstrap-switch]');

            $switch.each(function () {
                $(this).bootstrapSwitch('state', $(this).prop('checked'), true);
            });

            $switch.on('switchChange.bootstrapSwitch', function (event, state) {
                const form = $('#autoPreviewForm');
                const formData = form.serializeArray();
                const newValue = state ? 1 : 0;
                formData.push({name: 'is_auto_preview', value: newValue});

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: formData,
                    success: function () {
                        console.log('is_auto_preview updated to', newValue);
                    },
                    error: function (xhr) {
                        console.error('Error updating is_auto_preview:', xhr.responseText);
                    }
                });
            });
        }

        $(document).ready(initIsPreviewSwitch);
        $(document).on('pjax:success', initIsPreviewSwitch);

        // Copy Webhook URL Function
        function copyWebhookUrl(event) {
            const webhookInput = document.getElementById('utd_stream_webhook_url');
            const btn = event.currentTarget;

            // Create a temporary input to select and copy
            const tempInput = document.createElement('input');
            tempInput.value = webhookInput.value;
            document.body.appendChild(tempInput);
            tempInput.select();
            tempInput.setSelectionRange(0, 99999);

            try {
                document.execCommand('copy');
                document.body.removeChild(tempInput);

                // Success notification
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-check"></i>';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-secondary');

                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            } catch (err) {
                console.error('Failed to copy: ', err);
                document.body.removeChild(tempInput);
                alert('Failed to copy URL');
            }
        }
    </script>
</div>
