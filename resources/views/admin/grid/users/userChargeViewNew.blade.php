<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">{{ __('admin.Actions') }}</h3>

        <div class="box-tools">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        /* Hide default HTML checkbox */
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* The slider */
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>

<div class="box-body no-padding">
    <div class="row">
        <div class="col-md-4">
            @php
                $make_rooms_top = settings()->get('make_rooms_top');
            @endphp
            <ul class="nav nav-pills nav-stacked">
                <li>
                    <a href="javascript:void(0);" class="ban_user_action">
                        <i class="fa fa-dollar text-red"></i>
                        <label for="make_rooms_top">{{__("dashboard.roomOn")}}</label>
                        <label class="switch">
                            <input type="checkbox" id="stopChargeCheckbox" {{ $make_rooms_top == 1 ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-md-4">
            @php
                $stop_charge = settings()->get('stop_charge');
            @endphp
            <ul class="nav nav-pills nav-stacked">
                <li>
                    <a href="javascript:void(0);" class="ban_user_action">
                        <i class="fa fa-dollar text-red"></i>
                        <label for="stop_charge">{{__('dashboard.frazeCharge')}}</label>

                        <label class="switch">
                            <input type="checkbox" id="stopCharge"
                                {{ $stop_charge == 1 ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-md-4">
            @php
                $stop_invite_code = settings()->get('stop_invite_code');
            @endphp
            <ul class="nav nav-pills nav-stacked">
                <li>
                    <a href="javascript:void(0);" class="ban_user_action">
                        <i class="fa fa-dollar text-red"></i>
                        <label for="stop_invite_code">{{__("dashboard.closeCose")}}</label>
                        <label class="switch">
                            <input type="checkbox" id="stopInviteCode"
                                {{ $stop_invite_code == 1  ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            @php
                $transfer_salary = settings()->get('transfer_salary');
            @endphp
            <ul class="nav nav-pills nav-stacked">
                <li>
                    <a href="javascript:void(0);" class="ban_user_action">
                        <i class="fa fa-dollar text-red"></i>
                        <label for="transfer_salary">{{__("dashboard.transSalary")}}</label>
                        <label class="switch">
                            <input type="checkbox" id="stopTransferSalary" {{ $transfer_salary == 1 ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

    <!-- /.box-body -->

    <script>
        $(document).ready(function() {
            // Get the checkbox element
            var stopChargeCheckbox = $('#stopChargeCheckbox');

            // Attach a change event listener to the checkbox
            stopChargeCheckbox.on('change', function() {
                // Get the current state of the checkbox
                var isChecked = stopChargeCheckbox.is(':checked');
                // Make an API call here, for example, using jQuery.ajax
                $.ajax({
                    url: '/send-request-make-rooms-top',
                    method: 'POST',
                    data: {
                        make_rooms_top: isChecked
                    },
                    success: function(response) {
                        // Handle the API response if needed
                        console.log(response);
                    },
                    error: function(error) {
                        // Handle errors if the API call fails
                        console.error(error);
                    }
                });
            });



             var stopCharge = $('#stopCharge');

            // Attach a change event listener to the checkbox
            stopCharge.on('change', function() {
                // Get the current state of the checkbox
                var isChecked = stopCharge.is(':checked');
                // Make an API call here, for example, using jQuery.ajax
                $.ajax({
                    url: '/send-request-stop-charge',
                    method: 'POST',
                    data: {
                        stop_charge: isChecked
                    },
                    success: function(response) {
                        // Handle the API response if needed
                        console.log(response);
                    },
                    error: function(error) {
                        // Handle errors if the API call fails
                        console.error(error);
                    }
                });
            });

            var stopInviteCode = $('#stopInviteCode');

            // Attach a change event listener to the checkbox
            stopInviteCode.on('change', function() {
                // Get the current state of the checkbox
                var isChecked = stopInviteCode.is(':checked');
                // Make an API call here, for example, using jQuery.ajax
                $.ajax({
                    url: 'send-request-invite-code',
                    method: 'POST',
                    data: {
                        stop_invite_code: isChecked
                    },
                    success: function(response) {
                        // Handle the API response if needed
                        console.log(response);
                    },
                    error: function(error) {
                        // Handle errors if the API call fails
                        console.error(error);
                    }
                });
            });


            var stopTransferSalary = $('#stopTransferSalary');

            // Attach a change event listener to the checkbox
            stopTransferSalary.on('change', function() {
                // Get the current state of the checkbox
                var isChecked = stopTransferSalary.is(':checked');
                // Make an API call here, for example, using jQuery.ajax
                $.ajax({
                    url: '/send-request-transfer-salary',
                    method: 'POST',
                    data: {
                        transfer_salary: isChecked
                    },
                    success: function(response) {
                        // Handle the API response if needed
                        console.log(response);
                    },
                    error: function(error) {
                        // Handle errors if the API call fails
                        console.error(error);
                    }
                });
            });
        });
    </script>

</div>
