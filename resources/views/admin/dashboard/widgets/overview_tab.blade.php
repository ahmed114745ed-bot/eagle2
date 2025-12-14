
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

<div class="position-relative">
    <div class="floating-dots"></div>

    <div class="dashboard-wrap page-padding">

        <div class="card p-3 mb-4" data-aos="fade-down">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="small text-muted">{{    __('From')}}</label>
                    <input type="date" id="from_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted">   {{  __('To')}} </label>
                    <input type="date" id="to_date" class="form-control">
                </div>
             
                <div class="col-md-3">
                    <button id="applyFilter" class="btn btn-primary w-100">
                     {{ __('Filter') }}
                    </button>
                </div>
            </div>
        </div>

            
        <div class="cards-container mb-4" data-aos="fade-up">
            @foreach ([
                'total_balance'     => 'fa-wallet',
                'pending_balance'   => 'fa-clock',
                'available_balance' => 'fa-money-bill-wave',
                'today_balance'     => 'fa-coins'
            ] as $id => $icon)
                <div class="card finance-card" id="{{ $id }}">
                    <div class="card-icon"><i class="fa-solid {{ $icon }}"></i></div>
                    <h2 style="margin-top:0 !important;">{{ __("{$id}") }}</h2>
                    <p class="amount">0 $</p>
                    <h3>{{ __("{$id}") }}</h3>
                </div>
            @endforeach
        </div>

        <div class="row g-3">

            <div class="col-lg-7">

                <div class="col-lg-12">
                    <div class="main-chart-container" data-aos="fade-up">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="chart-title">{{ __("shipments_revenue") }}</h5>
                            <select id="filterChart" class="form-select form-select-sm" style="width:auto;">
                                <option value="7" selected>{{ __("filter_week") }}</option>
                                <option value="30">{{ __("filter_month") }}</option>
                                <option value="365">{{ __("filter_year") }}</option>
                            </select>
                        </div>
                            <div class="chart-container" style="margin-top: -22px">
                                <canvas id="shipmentsChart"></canvas>
                            </div>
                    </div>
                </div>

                <div class="col-lg-12 mt-4">
                    <div class="card card-with p-3" data-aos="fade-up">
                        <h5 class="mb-3">{{ __("latest_withdrawals") }}</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <thead class="text-muted small">
                                    <tr>
                                        <th>{{ __("table_header_index") }}</th>
                                        <th>{{ __("table_header_account") }}</th>
                                        <th>{{ __("table_header_amount") }}</th>
                                        <th>{{ __("table_header_status") }}</th>
                                        <th>{{ __("table_header_date") }}</th>
                                    </tr>
                                </thead>
                                <tbody id="withdrawalsTable"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-5">
                <div class="card card-trans p-3" data-aos="fade-up">
                    <h5 class="mb-3">{{ __("latest_payments") }}</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle">
                            <thead class="text-muted small">
                                <tr>
                                    <th>{{ __("table_header_index") }}</th>
                                    <th>{{ __("table_header_gateway") }}</th>
                                    <th>{{ __("table_header_amount") }}</th>
                                    <th>{{ __("table_header_status") }}</th>
                                    <th>{{ __("table_header_date") }}</th>
                                </tr>
                            </thead>
                            <tbody id="paymentsTable"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>


    </div>
</div>
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<style>
.floating-dots { position:absolute; inset:0; z-index:0; background: radial-gradient(circle, rgba(0,0,0,0.06) 2px, transparent 3px); background-size:40px 40px; animation: moveDots 40s linear infinite; opacity:0.6; pointer-events:none; }
@keyframes moveDots { from { background-position:0 0;} to { background-position:400px 400px; } }

.dashboard-wrap{ position:relative; z-index:1; }
.page-padding{ padding:1.25rem; }

