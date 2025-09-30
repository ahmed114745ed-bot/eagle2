@php
    $userService = app(\App\Admin\Services\UserSuperAdminService::class);
@endphp
<style>
    .shadow-sm {
        width: 95%;
        margin: auto;
    }
    .text-center{
        text-align: center !important;
    }
</style>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fa fa-users me-2"></i> {{ __('Top Followers') }}</h4>
        </div>
        <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="bi bi-people-fill me-2"></i>
            <h6 class="mb-0">{{ __('أعلى المتابعين') }}</h6>
        </div>

        <div class="card-body p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 60px;">#</th>
                        <th>{{ __('المستخدم') }}</th>
                        <th class="text-center">{{ __('عدد المتابعين') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($top5 as $index => $user)
                        <tr>
                            <td class="text-center fw-bold">{{ $index + 1 }}</td>
                            <td class="d-flex align-items-center">
                                <div class="me-3">
                                    {!! $userService->adminUserAvatar((object)[
                                        'id'     => $user->id,
                                        'uuid'   => $user->uuid ,
                                        'name'   => $user->name ,
                                        'avatar' => $user->profile?->avatar ,
                                    ], withoutLevels: true) !!}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    <small class="text-muted">UID: {{ $user->uuid }}</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success rounded-pill px-3 py-2 fs-6">
                                    {{ number_format($user->followers_count) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
