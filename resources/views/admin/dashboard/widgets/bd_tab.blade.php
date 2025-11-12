<div class="row g-3">

    <!-- BD Count -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-briefcase"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Bd Count') }}</span>
                <span class="info-box-number" data-bdstat="bdCount"></span>
                <a href="{{ admin_url('usersBD') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Total BD Salary -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-wallet"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Total BD Salary') }}</span>
                <span class="info-box-number" data-bdstat="totalBDSalary"></span>
                <a href="{{ admin_url('bd-salaries') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Total Cut Amount -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-red">
            <span class="info-box-icon"><i class="fa fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Total Cut Amount') }}</span>
                <span class="info-box-number" data-bdstat="totalBDCut"></span>
                <a href="{{ admin_url('bd-salaries') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Average Agencies per BD -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-olive">
            <span class="info-box-icon"><i class="fa fa-briefcase"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Average Agencies Per BD') }}</span>
                <span class="info-box-number" data-bdstat="averageAgenciesPerBD"></span>
                <a href="{{ admin_url('usersBD') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

</div>

@php
    $prefix = request()->is('superadmin*') ? 'superadmin' : 'admin';
@endphp

<script>
$(function() {
    function updateBdStats() {
        $.ajax({
            url: '{{ url($prefix . "/statistics/bd-stats") }}',
            type: 'GET',
            beforeSend: function() {
                $('#refreshBdStats').html('<i class="fa fa-spinner fa-spin"></i> {{ __("Loading...") }}');
            },
            success: function(data) {
                $('[data-bdstat="bdCount"]').text(data.bdCount);
                $('[data-bdstat="totalBDSalary"]').text(data.totalBDSalary);
                $('[data-bdstat="totalBDCut"]').text(data.totalBDCut);
                $('[data-bdstat="averageAgenciesPerBD"]').text(data.averageAgenciesPerBD);

                $('#refreshBdStats').html('<i class="fa fa-refresh me-1"></i> {{ __("Refresh Stats") }}');
            },
            error: function() {
                alert('{{ __("Error loading BD stats") }}');
                $('#refreshBdStats').html('<i class="fa fa-refresh me-1"></i> {{ __("Refresh Stats") }}');
            }
        });
    }

    updateBdStats();

    $('#refreshBdStats').on('click', function() {
        updateBdStats();
    });
});
</script>
