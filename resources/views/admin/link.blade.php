
<body>
    <div class="all-page">
        <div class="settings-content">
        

   <div class="modal fade"  id="UpdateCredential_zego_model"  tabindex="-1"  tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-6" role="document">
        <div class="modal-content border-0">
            <div class="modal-content position-relative">
                <div class="position-absolute top-0 end-0 mt-2 me-2 z-index-1">
                    <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                    <div class="modal-body p-0">
                        
                        <div class="p-4">
                            <div class="row" style="justify-content:space-evenly">
                               
                                <!-- Zego Settings -->
                                <div class="col-lg-12">
                                    <h5>{{ __('Room cup target ') }}</h5>
                                </div>
                                <div class="col-lg-6 form-group mb-3">
                                    <label class="form-label">{{ __('validation.zego_app_id') }}</label>
                                    <input type="text" class="form-control" name="zego_settings[app_id]" required>
                                </div>

                                <div class="col-lg-6 form-group mb-3">
                                    <label class="form-label">{{ __('validation.zegoClientId') }}</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="zego_settings[zego_client_id]" id="zego_client_id" readonly>
                                        <button type="button" class="btn btn-outline-secondary" id="copy-zego-id">{{__('validation.Copy')}}</button>
                                    </div>
                                </div>

                                <hr class="my-4"> <!-- فاصل بين الأقسام -->


                               
                                 <div class="col-lg-12">
                                    <h5>{{ __('Super admin details') }}</h5>
                                </div>
                                <div class="col-lg-6 form-group mb-3">
                                    <label class="form-label">{{ __('validation.zego_app_id') }}</label>
                                    <input type="text" class="form-control" name="zego_settings[app_id]" required>
                                </div>

                                <div class="col-lg-6 form-group mb-3">
                                    <label class="form-label">{{ __('validation.zegoClientId') }}</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="zego_settings[zego_client_id]" id="zego_client_id" readonly>
                                        <button type="button" class="btn btn-outline-secondary" id="copy-zego-id">{{__('validation.Copy')}}</button>
                                    </div>
                                </div>
                                

                            </div>
                        </div>
                    </div>
                    


            </div>
        </div>
    </div>
</div>
         
        </div>
    </div>
</body>