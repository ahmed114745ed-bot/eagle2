<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-messaging-compat.js"></script>
<script src="https://js.pusher.com/8.2/pusher.min.js"></script>

<script>
    window.PUSHER_CONFIG = @json(config('broadcasting.connections.pusher'));
    window.ADMIN_TYPE = @json(Auth::user()->type);
    window.ADMIN_ID = @json(Auth::user()->id);
    window.firebaseConfig = {
        apiKey: "{{ config('firebase.apiKey') }}",
        authDomain: "{{ config('firebase.authDomain') }}",
        projectId: "{{ config('firebase.projectId') }}",
        storageBucket: "{{ config('firebase.storageBucket') }}",
        messagingSenderId: "{{ config('firebase.messagingSenderId') }}",
        appId: "{{ config('firebase.appId') }}",
        vapidKey: "{{ config('firebase.vapid_key') }}"
    };
    window.ADMIN_ID = @json(Auth::user()->id);

</script>



<meta name="csrf-token" content="{{ csrf_token() }}">



</script>

   <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-no {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: modal-appear 0.3s ease-out;
        }

        @keyframes modal-appear {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #e9ecef;
            background-color: #f8f9fa;
        }

        .modal-header h5 {
            margin: 0;
            font-weight: 600;
            color: #343a40;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
            transition: color 0.2s;
            line-height: 1;
        }

        .close-btn:hover {
            color: #343a40;
        }

        .modal-body2 {
            padding: 0;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 14px 20px;
            border-bottom: 1px solid #f1f3f4;
            transition: background-color 0.2s;
            cursor: pointer;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item.unread {
            background-color: #e7f1ff;
        }

        .notification-item.unread:hover {
            background-color: #dbe9fd;
        }

        .notification-title {
            font-weight: 500;
            margin-bottom: 4px;
            color: #212529;
        }

        .notification-time {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .modal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-top: 1px solid #e9ecef;
            background-color: #f8f9fa;
        }

        .btn-footer {
            border-radius: 6px;
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.2s;
        }

        .btn-mark-all {
            background-color: var(--primary-color);
            border: 1px solid #0d6efd;
            color: white;
        }

        .btn-mark-all:hover {
            background-color: var(--primary-color);
            border-color: #0a58ca;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
        }

        .btn-show-more {
            background-color: var(--primary-color);
            border: 1px solid #6c757d;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-show-more:hover {
            background-color:var(--primary-color);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.2);
        }

        .btn-show-more i {
            margin-left: 6px;
            font-size: 0.9em;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .p-3 {
            padding: 1rem !important;
        }

        #preview-buttons-wrapper {
            display: inline-block;
            vertical-align: middle;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .modal-no {
                width: 95%;
            }

            .modal-footer {
                flex-direction: column;
                gap: 12px;
            }

            .btn-footer {
                width: 100%;
            }
        }
    </style>
<header class="main-header">

