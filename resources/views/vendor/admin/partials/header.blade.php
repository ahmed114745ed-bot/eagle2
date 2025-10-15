<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-messaging-compat.js"></script>
<script type="module" src="{{ asset('js/firebase-notification.js') }}"></script>
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
            $country   = \App\Models\Country::find(Admin::user()->country_id);
            $countries = \App\Models\Country::select(['id', 'name', 'flag'])->get();
            $selectedCountryId = session('country_id') ?? request('country_id') ?? Admin::user()->country_id;
            $selectedCountry   = $countries->firstWhere('id', (int) $selectedCountryId);
        @endphp

        @if(!session('preview_superadmin'))
            @if (request()->is('admin*'))
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
            <script>window.enableCountryHeader = true;</script>
        @endif

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
                <li class="nav-item dropdown" id="notificationsDropdown">
                    <a href="#" class="nav-link" onclick="openModal(event)" style="position: relative;">
                        <i class="fa fa-bell" style="font-size: 20px;"></i>
                        <span id="notificationsCount"
                            style="position:absolute; top:5px; right:5px; background:red; color:white; border-radius:50%; padding:2px 6px; font-size:11px; display:none;">
                        </span>
                    </a>
                </li>

                <!-- المودال -->
                <div class="modal-overlay" id="myModal">
                    <div class="modal-no">
                        <div class="modal-header">
                            <h5>{{ __('Notifications') }}</h5>
                            <button type="button" class="close-btn" id="closeModalBtn">×</button>
                        </div>
                        <div class="modal-body2" id="notificationsContent">
                            <div class="text-center text-muted p-3">{{ __('dashboard.login.loading.prepare') }}</div>
                        </div>
                        {{-- <div class="modal-footer" style="text-align: center; padding: 10px;">
                            <button type="button" class="btn btn-sm btn-primary" id="markAllReadBtn">{{ __('Mark all as read') }}</button>
                        </div>
                        <div class="modal-footer" style="text-align: center; padding: 10px;">
                            <button type="button" class="see-more btn btn-sm btn-outline-secondary load-more-btn" id="loadMoreBtn">
                            <a href="{{ route('admin.notifications.grid') }}" 
                                    class="see-more btn btn-sm btn-outline-secondary load-more-btn" 
                                    id="loadMoreBtn">
                                        {{ __('Show more') }}
                                    </a>
                            </button>
                        </div> --}}

                         <div class="modal-footer">
                            <button type="button" class="btn btn-footer btn-mark-all" id="markAllReadBtn">
                                {{ __('Mark all as read') }}
                            </button>
                            <a href="{{ route('admin.notifications.grid') }}" class="btn btn-footer btn-show-more" id="loadMoreBtn">
                                 {{ __('Show more') }}
                            </a>
                        </div>
                    </div>
                </div>
                <audio id="notificationSound" src="{{ asset('sounds/notification.mp3') }}" preload="auto" style="display:none;"></audio>
    
                <script>
                    window.NOTIFICATIONS_API = {
                        countUrl: "{{ admin_url('notifications/count') }}",
                        listUrl: "{{ admin_url('notifications/list') }}",
                        markReadUrl: "{{ admin_url('notifications/mark-all-read') }}"
                    };
                </script>

                <link rel="stylesheet" href="{{ asset('css/modal.css') }}">
                <script src="{{ asset('js/modal.js') }}"></script>
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

                @if(!session('preview_superadmin') && session('country_id'))
                    @if (request()->is('admin*'))
                        <li style="padding: 10px;">
                            <button id="preview-superadmin-btn" class="btn btn-default preview-superadmin-btn">
                                <i class="fa fa-eye"></i> {{ __('go to the country') }}
                            </button>
                        </li>
                    @endif
                @endif

                @if(session('preview_superadmin'))
                    @if (request()->is('admin*'))
                        <li style="padding: 10px;">
                            <button id="exit-preview-btn" class="btn btn-danger exit-preview-btn"">
                            <i class="fa fa-times"></i> {{ __('Back to the main dashboard') }}
                            </button>
                        </li>
                    @endif
                @endif

                <!-- Control Sidebar Toggle Button -->
                {{-- <li><a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a></li> --}}
            </ul>
        </div>
    </nav>
</header>

<script>
    $(document).ready(function () {
        const $countrySelect = $('#country-select');

        if ($countrySelect.length) {
            $countrySelect.select2({
                placeholder: "{{ __('Select Country') }}",
                allowClear: true,
                templateResult: formatCountry,
                templateSelection: formatCountry,
                escapeMarkup: function (markup) { return markup; }
            });

            $countrySelect.on('change', function () {
                const countryId = $(this).val();
                const url = new URL(window.location.href);

                if (countryId && countryId !== 'null') {
                    url.searchParams.set('country_id', countryId);
                } else {
                    url.searchParams.set('country_id', 'null');
                }

                window.location.href = url.toString();
            });

            $(document).on('click', '.select2-selection__clear', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const url = new URL(window.location.href);
                url.searchParams.set('country_id', 'null');
                window.location.href = url.toString();
            });
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

        if (previewBtn) {
            previewBtn.addEventListener('click', function () {
                fetch('/admin/set-preview-superadmin', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf }
                }).then(() => window.location.reload());
            });
        }

        if (exitBtn) {
            exitBtn.addEventListener('click', function () {
                fetch('/admin/unset-preview-superadmin', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf }
                }).then(() => window.location.reload());
            });
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
    window.PUSHER_APP_KEY = "{{ App\Helpers\Common::getConf('pusher_app_key') }}";
    window.PUSHER_APP_CLUSTER = "{{ App\Helpers\Common::getConf('pusher_app_cluster') ?? 'mt1' }}";
</script>
