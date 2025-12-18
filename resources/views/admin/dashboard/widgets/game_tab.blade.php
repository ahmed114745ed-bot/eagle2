@if (\Encore\Admin\Facades\Admin::user()->can('pay-switch' . 'dashboard') || \Encore\Admin\Facades\Admin::user()->can('*'))
    <div class="filter-form-premium fade-in-up">
        <form method="GET" action="{{ url()->current() }}" style="
    padding: 4px 17px;
    display: flex;
    border-radius: 15px;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
        ">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-calendar-alt" style="color: var(--accent-primary);"></i>
                <input type="month" name="date" id="date-filter" value="{{ request('date') }}">
            </div>
            <button type="submit" class="btn-premium">
                <i class="fas fa-filter"></i> {{ __('admin.filter') }}
            </button>
        </form>
    </div>
    @endif

    @if (\Encore\Admin\Facades\Admin::user()->can('pay-switch' . 'dashboard') || \Encore\Admin\Facades\Admin::user()->can('*'))
    <div class="card balance-card fade-in-up" style="--stagger: 1">
        <div class="card-header">
            <i class="fas fa-wallet"></i> {{ __('admin.balance') }} & {{ __('admin.game_recharge_rate') }}
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Left content with table -->
                <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <table class="table balance-table-premium" id="balance-table">
                        <tbody>
                            <tr>
                                <th><i class="fas fa-coins"></i> {{ __('admin.balance') }}</th>
                                <td id="all-balance">0</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-check-circle"></i> {{ __('admin.availableBalance') }}</th>
                                <td id="available-balance">0</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-dollar-sign"></i> {{ __('admin.balance') }} $</th>
                                <td id="balance-dollar">0</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-minus-circle"></i> {{ __('admin.used') }}</th>
                                <td id="used-balance">0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Right content with chart -->
                <div class="col-md-6 chart-container">
                    <canvas id="myChart"></canvas>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-center">
                    <div id="payment-alert-container"></div>
                    <button type="button" class="btn-premium mt-3" onclick="window.location.href='admin/payment-with-method';">
                        <i class="fas fa-credit-card"></i> {{ __('admin.pay') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
<div class="row g-3">
    <div class="col-md-3 col-sm-6">
        <div class="cards-container mb-4" data-aos="fade-up">
            <div class="card finance-card" id="app_profit">
                <div class="card-icon"><i class="fa-solid fa-gamepad"></i></div>
                <h3>{{ __('App Profit') }}</h3>
                <p class="amount" id="appProfit">0 $</p>
                <small>{{ __('Total earnings from app') }}</small>
            </div>
        </div>
    </div>
</div>

@php
    if (request()->is('superadmin*')) {
        $prefix = 'superadmin';
    } elseif (request()->is('areaManager*')) {
        $prefix = 'areaManager';
    } else {
        $prefix = 'admin';
    }
@endphp

<script>
    $(function() {
        $.ajax({
            url: '{{ url($prefix . "/statistics/game-summary") }}',
            type: 'GET',
            success: function(data) {
                $('#appProfit').text(data.app_profit);
            },
            error: function() {
                $('#appProfit').text('Error');
            }
        });
    });
</script>