<a href="{{ admin_url('/') }}" class="logo">
        <span class="logo-mini">{!! config('admin.logo-mini', config('admin.name')) !!}</span>
        <span class="logo-lg">{!! config('admin.logo', config('admin.name')) !!}</span>
    </a>
    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        @php
            $areaManagers = \Modules\AreaManager\Entities\AreaManager::select(['id','name','username','avatar'])->get();
            $selectAreaManagerId = session('area_manager_id') ?? request('area_manager_id');
            $selectedAreaManager   = $areaManagers->firstWhere('id', (int) $selectAreaManagerId);
            $country   = \App\Models\Country::find(Admin::user()->country_id);

            $countries = collect();
            if ($selectAreaManagerId) {
                $areaManager = \Modules\AreaManager\Entities\AreaManager::find($selectAreaManagerId);
                if ($areaManager && method_exists($areaManager, 'countries')) {
                    $countries = $areaManager->countries()
                        ->select(['countries.id', 'countries.name', 'countries.flag'])
                        ->get();
                }
            } else {
                $countries = \App\Models\Country::select(['id', 'name', 'flag'])->get();
            }

            $authId = auth()->user()->type == 'area-manager' ? auth()->id() : auth()->user()->parent_id;
            $authAdmin = \Modules\AreaManager\Entities\AreaManager::find($authId);

            if ($authAdmin && method_exists($authAdmin, 'countriesQuery')) {
                $areaManagerCountries = $authAdmin->countriesQuery()
                    ->select(['id', 'name', 'flag'])
                    ->get();
            } else {
                $areaManagerCountries = collect();
            }

            $selectedCountryId = session('filter_country_id') ?? request('filter_country_id') ?? Admin::user()->country_id;
            $selectedCountry = $countries->firstWhere('id', (int) $selectedCountryId);

            $selectedAreaManagerCountryId = session('area_manager_country_id') ?? request('area_manager_country_id') ?? Admin::user()->country_id;
            $selectedAreaManagerCountry   = $areaManagerCountries->firstWhere('id', (int) $selectedAreaManagerCountryId);
        @endphp

        @if (request()->is('admin*'))
            <a class="nav-item select-country">
                <select id="area-Manager-select" class="form-control" style="width:190px;">
                    <option value="">{{ __('Select area manager') }}</option>
                    @foreach($areaManagers as $areaManager)
                        <option
                            value="{{ $areaManager->id }}"
                            data-flag="{{ getImagePath($areaManager->avatar) }}"
                            {{ (string)$selectAreaManagerId === (string)$areaManager->id ? 'selected' : '' }}>
                            {{ $areaManager->name ?? $areaManager->username }}
                        </option>
                    @endforeach
                </select>
            </a>
            <a class="nav-item select-country">
                <select id="country-select" class="form-control" style="width:190px;">
                    <option value="">{{ __('Select Country...') }}</option>
                    @foreach($countries as $currentCountry)
                        <option
                            value="{{ $currentCountry->id }}"
                            data-flag="{{ getImagePath($currentCountry->flag) }}"
                            {{ (string)$selectedCountryId === (string)$currentCountry->id ? 'selected' : '' }}>
                            {{ $currentCountry->name }}
                        </option>
                    @endforeach
                </select>
            </a>
        @endif

        @if (request()->is('areaManager*'))
            <a class="nav-item select-country">
                <select id="country-select" class="form-control" style="width:190px;">
                    <option value="">{{ __('Select Country...') }}</option>
                    @foreach($areaManagerCountries as $currentCountry)
                        <option
                            value="{{ $currentCountry->id }}"
                            data-flag="{{ getImagePath($currentCountry->flag) }}"
                            {{ (string)$selectedAreaManagerCountryId === (string)$currentCountry->id ? 'selected' : '' }}>
                            {{ $currentCountry->name }}
                        </option>
                    @endforeach
                </select>
            </a>
        @endif
        <script>window.enableCountryHeader = true;</script>


        <ul class="nav navbar-nav hidden-sm visible-lg-block">
        {!! Admin::getNavbar()->render('left') !!}
        </ul>

        <div class="navbar-custom-menu">

            @if (Admin::user()->type == 'superadmin' && $country && $country->flag)
                <img src="{{ getImagePath($country->flag) }}"
                     class="flag-image"
                     alt="flag Image"
                     title="{{ app()->getLocale() === 'ar' ? $country->name : $country->e_name }}">
            @endif

            <ul class="nav navbar-nav">

            <ul class="nav navbar-nav hidden-sm visible-lg-block" style="    padding: 0px !important;">
        @if (!Admin::user()->type || Admin::user()->type == '')

                @endif


                @php
                    $admin = Auth::user();

                @endphp

                @if (empty($admin->type))
                    @include('admin.notifications.admin')
                @endif

                @if ($admin->type == 'superadmin')

                   @include('SuperAdmin::notifications.super')

                @endif

            </ul>
                {!! Admin::getNavbar()->render() !!}

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ Admin::user()->image }}" class="user-image" alt="User Image">
                        <span class="hidden-xs">{{ Admin::user()->name ?? Admin::user()->username }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <img src="{{ Admin::user()->image }}" class="img-circle" alt="User Image">
                            <p>
                                {{ Admin::user()->name }}
                                <small>Member since admin {{ Admin::user()->created_at }}</small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
                                @if (Admin::user()->type == 'bd')
                                    <a href="{{ bd_url('setting') }}" class="btn btn-default btn-flat">{{ trans('admin.setting') }}</a>
                                @else
                                    <a href="{{ admin_url('auth/setting') }}" class="btn btn-default btn-flat">{{ trans('admin.setting') }}</a>
                                @endif
                            </div>
                            <div class="pull-right">
                                @if (Admin::user()->type == 'bd')
                                    <a href="{{ bd_url('/logout') }}" class="btn btn-default btn-flat">{{ trans('admin.logout') }}</a>
                                @else
                                    <a href="{{ admin_url('auth/logout') }}" class="btn btn-default btn-flat">{{ trans('admin.logout') }}</a>
                                @endif
                            </div>
                        </li>
                    </ul>
                </li>

                <span id="preview-buttons-wrapper">
                    @if(!session('preview_superadmin') && session('filter_country_id') && !session('area_manager_id'))
                        @if (request()->is('admin*'))
                            <li style="padding: 10px;">
                                <button id="preview-superadmin-btn" class="btn btn-default preview-superadmin-btn">
                                    <i class="fa fa-eye"></i> {{ __('go to the country') }}
                                </button>
                            </li>
                        @endif
                    @elseif(!session('preview_area_manager')  && session('area_manager_id') )
                        @if (request()->is('admin*'))
                            <li style="padding: 10px;">
                                <button id="preview-area-manger-btn" class="btn btn-default preview-area-manger-btn">
                                    <i class="fa fa-eye"></i> {{ __('go to the preview') }}
                                </button>
                            </li>
                        @endif
                    @endif

                    @if(session('preview_superadmin') || session('preview_area_manager') )
                        @if (request()->is('admin*'))
                            <li style="padding: 10px;">
                                <button id="exit-preview-btn" class="btn btn-danger exit-preview-btn"">
                                <i class="fa fa-times"></i> {{ __('Back to the main dashboard') }}
                                </button>
                            </li>
                        @endif
                    @endif
                </span>
                <!-- Control Sidebar Toggle Button -->
                {{-- <li><a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a></li> --}}
            </ul>
        </div>
    </nav>
</header>

<script>

     $('.container-refresh').off('click').on('click', function() {
        location.reload();
        toastr.success('{{ __('admin.refresh_succeeded') }}', '', {positionClass:"toast-top-center"});
    });
    $(document).ready(function () {
        const $countrySelect = $('#country-select');
        const $AreaManagerSelect = $('#area-Manager-select');
        const isPreviewSuperadmin = @json(session('preview_superadmin'));
        const isPreviewAreaManager = @json(session('preview_area_manager'));

          $('#area-Manager-select').select2({
                placeholder: '{{ __("Select area manager") }}',
                allowClear: true,
                width: '190px'
            });

        $AreaManagerSelect.on('change', function () {
            const $this = $(this);
            if (!$this.val()) {
                setTimeout(() => $this.select2('close'), 0);

                const url = new URL(window.location.href);
                url.searchParams.set('clear_area_manager', 1);
                url.searchParams.delete('area_manager_id');

                if ($.pjax) {
                    setTimeout(() => {
                        $.pjax({ url: url.toString(), container: '#pjax-container' });
                    }, 1);
                } else {
                    window.location.href = url.toString();
                }
                return;
            }

            const areaManagerId = $(this).val();
            const url = new URL(window.location.href);

            if (areaManagerId && areaManagerId !== 'null') {
                url.searchParams.set('area_manager_id', areaManagerId);
                url.searchParams.delete('clear_area_manager');
            } else {
                url.searchParams.set('clear_area_manager', 1);
                url.searchParams.delete('area_manager_id');
            }

            if ($.pjax) {
                setTimeout(() => {
                    $.pjax({url: url.toString(), container: '#pjax-container'});
                }, 1);
            } else {
                window.location.href = url.toString();
            }
        });

        if ($countrySelect.length) {
            $countrySelect.select2({
                placeholder: "{{ __('Select Country') }}",
                allowClear: true,
                templateResult: formatCountry,
                templateSelection: formatCountry,
                escapeMarkup: function (markup) { return markup; }
            });

            $countrySelect.on('change', function () {
                const $this = $(this);
                if (!$this.val()) {
                    setTimeout(() => $this.select2('close'), 0);

                    const url = new URL(window.location.href);
                    url.searchParams.set('clear_country', 1);
                    url.searchParams.delete('filter_country_id');

                    if ($.pjax) {
                        setTimeout(() => {
                            $.pjax({ url: url.toString(), container: '#pjax-container' });
                        }, 1);
                    } else {
                        window.location.href = url.toString();
                    }
                    return;
                }

                const countryId = $(this).val();
                const url = new URL(window.location.href);

                if (countryId && countryId !== 'null') {
                    url.searchParams.set('filter_country_id', countryId);
                } else {
                    url.searchParams.set('filter_country_id', 'null');
                }

                if ($.pjax) {
                    setTimeout(() => {
                        $.pjax({url: url.toString(), container: '#pjax-container'});
                    }, 1);
                } else {
                    window.location.href = url.toString();
                }
            });

            @if(request()->is('areaManager*'))
                $countrySelect.on('change', function () {
                const $this = $(this);
                if (!$this.val()) {
                    setTimeout(() => $this.select2('close'), 0);

                    const url = new URL(window.location.href);
                    url.searchParams.set('clear_area_manager_country', 1);
                    url.searchParams.delete('area_manager_country_id');

                    if ($.pjax) {
                        setTimeout(() => {
                            $.pjax({ url: url.toString(), container: '#pjax-container' });
                        }, 1);
                    } else {
                        window.location.href = url.toString();
                    }
                    return;
                }

                    const countryId = $(this).val();
                    const url = new URL(window.location.href);

                    if (countryId && countryId !== 'null') {
                        url.searchParams.set('area_manager_country_id', countryId);
                        url.searchParams.delete('clear_area_manager_country');
                    } else {
                        url.searchParams.set('clear_area_manager_country', 1);
                        url.searchParams.delete('area_manager_country_id');
                    }

                    if ($.pjax) {
                        setTimeout(() => {
                            $.pjax({url: url.toString(), container: '#pjax-container'});
                        }, 1);
                    } else {
                        window.location.href = url.toString();
                    }
                });
            @endif
        }

        function formatCountry(country) {
            if (!country.id) {
                return country.text;
            }
            const flag = $(country.element).data('flag');
            const name = country.text;
            if (flag) {
                return `
                <span>
                    <img src="${flag}" style="width:20px; height:14px; margin-right:5px; vertical-align:middle;">
                    ${name}
                </span>
            `;
            }
            return name;
        }

        const originalFetch = window.fetch;
        window.fetch = function (url, options = {}) {
            options.headers = options.headers || {};

            if (window.enableCountryHeader) {
                const countryId = $('#country-select').val();
                options.headers['X-Country-ID'] = countryId ? countryId : 'null';
            }

            if (!options.headers['Accept'])
                options.headers['Accept'] = 'application/json';

            return originalFetch(url, options);
        };

        const csrf = '{{ csrf_token() }}';
        const previewBtn = document.getElementById('preview-superadmin-btn');
        const exitBtn = document.getElementById('exit-preview-btn');

        const previewBtnArea = document.getElementById('preview-area-manger-btn');


        if (previewBtn) {
            previewBtn.addEventListener('click', function () {
                fetch('/admin/set-preview-superadmin', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf }
                }).then(() => window.location.reload());
            });
        }

        // if (exitBtn) {
        //     exitBtn.addEventListener('click', function () {
        //         fetch('/admin/unset-preview-superadmin', {
        //             method: 'POST',
        //             headers: { 'X-CSRF-TOKEN': csrf }
        //         }).then(() => window.location.reload());
        //     });
        // }

        if (previewBtnArea) {
            previewBtnArea.addEventListener('click', function () {
                fetch('/admin/set-preview-area-manager', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf }
                }).then(() => window.location.reload());
            });
        }

    if (exitBtn) {
        if (isPreviewSuperadmin) {
            exitBtn.addEventListener('click', function () {
                fetch('/admin/unset-preview-superadmin', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf }
                }).then(() => window.location.reload());
            });
        } else if (isPreviewAreaManager) {
            exitBtn.addEventListener('click', function () {
                fetch('/admin/unset-preview-area-manager', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf }
                }).then(() => window.location.reload());
            });
        }
    }


    });
