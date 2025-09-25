<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{config('admin.title')}} | {{ trans('admin.login') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&display=swap" rel="stylesheet">

    @if(!is_null($favicon = Admin::favicon()))
        <link rel="shortcut icon" href="{{$favicon}}">
    @endif
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/font-awesome/css/font-awesome.min.css") }}">
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css") }}">
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/iCheck/flat/green.css") }}">
    <link rel="stylesheet" href="{{asset('css/dashboard.css')}}">

    <!--@include('css.dynamic-style')-->
    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>
            #forget-password:hover {
                text-decoration: underline;
            }
    </style>
</head>

<body class="hold-transition login-page" @if(config('admin.login_background_image'))style="background: url({{config('admin.login_background_image')}}) no-repeat;background-size: cover;"@endif>

<div class="login-box">
    <div class="login-logo">
        @php
        $logo = App\Models\Setting::where('key', 'app_logo')->first();
        $logo_url =  $logo?->value;
    @endphp
        <div><img src="{{ empty($logo)? asset('images/app-logo.png') : getImagePath($logo_url)}}" style="width: 150px;"></div>
        <div class="box-title">
            <a href="{{ admin_url('/') }}" style="color: var(--green-color);">{{__('dashboard.login.titleSuperAdmin')}}</a>
        </div>

    </div>

    <div class="login-box-body">
        <form action="{{ superadmin_url('login') }}" method="post">
            <div class="form-group has-feedback ">
            @if (@$errors && $errors->has('username'))
                @foreach($errors->get('username') as $message)
                    <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
                @endforeach
            @endif
                <input type="text" id="username" class="form-control input-lg  text-center" placeholder="{{ trans('admin.username') }}" name="username" value="{{ old('username') }}">
                <input type="hidden" name="url"  value="{{ @$test }}">
            </div>
            <div class="form-group has-feedback ">
            @if (@$errors && $errors->has('username'))
                @foreach($errors->get('username') as $message)
                    <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
                @endforeach
            @endif

                <input type="password" class="form-control input-lg text-center" placeholder="{{ trans('admin.password') }}" name="password">
            </div>
            @if(config('admin.auth.remember'))
                <div class="checkbox icheck text-center" dir="rtl">
                    <label>
                        <input type="checkbox" name="remember" value="1" {{ (!old('username') || old('remember')) ? 'checked' : '' }}>
                        {{ __('dashboard.login.remember') }}
                    </label>
                </div>
            @endif

              <div class="checkbox icheck text-center" dir="rtl">
                    <label>
                        <input type="checkbox" name="code" value="1" {{ (!old('username') || old('remember')) ? 'checked' : '' }}>
                        {{ __('please inter code sent to your whatsapp') }}
                    </label>
                </div>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <button type="submit" class="btn btn-success btn-block btn-lg btn-flat rounded submit">{{ trans('admin.login') }}</button>
            <!--
            <div class="form-group">
                <select class="form-control" id="locale">
                    @foreach($languages as $key => $language)
                        <option value="{{$key}}" {!! $key != $current ?: 'selected' !!}>{{$language}}</option>
                    @endforeach
                </select>
            </div>
            -->
        </form>
       <div class="language-switch text-center">
            <a href="#" id="language-switcher" style="color: var(--green-color);">{{__('dashboard.login.language.switch')}} <span style="font-weight: bold;">{{__('dashboard.login.language.lang')}}</span></a>
        </div>
                <div class="forget-password text-center" style="margin-top: 10px;">
                    <a href="" id="forget-password" 
                    style="color: var(--green-color); font-weight: bold; text-decoration: underline;">
                        {{ __('forget password') }}
                    </a>
                </div>
                <br>
                    <form action="{{ admin_url('change-password-view') }}" method="get" id="forget-password-form"
                        class="text-center" style="display: none; margin-top: 15px;">
                        @csrf
                        {{-- hidden username from login --}}
                        <input type="hidden" name="username" id="forget-username">

                        <label for="whatsapp_code" style="font-weight: bold; margin-bottom: 10px; display: block;">
                            {{ __('please enter the code sent to your WhatsApp') }}
                        </label>
                        <input type="text" class="form-control input-lg text-center"
                            id="whatsapp_code"
                            placeholder="{{ trans('code whatsapp') }}"
                            name="code"
                            value="{{ old('whatsapp_code') }}">

                        @if($errors->has('code'))
                            <label class="control-label text-danger">
                                {{ $errors->first('code') }}
                            </label>
                        @endif

                        <button type="submit" class="btn btn-success btn-block btn-lg btn-flat rounded submit" style="margin-top: 10px;">
                            {{ trans('send') }}
                        </button>
                    </form>
    </div>
</div>

<div class="rights text-center">{{ __('dashboard.login.rights') . config('app.name')}}</div>


<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js")}} "></script>
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js")}}"></script>
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/iCheck/icheck.min.js")}}"></script>
<script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_flat-green',
            radioClass: 'iradio_flat-green',
            increaseArea: '20%' // optional
        });
        $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('input[name="_token"]').attr('value')}});

        $('#language-switcher').on('click', function() {
            let current_locale = '{{$current}}';
            let locale = (current_locale == 'ar') ? 'en' : 'ar';
            $.post("{{ admin_url('/locale') }}",{locale: locale}, function () {
                location.reload();
            });
        });

        // Prevent double submission
        $('form').on('submit', function () {
            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).text('{{ trans('logging in') ?? 'Logging in...' }}');
        });

         document.getElementById('forget-password').addEventListener('click', function (e) {
                e.preventDefault();
                let form = document.getElementById('forget-password-form');
                form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
            });

           document.getElementById('forget-password').addEventListener('click', function (e) {
            e.preventDefault();

            let username = document.getElementById('username').value;

            if (!username) {
                alert("{{ __('Please enter your username first') }}");
                return;
            }

            // Send AJAX request to backend
          fetch("{{ superadmin_url('send-whatsapp-code') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ username: username })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    alert(data.message); 
                    document.getElementById('forget-password-form').style.display = "block";
                } else {
                    alert(data.message); 
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("{{ __('Something went wrong, please try again later.') }}");
            });
        });
            document.getElementById('forget-password').addEventListener('click', function (e) {
                e.preventDefault();
                // copy username into hidden field
                let username = document.getElementById('username').value;
                document.getElementById('forget-username').value = username;

                // show forget-password form
                document.getElementById('forget-password-form').style.display = 'block';
            });
    });
</script>
</body>
</html>
