
<div class="position-relative">
    <div class="floating-dots"></div>
    <div class="dashboard-wrap page-padding">

        <div class="cards-container" data-aos="fade-up">
            @foreach ([
                'total_balance'      => ['Total Balance', 'fa-wallet'],
                'pending_balance'    => ['Pending Balance', 'fa-clock'],
                'available_balance'  => ['Available Balance', 'fa-money-bill-wave'],
                'available_balance2' => ['Available Balance', 'fa-coins']
            ] as $key => [$label, $icon])
                <div class="card finance-card" id="{{ $key }}">
                    <div class="card-icon">
                        <i class="fa-solid {{ $icon }}"></i>
                    </div>

                    <h3 style="margin-top: 0px !important;">{{ $label }}</h3>
                    <p class="amount">0 EGP</p>
                    <small>{{ $label }}</small>
                </div>
            @endforeach
        </div>


        <div class="row" >
          
            <div class="col-md-6 col-sm-12" ></div>
             <div class="col-md-6 col-sm-12" >
                <div class="main-chart-container" data-aos="fade-up">
                    <h3 style="margin-bottom:15px;">Monthly Financial Overview</h3>
                    <canvas id="main-chart"></canvas>
                </div>
            </div>
        </div>
        

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<style>
.floating-dots { position:absolute; inset:0; z-index:0; background: radial-gradient(circle, rgba(0,0,0,0.06) 2px, transparent 3px); background-size:40px 40px; animation: moveDots 40s linear infinite; opacity:0.6; pointer-events:none; }
@keyframes moveDots { from { background-position:0 0;} to { background-position:400px 400px; } }

.dashboard-wrap{ position:relative; z-index:1; }
.page-padding{ padding:1.25rem; }

.cards-container { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:20px; margin-bottom:40px; }
.card { background:#fff; border-radius:12px; padding:7px 12px; text-align:end; box-shadow:0 4px 12px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s; overflow:hidden; }
.card:hover { transform: translateY(-5px); box-shadow:0 8px 20px rgba(0,0,0,0.15); }
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

.main-chart-container canvas {
    width: 100% !important;
    height: 100% !important;
}

.chart-title {
    margin-bottom: 12px;
    font-weight: 600;
}
@media (max-width:600px){ .cards-container{ grid-template-columns:1fr; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
AOS.init({ once:true, duration:650 });

function animateCounter(element, start, end, duration=1500){
    let startTimestamp=null;
    const step=(timestamp)=>{
        if(!startTimestamp) startTimestamp=timestamp;
        const progress=Math.min((timestamp-startTimestamp)/duration,1);
        element.innerText=Math.floor(progress*(end-start)+start).toLocaleString() + " EGP";
        if(progress<1) window.requestAnimationFrame(step);
    };
    window.requestAnimationFrame(step);
}

function createSparkline(ctx,data,color){
    const gradient=ctx.createLinearGradient(0,0,0,120);
    gradient.addColorStop(0,color+'33'); gradient.addColorStop(1,color+'00');
    return new Chart(ctx,{
        type:'line',
        data:{labels:['Jan','Feb','Mar','Apr'], datasets:[{data, borderColor:color, backgroundColor:gradient, fill:true, tension:0.4, borderWidth:3, pointRadius:5, pointBackgroundColor:color}]},
        options:{responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{x:{display:false},y:{display:false}}, animation:{duration:1500,easing:'easeOutQuart'}}
    });
}

document.addEventListener('DOMContentLoaded',function(){
    const financialData = {
        total_balance: { value:100000, chart:[50000,65000,85000,100000], color:'#1d4ed8' },
        pending_balance: { value:25000, chart:[5000,12000,20000,25000], color:'#f59e0b' },
        available_balance: { value:75000, chart:[45000,55000,70000,75000], color:'#10b981' },
       /* last_transaction: { value:12500, chart:[5000,8000,11000,12500], color:'#ef4444' }*/
    };

    Object.keys(financialData).forEach(key=>{
        const card=document.getElementById(key);
        animateCounter(card.querySelector('.amount'),0,financialData[key].value);
        const ctx=document.getElementById('chart-'+key).getContext('2d');
        createSparkline(ctx,financialData[key].chart,financialData[key].color);
    });

document.addEventListener('DOMContentLoaded', () => {

    const ctx = document.getElementById('main-chart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'],
            datasets: [
                {
                    label: 'Total Balance',
                    data: [50000,65000,85000,100000,95000,110000,120000,125000],
                    backgroundColor: 'rgba(29, 78, 216, 0.75)',
                    borderRadius: 8,
                    barThickness: 28,
                },
                {
                    label: 'Pending Balance',
                    data: [5000,12000,20000,25000,30000,35000,40000,45000],
                    backgroundColor: 'rgba(245, 158, 11, 0.75)',
                    borderRadius: 8,
                    barThickness: 28,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // 👈 يعتمد على CSS
            animation: {
                duration: 1600,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 14,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) =>
                            `${ctx.dataset.label}: ${ctx.raw.toLocaleString()}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => v.toLocaleString()
                    }
                }
            }
        }
    });

});

});
</script>
