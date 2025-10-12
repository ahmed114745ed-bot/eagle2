<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

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
            $countries = \App\Models\Country::select(['id', 'name'])->get();
            $selectedCountryId = session('country_id') ?? request('country_id') ?? Admin::user()->country_id;
            $selectedCountry   = $countries->firstWhere('id', (int) $selectedCountryId);
        @endphp

        @if(!session('preview_superadmin'))
            @if (request()->is('admin*'))
                <a class="nav-item select-country">
                    <select id="country-select" class="form-control" style="width:190px;">
                        <option value="">{{ __('Select Country...') }}</option>
                        @foreach($countries as $currentCountry)
                            <option value="{{ $currentCountry->id }}"
                                {{ (string)$selectedCountryId === (string)$currentCountry->id ? 'selected' : '' }}>
                                {{ $currentCountry->name }}
                            </option>
                        @endforeach
                    </select>
                </a>
            @endif
            <script> window.enableCountryHeader = true; </script>
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
                                <i class="fa fa-eye"></i> {{ __('Preview Super Admin') }}
                            </button>
                        </li>
                    @endif
                @endif

                @if(session('preview_superadmin'))
                    @if (request()->is('admin*'))
                        <li style="padding: 10px;">
                            <button id="exit-preview-btn" class="btn btn-danger exit-preview-btn"">
                            <i class="fa fa-times"></i> {{ __('Exit Preview') }}
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
    $(function () {
        const $countrySelect = $('#country-select');
        const $countryName   = $('#country-name');

        if ($countrySelect.length) {
            $countrySelect.select2({
                placeholder: "{{ __('Select Country') }}",
                allowClear : true
            });

            $countrySelect.on('change select2:clear', function () {
                const countryId = $(this).val();
                const url = new URL(window.location.href);

                if (countryId && countryId !== 'null') {
                    url.searchParams.set('country_id', countryId);
                } else {
                    url.searchParams.set('country_id', 'null');
                }

                window.location.href = url.toString();
            });
        }

        const originalFetch = window.fetch;
        window.fetch = function (url, options = {}) {
            options.headers = options.headers || {};

            if (window.enableCountryHeader) {
                const countryId = $countrySelect.val();
                if (countryId) {
                    options.headers['X-Country-ID'] = countryId;
                } else {
                    options.headers['X-Country-ID'] = '';
                }
            }

            if (!options.headers['Accept'])
                options.headers['Accept'] = 'application/json';

            return originalFetch(url, options);
        };
    });

    document.addEventListener("DOMContentLoaded", function () {
        const csrf = '{{ csrf_token() }}';
        const previewBtn = document.getElementById('preview-superadmin-btn');
        const exitBtn = document.getElementById('exit-preview-btn');

        if (previewBtn) {
            previewBtn.addEventListener('click', function () {
                fetch('/admin/set-preview-superadmin', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    }
                }).then(() => window.location.reload());
            });
        }

        if (exitBtn) {
            exitBtn.addEventListener('click', function () {
                fetch('/admin/unset-preview-superadmin', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    }
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
</style>
