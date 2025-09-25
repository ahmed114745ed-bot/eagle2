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
            <a href="{{ admin_url('/') }}" style="color: var(--green-color);">{{__('change password')}}</a>
        </div>

    </div>

    <div class="login-box-body">
        <form action="{{ superadmin_url('change-password') }}" method="post">
            @csrf

            <div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">
                @if($errors->has('password'))
                    @foreach($errors->get('password') as $message)
                        <label class="control-label" for="inputError">
                            <i class="fa fa-times-circle-o"></i>{{ $message }}
                        </label><br>
                    @endforeach
                @endif

                <label for="password" style="font-weight: bold; margin-bottom: 10px; display: block;">
                    {{ __('please enter new password') }}
                </label>
                <input type="password" class="form-control input-lg text-center" 
                    placeholder="{{ trans('admin.password') }}" 
                    name="password">
                <input type="hidden" name="username" value="{{ @$userName }}">
            </div>

            <button type="submit" class="btn btn-success btn-block btn-lg btn-flat rounded submit">
                {{ trans('change') }}
            </button>
        </form>

       

    </div>
</div>




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

       


    });
</script>
</body>
</html>