.cards-container { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:20px; margin-bottom:40px; }
.card { background:#fff; border-radius:12px; padding:10px 12px; text-align:end; box-shadow:0 4px 12px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s; overflow:hidden; }
.card:hover { transform: translateY(-5px); box-shadow:0 8px 20px rgba(0,0,0,0.15); }
.card h2 { font-size:1.3rem; color:#333; }
.card h3 { font-size:1rem; margin-bottom:10px; color:#333; }
.card .amount { font-size:1.5rem; font-weight:bold; margin-bottom:15px; color:#1e3a8a; transition: all 0.6s ease; }
.card canvas { width:100% !important; height:120px !important; }
.card .card-icon{ position: absolute; top:7px}
.main-chart-container { background:#fff; border-radius:12px; padding:20px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.main-chart-container canvas {
    /* height: 200px !important;  
    width: 100% !important;    */
}
.main-chart-container {
    position: relative;
    width: 100%;
    height: 260px; 
}
.main-chart-container{
    background:#fff;
    border-radius:12px;
    padding:20px;
    box-shadow:0 4px 12px rgba(0,0,0,.1);
    height:320px; 
}

#shipmentsChart{
    width:100% !important;
    height:81% !important;
}
.card-trans{min-height: 370px;}
.card-with{margin-top:20px;}

@media (max-width:600px){ .cards-container{ grid-template-columns:1fr; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<script>
AOS.init({ once:true, duration:600 });

let financeChart = null;
let shipmentsChart = null;

function animateAmount(el, value) {
    let start = 0;
    const duration = 900;
    const startTime = performance.now();

    function step(now){
        const p = Math.min((now-startTime)/duration,1);
        el.innerText = Math.floor(start + p * value).toLocaleString() + ' $';
        if(p<1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

async function loadFinance() {
    try {
        const params = new URLSearchParams({
            from: document.getElementById('from_date')?.value || '',
            to: document.getElementById('to_date')?.value || '',
            days: document.getElementById('filterChart')?.value || 7
        });

        const res = await fetch(`/admin/dashboard/finance?${params}`);
        if(!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();

        console.log('Finance API Response:', data); 

        Object.keys(data.cards).forEach(key=>{
            const card = document.getElementById(key);
            if(card){
                animateAmount(card.querySelector('.amount'), data.cards[key]);
            }
        });



const ctx = document.getElementById('shipmentsChart').getContext('2d');
if (shipmentsChart) shipmentsChart.destroy();

shipmentsChart = new Chart(ctx, {
    type: 'bar', 
    data: {
        labels: data.chart.labels,
        datasets: [{
            label: 'إيرادات الشحنات',
            data: data.chart.values,
            backgroundColor: '#1d4ed8',
            borderRadius: 8,
            maxBarThickness: 50, 
            minBarLength: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false, 
        animation: { duration: 1200, easing: 'easeOutQuart' },
        plugins: {
            legend: { display: true, position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: ctx => `${ctx.dataset.label}: ${ctx.raw.toLocaleString()} $`
                }
            }
        },
        scales: {
            x: { 
                grid: { display: false },
                ticks: { autoSkip: false }
            },
            y: {
                beginAtZero: true,
                suggestedMin: 0, 
                ticks: { callback: v => v.toLocaleString() }
            }
        }
    }
});


console.log('labels:', data.chart.labels);
console.log('values:', data.chart.values);
console.log('canvas size:', ctx.canvas.width, ctx.canvas.height);
        const paymentsTbody = document.getElementById('paymentsTable');
        paymentsTbody.innerHTML = '';
        data.payments.forEach(p=>{
            paymentsTbody.innerHTML += `
                <tr>
                    <td>#${p.id}</td>
                    <td>${p.gateway}</td>
                    <td>${p.amount.toLocaleString()} $</td>
                    <td><span class="badge bg-${p.status_color}">${p.status}</span></td>
                    <td class="text-muted small">${p.date}</td>
                </tr>
            `;
        });

        const withdrawalsTbody = document.getElementById('withdrawalsTable');
        withdrawalsTbody.innerHTML = '';
        data.withdrawals.forEach(w=>{
            withdrawalsTbody.innerHTML += `
                <tr>
                    <td>#${w.id}</td>
                    <td>${w.wallet_name}</td>
                    <td>${w.amount.toLocaleString()} $</td>
                    <td>${w.type}</td>
                    <td class="text-muted small">${w.date}</td>
                </tr>
            `;
        });

    } catch (err) {
        console.error('Error loading finance data:', err);
    }
}

document.getElementById('applyFilter')?.addEventListener('click', loadFinance);
document.getElementById('filterChart')?.addEventListener('change', loadFinance);
document.addEventListener('DOMContentLoaded', loadFinance);
</script>
