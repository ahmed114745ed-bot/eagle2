<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="utf-8">
    <title>{{ config('admin.title') }} | {{ trans('admin.login') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF meta -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/font-awesome/css/font-awesome.min.css") }}">
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css") }}">
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/iCheck/flat/green.css") }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <style>
        #forget-password:hover { text-decoration: underline; cursor: pointer; }
    </style>
</head>

<body class="hold-transition login-page" @if(config('admin.login_background_image')) style="background: url({{config('admin.login_background_image')}}) no-repeat;background-size: cover;" @endif>

<div class="login-box">
    <div class="login-logo">
        @php
            $logo = App\Models\Setting::where('key', 'app_logo')->first();
            $logo_url = $logo?->value;
        @endphp
        <div><img src="{{ empty($logo) ? asset('images/app-logo.png') : getImagePath($logo_url) }}" style="width:150px;"></div>
        <div class="box-title">
            <a href="{{ areaManager_url('/') }}" style="color: var(--green-color);">{{ __('dashboard.login.titleAreaManager') }}</a>
        </div>
    </div>

    <div class="login-box-body">

        {{-- Existing errors / success --}}
        @if($errors->any())
            <div class="alert alert-danger text-center">
                @foreach($errors->all() as $error)
                    <div><i class="fa fa-times-circle-o"></i> {{ $error }}</div>
                @endforeach
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success text-center">
                <i class="fa fa-check-circle-o"></i> {{ session('success') }}
            </div>
        @endif

        <form action="{{ areaManager_url('login') }}" method="post" id="login-form">
            @csrf
            <div class="form-group has-feedback">
                <input type="text" id="username" name="username"
                       class="form-control input-lg text-center" placeholder="{{ trans('admin.username') }}"
                       value="{{ old('username') }}" required>
            </div>

                <!-- <div class="form-group has-feedback">
                    <select id="type" name="type" class="form-control input-lg text-center" required>
                        <option value="area-manager"selected>{{ __('Area manager') }}</option>
                        <option value="sub_area_manager">{{ __('Sub area manager') }}</option>
                    </select>
                </div> -->

            <div class="form-group has-feedback">
                <input type="password" name="password"
                       class="form-control input-lg text-center" placeholder="{{ trans('admin.password') }}" required>
            </div>

               

            @if(config('admin.auth.remember'))
                <div class="checkbox icheck text-center" dir="rtl">
                    <label>
                        <input type="checkbox" name="remember" value="1" {{ (!old('username') || old('remember')) ? 'checked' : '' }} >
                        {{ __('dashboard.login.remember') }}
                    </label>
                </div>
            @endif

            <button type="submit" class="btn btn-success btn-block btn-lg btn-flat rounded submit">{{ trans('admin.login') }}</button>
        </form>

        <div class="language-switch text-center">
            <a href="#" id="language-switcher" style="color: var(--green-color);">{{__('dashboard.login.language.switch')}} <span style="font-weight: bold;">{{__('dashboard.login.language.lang2')}}</span></a>
        </div>
                <div class="forget-password text-center" style="margin-top: 10px;">
                    <a href="" id="forget-password"
                    style="color: var(--green-color); font-weight: bold; text-decoration: underline;">
                        {{ __('forget password') }}
                    </a>
                </div>
                <br>


        <form action="{{ areaManager_url('change-password-view') }}" method="get" id="forget-password-form" style="display:none; margin-top:15px;">
            @csrf
            <input type="hidden" name="username" id="forget-username">
            <input type="hidden" name="type" id="forget-type">
            <label for="whatsapp_code" style="font-weight:bold; display:block; margin-bottom:8px;">
                    {{ __('dashboard.login.enter_code') }}
            </label>
            <input type="text" id="whatsapp_code" name="code" class="form-control input-lg text-center" placeholder="{{ __('dashboard.login.whatsapp_code') }}">
            <div style="margin-top:10px;">
                <button type="submit" class="btn btn-success btn-block btn-lg btn-flat rounded submit">{{ __('Validation') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="rights text-center">{{ __('dashboard.login.rights') . ' ' . config('app.name') }}</div>

<!-- مودال الخطأ -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-hidden="true" >
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content" >
      <div class="modal-header  text-white"  style="background-color: #ff0000 !important;">
        <h5 class="modal-title"><i class="fa fa-exclamation-circle"></i> {{ __('dashboard.login.error') }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body text-center">
        <p id="errorModalText" style="margin:0;"></p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('dashboard.login.ok') }}</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fa fa-whatsapp"></i> {{ __('dashboard.login.confirm_send') }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body text-center">
        <p id="confirmText" style="margin:0;"></p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('dashboard.login.cancel') }}</button>
        <button type="button" class="btn btn-success" id="confirmSendBtn">{{ __('dashboard.login.confirm') }}</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fa fa-check-circle"></i> {{ __('dashboard.login.success') }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body text-center">
        <p id="successModalText" style="margin:0;"></p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('dashboard.login.ok') }}</button>
      </div>
    </div>
  </div>
</div>


<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js") }}"></script>
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js") }}"></script>
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/iCheck/icheck.min.js") }}"></script>

