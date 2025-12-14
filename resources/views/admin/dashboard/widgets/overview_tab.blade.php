
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

<div class="position-relative">
    <div class="floating-dots"></div>

    <div class="dashboard-wrap page-padding">

        {{-- 🔎 FILTER --}}
        <div class="card p-3 mb-4" data-aos="fade-down">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="small text-muted">من تاريخ</label>
                    <input type="date" id="from_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted">إلى تاريخ</label>
                    <input type="date" id="to_date" class="form-control">
                </div>
             
                <div class="col-md-3">
                    <button id="applyFilter" class="btn btn-primary w-100">
                        تطبيق الفلتر
                    </button>
                </div>
            </div>
        </div>

        {{-- 💰 STAT CARDS --}}
        <div class="cards-container mb-4" data-aos="fade-up">
            @foreach ([
                'total_balance'     => ['إجمالي الرصيد','fa-wallet'],
                'pending_balance'   => ['قيد المعالجة','fa-clock'],
                'available_balance' => ['المتاح','fa-money-bill-wave'],
                'today_balance'     => ['اليوم','fa-coins'],
            ] as $id => [$label,$icon])
                <div class="card finance-card" id="{{ $id }}">
                    <div class="card-icon"><i class="fa-solid {{ $icon }}"></i></div>
                    <h2 style="margin-top: 0px !important;">{{ $label }}</h2>
                    <p class="amount">0 $</p>
                    <h3>{{ $label }}</h3>
                </div>
            @endforeach
        </div>

        {{-- 📊 CHART + TABLE --}}
        <div class="row g-3">

            <div class="col-lg-7">
              <div class="main-chart-container" data-aos="fade-up">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="chart-title">إيرادات الشحنات</h5>
                        <select id="filterChart" class="form-select form-select-sm" style="width:auto;">
                            <option value="7" selected>أسبوع</option>
                            <option value="30">شهر</option>
                            <option value="365">سنة</option>
                        </select>
                    </div>
                    <canvas id="shipmentsChart"></canvas>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card p-3" data-aos="fade-up">
                    <h5 class="mb-3">آخر عمليات الدفع</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle">
                            <thead class="text-muted small">
                                <tr>
                                    <th>#</th>
                                    <th>البوابة</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
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
.card h2 { font-size:2rem; color:#333; }
.card h3 { font-size:1rem; margin-bottom:10px; color:#333; }
.card .amount { font-size:1.5rem; font-weight:bold; margin-bottom:15px; color:#1e3a8a; transition: all 0.6s ease; }
.card canvas { width:100% !important; height:120px !important; }
.card .card-icon{ position: absolute; top:7px}
.main-chart-container { background:#fff; border-radius:12px; padding:20px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.main-chart-container canvas {
    height: 200px !important;  
    width: 100% !important;   
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
    height:100% !important;
}

@media (max-width:600px){ .cards-container{ grid-template-columns:1fr; } }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<script>
AOS.init({ once:true, duration:600 });

let financeChart = null;

function animateAmount(el, value) {
    let start = 0;
    const duration = 900;
    const startTime = performance.now();

    function step(now){
        const p = Math.min((now-startTime)/duration,1);
        el.innerText = Math.floor(start + p * value).toLocaleString() + ' EGP';
        if(p<1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

async function loadFinance() {

    const params = new URLSearchParams({
        from: document.getElementById('from_date').value,
        to: document.getElementById('to_date').value,
        gateway: document.getElementById('gateway').value,
    });

    const res = await fetch(`/api/dashboard/finance?${params}`);
    const data = await res.json();

    // 🔢 Cards
    Object.keys(data.cards).forEach(key=>{
        const card = document.getElementById(key);
        if(card){
            animateAmount(card.querySelector('.amount'), data.cards[key]);
        }
    });

    // 📊 Chart
    if(financeChart) financeChart.destroy();

    financeChart = new Chart(document.getElementById('main-chart'), {
        type:'bar',
        data:{
            labels: data.chart.labels,
            datasets:[{
                label:'Shipments',
                data: data.chart.values,
                backgroundColor:'#2563eb',
                borderRadius:8,
                barThickness:26
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            animation:{ duration:1200 },
            plugins:{ legend:{display:false} },
            scales:{ y:{ beginAtZero:true } }
        }
    });

    // 📋 Table
    const tbody = document.getElementById('paymentsTable');
    tbody.innerHTML = '';

    data.payments.forEach(p=>{
        tbody.innerHTML += `
            <tr>
                <td>#${p.id}</td>
                <td>${p.gateway}</td>
                <td>${p.amount.toLocaleString()} EGP</td>
                <td><span class="badge bg-${p.status_color}">${p.status}</span></td>
                <td class="text-muted small">${p.date}</td>
            </tr>
        `;
    });
}

document.getElementById('applyFilter').addEventListener('click', loadFinance);
document.addEventListener('DOMContentLoaded', loadFinance);
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    const ctx = document.getElementById('shipmentsChart').getContext('2d');

    // البيانات الافتراضية (أسبوع)
    let labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    let data = [1200, 2500, 1800, 3000, 2200, 2800, 3500]; // مبالغ افتراضية

    const shipmentsChart = new Chart(ctx,{
        type:'bar',
        data:{
            labels: labels,
            datasets:[{
                label:'إيرادات الشحنات',
                data:data,
                backgroundColor:'#1d4ed8',
                borderRadius:8
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            animation:{
                duration:1500,
                easing:'easeOutQuart'
            },
            plugins:{
                legend:{
                    display:true,
                    position:'bottom'
                },
                tooltip:{
                    callbacks:{
                        label: ctx => `${ctx.dataset.label}: ${ctx.raw.toLocaleString()} EGP`
                    }
                }
            },
            scales:{
                x:{ grid:{display:false} },
                y:{
                    beginAtZero:true,
                    ticks:{
                        callback:v => v.toLocaleString()
                    }
                }
            }
        }
    });

    // فلتر الأيام
    const filter = document.getElementById('filterChart');
    filter.addEventListener('change', () => {
        const days = parseInt(filter.value);

        // ⚡ لاحقًا يمكن جلب البيانات من API حسب الأيام
        if(days === 7){
            shipmentsChart.data.labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
            shipmentsChart.data.datasets[0].data = [1200,2500,1800,3000,2200,2800,3500];
        } else if(days === 30){
            shipmentsChart.data.labels = Array.from({length:30}, (_,i)=> `Day ${i+1}`);
            shipmentsChart.data.datasets[0].data = Array.from({length:30}, ()=> Math.floor(Math.random()*5000)+1000);
        } else if(days === 365){
            shipmentsChart.data.labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            shipmentsChart.data.datasets[0].data = Array.from({length:12}, ()=> Math.floor(Math.random()*120000)+20000);
        }

        shipmentsChart.update();
    });

});
</script>