</script>

<style>
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 25px;
    }
    .rtl .select2-container--default .select2-selection--single .select2-selection__clear{
        left: 5px !important;
    }
    .select2-container .select2-selection--single .select2-selection__rendered img {
        margin-right: 5px;
        vertical-align: middle;
    }
</style>





<script>
    window.handleNotificationClick = function(id, url) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        if (!id) return;

            fetch(`/admin/notifications/mark-as-read/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
            })
            .then(res => res.json())
            .then(data => {
                const el = document.querySelector(`.notification-item[data-id='${id}']`);
                if (el) {
                    el.classList.remove('unread');
                    el.classList.add('read');
                }
                if (url) {
                    window.location.href = url;
                }
            })
            .catch(err => console.error('Error marking notification:', err));
    };



    window.superAdminhandleNotificationClick = function(id, url) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        if (!id) return;

            fetch(`/superadmin/notifications/mark-as-read/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
            })
            .then(res => res.json())
            .then(data => {
                const el = document.querySelector(`.notification-item[data-id='${id}']`);
                if (el) {
                    el.classList.remove('unread');
                    el.classList.add('read');
                }
                if (url) {
                    window.location.href = url;
                }
            })
            .catch(err => console.error('Error marking notification:', err));
    };

    window.initializeAdminHeader = function () {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const previewBtn     = document.getElementById('preview-superadmin-btn');
        const previewAreaBtn = document.getElementById('preview-area-manger-btn');
        const exitBtn        = document.getElementById('exit-preview-btn');

        // handle "go to country" button
        if (previewBtn) {
            previewBtn.addEventListener('click', function () {
                fetch('/admin/set-preview-superadmin', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                }).then(() => window.location.reload());
            });
        }

        // handle "go to preview" button
        if (previewAreaBtn) {
            previewAreaBtn.addEventListener('click', function () {
                fetch('/admin/set-preview-area-manager', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                }).then(() => window.location.reload());
            });
        }

        // handle "exit preview" button
        if (exitBtn) {
            exitBtn.addEventListener('click', function () {
                Promise.all([
                    fetch('/admin/unset-preview-superadmin', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                    }),
                    fetch('/admin/unset-preview-area-manager', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                    })
                ]).then(() => window.location.reload());
            });
        }
    };

    $(document).on('pjax:end', function() {
        $.get(window.location.href, function (response) {
            $('#preview-buttons-wrapper').html($(response).find('#preview-buttons-wrapper').html());
            $('#country-select').html($(response).find('#country-select').html());
            window.initializeAdminHeader();
            $('#country-select').select2({});
        });
    });
</script>