<script>
$(document).ready(function () {
    // iCheck init (if used)
    if ($.fn.iCheck) {
        $('input').iCheck({
            checkboxClass: 'icheckbox_flat-green',
            radioClass: 'iradio_flat-green',
            increaseArea: '20%'
        });
    }

    // AJAX CSRF setup (from meta)
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // language switcher (kept as-is)
    $('#language-switcher').on('click', function (e) {
        e.preventDefault();
        let current_locale = '{{$current}}';
        let locale = (current_locale === 'ar') ? 'en' : 'ar';
        $.post("{{ admin_url('/locale') }}", { locale: locale }, function () {
            location.reload();
        });
    });

    // prevent double submit for forms
    $('#login-form, #forget-password-form').on('submit', function () {
        var $btn = $(this).find('button[type="submit"]');

        if ($(this).attr('id') === 'forget-password-form') {
            $btn.text("{{ __('dashboard.login.loading.check_code') }}");
        } else {
            $btn.text("{{ __('dashboard.login.loading.logging_in') }}");
        }
    });

 console.log("Proceeding with forget password for:", username, type);
    // نقر على "نسيت كلمة المرور"
    $('#forget-password').on('click', function (e) {
        e.preventDefault();

        let username = $('#username').val()?.trim();
         let type = $('#type').val()?.trim();

        // 1) لو ما فيه username -> عرض مودال خطأ
        if (!username) {
            $('#errorModalText').text("{{ __('dashboard.login.error.enter_username_first') }}");
            $('#errorModal').modal('show');
            return;
        }
        if (!type) {
            $('#errorModalText').text("{{ __('login.error.enter_type') }}");
            $('#errorModal').modal('show');
            return;
        }

        $.ajax({
            url: "{{ areaManager_url('send-whatsapp-code-preview') }}",
            method: "POST",
            data: { username: username,
                type:type
             },
            dataType: "json",
            beforeSend: function () {
                // optional: show loading state
                $('#confirmText').text("{{ __('dashboard.login.loading.prepare') }}");
                $('#confirmModal').modal('show');
            },
            success: function (data) {
                if (!data || !data.status) {
                    $('#confirmModal').modal('hide');
                    $('#errorModalText').text(data?.message || "{{ __('login.error.try_again') }}");
                    $('#errorModal').modal('show');
                    return;
                }

                $('#confirmText').text("{{ __('dashboard.login.whatsapp.send_to_number') }} " + (data.masked_number || ''));
                $('#confirmModal').modal('show');

                $('#confirmSendBtn').off('click').on('click', function () {
                    var $btn = $(this);
                    $btn.prop('disabled', true).text("{{ __('dashboard.login.loading.sending') }}");

                    $.ajax({
                        url: "{{ areaManager_url('send-whatsapp-code') }}",
                        method: "POST",
                        data: { username: username,
                            type:type
                         },
                        dataType: "json",
                        success: function (res) {
                            $btn.prop('disabled', false).text("{{ __('dashboard.login.confirm') }}");
                            $('#confirmModal').modal('hide');

                            if (res && res.success) {
                                $('#successModalText').text(res.message || "{{ __('dashboard.login.whatsapp.sent') }}");
                                $('#successModal').modal('show');

                                $('#forget-username').val(username);
                                $('#forget-type').val(type);

                                $('#forget-password-form').fadeIn();
                                document.getElementById('forget-password-form').style.display = 'block';

                            } else {
                                $('#errorModalText').text(res?.message || "{{ __('dashboard.login.whatsapp.failed') }}");
                                $('#errorModal').modal('show');
                            }
                        },
                        error: function (xhr, status, error) {
                            $btn.prop('disabled', false).text("{{ __('dashboard.login.confirm') }}");
                            $('#confirmModal').modal('hide');
                            let res = xhr.responseJSON;
                            let errMsg = res?.message
                                ? "{{ __('') }}" + res.message
                                : xhr.responseText || error || "{{ __('dashboard.login.whatsapp.failed') }}";

                            $('#errorModalText').text(errMsg);
                            $('#errorModal').modal('show');
                            console.error('send-whatsapp-code error:', xhr.responseText || error);
                        }
                    });
                });
            },
            error: function (xhr, status, error) {
                $('#confirmModal').modal('hide');
                $('#errorModalText').text("{{ __('dashboard.login.error.user_fetch') }}");
                $('#errorModal').modal('show');
                console.error('preview error:', xhr.responseText || error);
            }
        });
    });
});

$('#forget-password-form').on('submit', function (e) {
    e.preventDefault(); // منع إعادة تحميل الصفحة

    var $form = $(this);
    var $btn  = $form.find('button[type="submit"]');

    var username = $('#forget-username').val();
    var code     = $('#whatsapp_code').val();
    var type = $('#forget-type').val();


    $btn.text("{{ __('dashboard.login.loading.check_code') }}");

    $.ajax({
        url: "{{ areaManager_url('verify-whatsapp-code') }}", // نفس الـ action بتاع الفورم
        method: "GET", // زي ما انت كاتب في الفورم
        data: {
            username: username,
            code: code,
             type:type,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: "json",
        success: function (res) {
            $btn.text("{{ __('Validation') }}").prop('disabled', false);

            if (res.success) {
                $('#successModalText').text(res.message);
                $('#successModal').modal('show');

                if (res.redirect) {
                    setTimeout(function () {
                        window.location.href = res.redirect;
                    }, 1500);
                }
            } else {
                $('#errorModalText').text(res.message);
                $('#errorModal').modal('show');
            }
        },
        error: function (xhr, status, error) {
            $('#errorModalText').text(xhr.responseJSON?.message || "{{ __('حدث خطأ أثناء التحقق، حاول مرة أخرى') }}");
            $('#errorModal').modal('show');
            console.error('verify-code error:', xhr.responseText || error);
        },
        complete: function () {
            // رجع النص الأصلي للزر بعد الانتهاء
            $btn.text("{{ __('Validation') }}");
        }
    });
});
</script>
</body>
</html>
