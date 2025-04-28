<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" class="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ Admin::title() }} @if($header) | {{ $header }}@endif</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @if(!is_null($favicon = Admin::favicon()))
    <link rel="shortcut icon" href="{{$favicon}}">
    @endif
    {!! Admin::css() !!}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css"/>

    <script src="{{ Admin::jQuery() }}"></script>
    {!! Admin::headerJs() !!}
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body class="hold-transition {{config('admin.skin')}} {{join(' ', config('admin.layout'))}}">

@if($alert = config('admin.top_alert'))
    <div style="text-align: center;padding: 5px;font-size: 12px;background-color: #ffffd5;color: #ff0000;">
        {!! $alert !!}
    </div>
@endif

<div class="wrapper">

    @include('admin::partials.header')

    @include('admin::partials.sidebar')

    <div class="content-wrapper" id="pjax-container">
        {!! Admin::style() !!}
        <div id="app">
        @yield('content')
        </div>
        {!! Admin::script() !!}
        {!! Admin::html() !!}
    </div>

    @include('admin::partials.footer')

</div>

<button id="totop" title="Go to top" style="display: none;"><i class="fa fa-chevron-up"></i></button>

<script>
    function LA() {}
    LA.token = "{{ csrf_token() }}";
    LA.user = @json($_user_);

    document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("main-sidebar");
    const toggleBtn = document.querySelector(".sidebar-toggle");

    toggleBtn.addEventListener("click", function (event) {
        event.preventDefault();
        sidebar.classList.toggle("active");
    });

    // إغلاق القائمة عند الضغط خارجها
    document.addEventListener("click", function (event) {
        if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
            sidebar.classList.remove("active");
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("main-sidebar");
    const contentWrapper = document.getElementById("pjax-container");
    const toggleButton = document.querySelector(".sidebar-toggle");

    function updateLayout() {
        if (sidebar.classList.contains("active")) {
            document.body.classList.remove("sidebar-collapsed");
        } else {

            document.body.classList.add("sidebar-collapsed");
        }
    }

    // استدعاء عند النقر على زر التبديل
    toggleButton.addEventListener("click", function () {

        sidebar.classList.toggle("active");
        sidebar.classList.toggle("active_hide");
        contentWrapper.classList.toggle("content-wrapper-rtl");
        updateLayout();
    });

    // تحديث عند تحميل الصفحة
    updateLayout();
});

</script>

<!-- REQUIRED JS SCRIPTS -->
{!! Admin::js() !!}

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

<script>
    function initPhoneInput() {
        const input = document.querySelector("#phone-input");
        if (input && !input.classList.contains('iti-initialized')) {
            const iti = window.intlTelInput(input, {
                separateDialCode: true,
                preferredCountries: ["eg"],
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
            });
            // Mark as initialized so it's not called twice
            input.classList.add('iti-initialized');
            // On submit, format the value
            const form = input.closest('form');
            if (form) {
                form.addEventListener('submit', function() {
                    if (iti) {
                        input.value = iti.getNumber();
                    }
                });
            }
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initPhoneInput, 100); // Wait 100ms to ensure rendering
    });
    // On Laravel-Admin: If you use tabs, modals, or custom reloads,
    // re-init on AJAX finished events:
    $(document).on('pjax:complete', function() {
        setTimeout(initPhoneInput, 100);
    });
    $(document).on('click', '.add-form-row', function() {
        setTimeout(initPhoneInput, 100);
    });
</script>
</body>
</html>
