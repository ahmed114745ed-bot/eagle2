@php
    $userService = app(\App\Admin\Services\UserSuperAdminService::class);
@endphp

<div class="box box-success shadow-sm border-0">
    <div class="box-header with-border text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="fa fa-users me-2"></i> {{ __('Top Followers') }}</h4>
    </div>

    <div class="box-body p-0">
{{--        <div id="top-followers-loading" class="text-center py-3">--}}
{{--            <i class="fa fa-spinner fa-spin fa-2x"></i>--}}
{{--            <p>{{ __('Loading...') }}</p>--}}
{{--        </div>--}}

        <div class="table-responsive d-none" id="top-followers-table-wrapper">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 60px;">#</th>
                    <th class="text-center">{{ __('User') }}</th>
                    <th class="text-center th">{{ __('Followers Count') }}</th>
                </tr>
                </thead>
                <tbody id="top-followers-body"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        fetch('admin/statistics/top-followers')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('top-followers-body');
                const tableWrapper = document.getElementById('top-followers-table-wrapper');
                const loading = document.getElementById('top-followers-loading');
                tbody.innerHTML = '';

                data.forEach((user, index) => {
                    tbody.innerHTML += `
                        <tr>
                            <td class="text-center fw-bold">${index + 1}</td>
                            <td class="avatar-cell">
                                <img src="${user.profile?.avatar ?? '/default-avatar.png'}" alt="avatar">
                                <span>${user.name}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success fs-6">${Number(user.followers_count).toLocaleString()}</span>
                            </td>
                        </tr>
                    `;
                });

                loading.classList.add('d-none');
                tableWrapper.classList.remove('d-none');
            })
            .catch(() => {
                document.getElementById('top-followers-loading').innerHTML = `<p class="text-danger">{{ __('Failed to load data') }}</p>`;
            });
    });
</script>
