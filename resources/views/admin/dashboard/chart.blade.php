{{-- Admin Dashboard --}}
{{-- eslint-disable --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
* {
    box-sizing: border-box;
}

body {
    background-color: #f8f9fa;
    color: #212529;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
}

.dashboard-container {
    background-color: #f8f9fa;
    min-height: 100vh;
    padding: 20px;
}

.section-header {
    font-size: 2.5rem;
    font-weight: 800;
    text-align: center;
    margin: 60px 0 40px 0;
    position: relative;
    background: var(--gradient-accent);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.02em;
}

.section-header::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: var(--gradient-accent);
    border-radius: 2px;
    box-shadow: var(--shadow-glow);
}

.card {
    background: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 16px 20px;
    font-weight: 600;
    font-size: 1.1rem;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-body {
    padding: 20px;
}

.balance-card-premium {
    background: var(--gradient-primary);
    color: white;
    position: relative;
    overflow: hidden;
}

.balance-card-premium::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: conic-gradient(from 0deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    animation: rotate 8s linear infinite;
}

@keyframes rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.balance-table-premium {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.balance-table-premium th {
    background: rgba(0, 212, 255, 0.15);
    color: var(--accent-primary);
    border: none;
    padding: 20px 24px;
    font-weight: 600;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.balance-table-premium td {
    background: rgba(255, 255, 255, 0.03);
    color: var(--text-primary);
    border: none;
    padding: 20px 24px;
    font-weight: 500;
    font-size: 1.1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.balance-table-premium tr:last-child td {
    border-bottom: none;
}

.chart-container {
    background: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
}

[dir="rtl"] .chart-container {
    direction: ltr;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    border-radius: 6px;
    padding: 10px 20px;
    color: white;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
    color: white;
    text-decoration: none;
}

.alert-premium {
    background: var(--gradient-danger);
    border: none;
    border-radius: 16px;
    color: white;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-danger);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 0, 128, 0.2);
}

.stats-masonry {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

/* Responsive for masonry grid */
@media (max-width: 768px) {
    .stats-masonry {
        grid-template-columns: 1fr;
        gap: 16px;
    }
}

@media (max-width: 480px) {
    .stats-masonry {
        gap: 12px;
    }
}

.widget-card-premium {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--border-light);
    border-radius: 20px;
    padding: 24px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-soft);
    position: relative;
    overflow: hidden;
}

.widget-card-premium::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--gradient-accent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.widget-card-premium:hover::before {
    opacity: 1;
}

.widget-card-premium:hover {
    background: var(--bg-glass-hover);
    border-color: var(--border-glow);
    box-shadow: var(--shadow-soft), var(--shadow-glow);
    transform: translateY(-6px) scale(1.02);
}

.filter-form-premium {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--border-light);
    border-radius: 16px;
    margin-bottom: 32px;
    box-shadow: var(--shadow-soft);
}

.filter-form-premium input,
.filter-form-premium button {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border-light);
    color: var(--text-primary);
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.filter-form-premium input:focus,
.filter-form-premium button:focus {
    outline: none;
    border-color: var(--accent-primary);
    box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
    background: rgba(255, 255, 255, 0.08);
}

.filter-form-premium input::placeholder {
    color: var(--text-muted);
}

.fade-in-up {
    animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    opacity: 0;
    transform: translateY(30px);
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stagger-animation {
    animation-delay: calc(var(--stagger) * 0.1s);
}

.pulse-glow {
    animation: pulseGlow 2s ease-in-out infinite alternate;
}

@keyframes pulseGlow {
    from {
        box-shadow: var(--shadow-soft);
    }
    to {
        box-shadow: var(--shadow-soft), var(--shadow-glow);
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 10px;
    }

    .section-header {
        font-size: 1.5rem;
        margin: 20px 0 15px 0;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .card-body {
        padding: 15px;
    }

    .card-header {
        padding: 12px 16px;
        font-size: 1rem;
    }

    .balance-card .row > div {
        margin-bottom: 15px;
    }
}

/* Tab Styles */
.tabs-container {
    margin-bottom: 30px;
}

.nav-tabs {
    background: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 4px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nav-tabs .nav-link {
    background: transparent;
    border: none;
    color: #6c757d;
    font-weight: 500;
    font-size: 0.95rem;
    padding: 12px 20px;
    margin: 0 2px;
    border-radius: 6px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.nav-tabs .nav-link:hover {
    background-color: #f8f9fa;
    color: #495057;
}

.nav-tabs .nav-link.active {
    background-color: #007bff;
    color: white;
    box-shadow: 0 2px 4px rgba(0,123,255,0.3);
}

.tab-content {
    background: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);

}

.tab-pane {
    animation: fadeIn 0.5s ease forwards;
    opacity: 0;
}
.nav-tabs {
    display: flex;
    justify-content: center;
    align-items: center;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.nav-tabs::-webkit-scrollbar {
    display: none;
}

.nav-tabs li {
    list-style: none;
    margin: 0 10px;
    flex-shrink: 0;
}

img {
    display: unset !important;
}

/* Responsive adjustments for tabs */
@media (max-width: 768px) {
    .nav-tabs {
        justify-content: flex-start;
        padding: 4px;
    }

    .nav-tabs li {
        margin: 2px 5px;
    }

    .nav-tabs .nav-link {
        font-size: 0.85rem;
        padding: 8px 12px;
        white-space: nowrap;
    }

    .nav-tabs .nav-link i {
        margin-right: 4px;
    }
}

@media (max-width: 480px) {
    .nav-tabs li {
        margin: 2px 2px;
    }

    .nav-tabs .nav-link {
        font-size: 0.8rem;
        padding: 6px 8px;
    }
}


.tab-pane.show {
    opacity: 1;
}

/* Loading states */
.loading-shimmer {
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
</style>

<div class="dashboard-container" @if(app()->getLocale() == 'ar') dir="rtl" @endif>
    <div class="tabs-container fade-in-up" style="--stagger: 1">
        <ul class="nav nav-tabs nav-tabs-glass" id="statsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                    <i class="fas fa-chart-line"></i> {{ __('Overview') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="false">
                    <i class="fas fa-users"></i> {{ __('Users') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rooms-tab" data-bs-toggle="tab" data-bs-target="#rooms" type="button" role="tab" aria-controls="rooms" aria-selected="false">
                    <i class="fas fa-home"></i> {{ __('Rooms') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="agencies-tab" data-bs-toggle="tab" data-bs-target="#agencies" type="button" role="tab" aria-controls="agencies" aria-selected="false">
                    <i class="fas fa-building"></i> {{ __('Agencies') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bd-tab" data-bs-toggle="tab" data-bs-target="#bd" type="button" role="tab" aria-controls="bd" aria-selected="false">
                    <i class="fas fa-briefcase"></i> {{ __('BD') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="game-tab" data-bs-toggle="tab" data-bs-target="#game" type="button" role="tab" aria-controls="game" aria-selected="false">
                    <i class="fas fa-gamepad"></i> {{ __('Game') }}
                </button>
            </li>
        </ul>

        <div class="tab-content" id="statsTabContent">
            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">

                <div class="col-md-12">@include('admin.dashboard.widgets.overview_tab')</div>

            </div>

            <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
                <div class="col-md-12 mb-4">@include('admin.dashboard.stats')</div>
                <div class="stats-grid">
                    <div class="widget-card">@include('admin.dashboard.widgets.users_chart')</div>
                    <div class="widget-card">@include('admin.dashboard.widgets.top_users_visits_chart')</div>
                    <div class="widget-card">@include('admin.dashboard.widgets.signups_weekly_chart')</div>
                    <div class="widget-card">@include('admin.dashboard.widgets.peak_hours_card')</div>
                    <div class="widget-card">@include('admin.dashboard.widgets.top_followers_table')</div>
                    <div class="widget-card">@include('admin.dashboard.widgets.users_online_chart')</div>
                </div>
            </div>

            <div class="tab-pane fade" id="rooms" role="tabpanel" aria-labelledby="rooms-tab">
                <div class="col-md-12 mb-4">@include('admin.dashboard.widgets.room_tab')</div>
                <div class="stats-grid">
                    <div class="widget-card">@include('admin.dashboard.widgets.rooms_distribution_chart')</div>
                    <div class="widget-card">@include('admin.dashboard.widgets.rooms_activity_chart')</div>
                    <div class="widget-card">@include('admin.dashboard.widgets.top_gifted_rooms_chart')</div>
                    <div class="widget-card">@include('admin.dashboard.widgets.avg_session_duration_chart')</div>
                </div>
            </div>

            <div class="tab-pane fade" id="agencies" role="tabpanel" aria-labelledby="agencies-tab">
                <div class="col-md-12 mb-4">@include('admin.dashboard.widgets.agency_tab')</div>
                <div class="stats-masonry">
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.agencies_targets_chart')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.top_senders_chart')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.top_receivers_chart')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.agencies_compare_chart')</div>
                </div>
            </div>

            <div class="tab-pane fade" id="bd" role="tabpanel" aria-labelledby="bd-tab">
                <div class="col-md-12">@include('admin.dashboard.widgets.bd_tab')</div>
            </div>

            <div class="tab-pane fade" id="game" role="tabpanel" aria-labelledby="game-tab">
                <div class="col-md-12">@include('admin.dashboard.widgets.game_tab')</div>
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

    $chartLabels = [__('admin.used'), __('admin.availableBalance')];
    $chartLabel = __('admin.data_distribution');
    $chartTitle = __('admin.game_recharge_rate');
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
var chartLabels = {!! json_encode($chartLabels) !!};
var chartLabel = {!! json_encode($chartLabel) !!};
var chartTitle = {!! json_encode($chartTitle) !!};
</script>

<script>
// المتغير العام للـ Chartf
var myChart = null;

// دالة لإنشاء أو تحديث الـ Chart
function createOrUpdateChart(chartData, usePercentage) {
    var ctx = document.getElementById('myChart').getContext('2d');

    // إذا كان الـ Chart موجود بالفعل، قم بتدميره أولاً
    if (myChart !== null) {
        myChart.destroy();
    }

    myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartLabels,
            datasets: [{
                label: chartLabel,
                data: chartData,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: chartTitle + ' - ' + usePercentage.toFixed(2) + '%'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function loadBalanceData(date = null) {
    $.ajax({
        url: '{{ url($prefix . "/statistics/balance-data") }}',
        type: 'GET',
        data: {
            date: date
        },
        beforeSend: function() {
            // إظهار مؤشر تحميل إذا أردت
            $('#balance-table').css('opacity', '0.7');
        },
        success: function(response) {
            if (response.success) {
                // تحديث البيانات في الجدول
                $('#all-balance').text(response.data.allBalance.toLocaleString());
                $('#available-balance').text(response.data.availableBalance.toLocaleString());
                $('#balance-dollar').text(response.data.balanceDollar.toLocaleString());
                $('#used-balance').text(response.data.used.toLocaleString());

                // تحديث الـ Chart
                createOrUpdateChart(response.data.chartData, response.data.usePercentage);

                // معالجة التنبيه
                handlePaymentAlert(response.data.showPaymentAlert, response.data.usePercentage);
            }
        },
        error: function(xhr, status, error) {
            console.log('Error loading balance data:', error);
            // إظهار رسالة خطأ للمستخدم
            alert('حدث خطأ في تحميل البيانات. يرجى المحاولة مرة أخرى.');
        },
        complete: function() {
            // إعادة opacity إلى الطبيعي
            $('#balance-table').css('opacity', '1');
        }
    });
}

// دالة للتعامل مع تنبيه الدفع
function handlePaymentAlert(showAlert, usePercentage) {
    const alertContainer = $('#payment-alert-container');

    alertContainer.empty();

    if (showAlert) {
        const alertHtml = `
            <div class="row">
                <div class="col-md-12 text-center">
                    <h4 class="text-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        {{ __('admin.you_must_pay') }}
                    </h4>
                    <small class="text-muted">(${usePercentage.toFixed(2)}% مستخدم)</small>
                </div>
            </div>
        `;
        alertContainer.html(alertHtml);

        // إضافة تأثير لو أردت
        alertContainer.hide().fadeIn(500);
    }
}

// تحميل البيانات عند فتح الصفحة
$(document).ready(function() {
    // إنشاء الـ Chart الأولي بالبيانات الافتراضية
    createOrUpdateChart([0, 0], 0);

    // تحميل البيانات الفعلية
    loadBalanceData();

    // إضافة event listener لحقل التاريخ
    $('#date-filter').on('change', function() {
        loadBalanceData($(this).val());
    });

    // منع إعادة تحميل الصفحة عند الضغط على الفلتر
    $('form').on('submit', function(e) {
        e.preventDefault();
        loadBalanceData($('#date-filter').val());
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const charts = document.querySelectorAll('canvas');

        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const url = entry.target.dataset.url;
                    if (url) fetch(url)
                        .then(r => r.json())
                        .then(data => {
                            const ctx = entry.target.getContext('2d');
                            new Chart(ctx, { type: 'bar', data: { labels: data.labels, datasets: [{ data: data.data }] } });
                        });
                    obs.unobserve(entry.target);
                }
            });
        });
        charts.forEach(c => io.observe(c));
    });
</script>
