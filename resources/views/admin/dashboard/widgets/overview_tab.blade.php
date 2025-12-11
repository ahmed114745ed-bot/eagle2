


    <style>
    #overview.tab-pane {
        overflow: visible !important;
    }
</style>



<style>
    /* ---------- Base Fixes ---------- */
    #overview.tab-pane {
        overflow: visible !important;
    }

    /* ---------- Floating dots background ---------- */
    .floating-dots {
        position: absolute;
        inset: 0;
        z-index: 0;
        background: radial-gradient(circle, rgba(0,0,0,0.06) 2px, transparent 3px);
        background-size: 40px 40px;
        animation: moveDots 40s linear infinite;
        opacity: 0.6;
        pointer-events: none;
    }
    
    @keyframes moveDots {
        from { background-position: 0 0; }
        to { background-position: 400px 400px; }
    }

    /* ---------- Dashboard wrapper ---------- */
    .dashboard-wrap { 
        position: relative; 
        z-index: 1; 
    }
    
    .page-padding { 
        padding: 1.25rem; 
    }

    /* ---------- Card Fixes ---------- */
    .card {
        width: 100% !important;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    /* ---------- Stats counter ---------- */
    .stat-number { 
        font-weight: 700; 
        font-size: 1.75rem; 
        transition: all 0.6s ease; 
    }

    /* ---------- Sparkline ---------- */
    .sparkline { 
        height: 32px; 
        max-width: 80px;
    }

    /* ---------- Chart card ---------- */
    .chart-card { 
        min-height: 220px; 
    }
    
    .chart-card canvas {
        max-width: 100% !important;
        height: auto !important;
    }

    /* ---------- Table avatars ---------- */
    .avatar-sm { 
        width: 36px; 
        height: 36px; 
        border-radius: 6px; 
        object-fit: cover; 
    }

    /* ---------- Heatmap Fix ---------- */
    #heatmapGrid {
        display: flex !important;
        flex-wrap: wrap !important;
        min-height: 200px;
    }

    /* ========== RESPONSIVE FIXES ========== */
    
    /* Tablet */
    @media (max-width: 992px) {
        .page-padding { 
            padding: 1rem; 
        }
        
        .chart-card { 
            min-height: 200px; 
        }
    }

    /* Mobile */
    @media (max-width: 768px) {
        .page-padding { 
            padding: 0.75rem; 
        }
        
        .stat-number { 
            font-size: 1.5rem; 
        }
        
        .chart-card { 
            min-height: 180px; 
        }
        
        .sparkline { 
            height: 28px;
            max-width: 60px;
        }
        
        .avatar-sm { 
            width: 32px; 
            height: 32px; 
        }

        /* Force full width columns */
        .row > [class*="col-md-"],
        .row > [class*="col-lg-"] {
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }

        /* Fix card content */
        .card .d-flex {
            flex-wrap: wrap;
        }

        /* Heatmap adjustment */
        #heatmapGrid {
            height: auto !important;
            min-height: 150px;
        }

        #heatmapGrid > .col {
            width: 25% !important;
            flex: 0 0 25% !important;
        }

        /* Table scroll */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }

    /* Small Mobile */
    @media (max-width: 480px) {
        .page-padding { 
            padding: 0.5rem; 
        }
        
        .stat-number { 
            font-size: 1.25rem; 
        }
        
        .chart-card { 
            min-height: 150px; 
        }
        
        .sparkline { 
            height: 24px;
            max-width: 50px;
        }
        
        .avatar-sm { 
            width: 28px; 
            height: 28px; 
        }

        /* Stack stat cards content */
        .card .d-flex.justify-content-between {
            flex-direction: column;
            text-align: center;
        }

        .card .d-flex.justify-content-between > div {
            margin-bottom: 0.5rem;
        }

        /* Smaller heatmap grid */
        #heatmapGrid > .col {
            width: 33.33% !important;
            flex: 0 0 33.33% !important;
        }

        /* Chart titles */
        .card h6 {
            font-size: 0.875rem;
        }
    }
</style>

<div class="position-relative">
    <div class="floating-dots"></div>
    <div class="dashboard-wrap page-padding">

        <!-- Top Stats -->
        <div class="row g-3 mb-4" data-aos="fade-up">
            @foreach (['users' => 'المستخدمين', 'rooms' => 'الرومات', 'agencies' => 'الوكالات'] as $key => $label)
                <div class="col-12 col-md-4">
                    <div class="card p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted">{{ $label }}</div>
                                <div id="{{ $key }}Count" class="stat-number">0</div>
                                <small id="{{ $key }}Change" class="text-success">↑ 0%</small>
                            </div>
                            <div>
                                <canvas id="spark{{ ucfirst($key) }}" class="sparkline"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Main Charts -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card chart-card p-3" data-aos="fade-up">
                    <h6>المتصلون الآن (خطي — Live)</h6>
                    <canvas id="liveLineChart"></canvas>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card chart-card p-3" data-aos="fade-up">
                    <h6>أعلى 10 رومات (هدايا)</h6>
                    <canvas id="topRoomsBar"></canvas>
                </div>
            </div>
        </div>

        <!-- Donut + Heatmap -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card p-3" data-aos="fade-up">
                    <h6>توزيع المستخدمين</h6>
                    <canvas id="usersDonut"></canvas>
                </div>
            </div>
            <div class="col-12 col-md-8">
                <div class="card p-3" data-aos="fade-up">
                    <h6>خريطة النشاط (مخطط حرارة)</h6>
                    <div class="row g-1" id="heatmapGrid" style="height:200px;"></div>
                </div>
            </div>
        </div>

        <!-- Top rooms table -->
        <div class="card p-3 mb-4" data-aos="fade-up">
            <h6>أشهر الرومات الآن</h6>
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>#</th>
                            <th>الروم</th>
                            <th>المشاهدون</th>
                            <th>الهدايا</th>
                            <th>الوكالة</th>
                        </tr>
                    </thead>
                    <tbody id="topRoomsTable"></tbody>
                </table>
            </div>
        </div>

        <!-- Gifts + Week Activity -->
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="card p-3" data-aos="fade-up">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted">الهدايا هذا الشهر</div>
                            <div id="giftsMonth" class="stat-number">0</div>
                            <small id="giftsChange" class="text-success">↑ 0%</small>
                        </div>
                        <div class="text-end">
                            <canvas id="sparkGifts" class="sparkline"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-8">
                <div class="card p-3" data-aos="fade-up">
                    <h6>معدل النشاط خلال الأسبوع</h6>
                    <canvas id="weekActivity" style="height:100px;"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
AOS.init({ once: true, duration: 650 });

function animateCounter(id, target, decimals = 0) {
    const el = document.getElementById(id);
    if (!el) return;
    let start = 0;
    const duration = 900;
    const startTime = performance.now();
    function step(now) {
        const progress = Math.min((now - startTime) / duration, 1);
        const value = start + (target - start) * progress;
        el.innerText = Number(value).toLocaleString(undefined, { maximumFractionDigits: decimals });
        if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

function createSparkline(ctx, data) {
    return new Chart(ctx, {
        type: 'line',
        data: { labels: data.map((_,i)=>i+1), datasets: [{ data, tension: 0.4, fill: true, pointRadius:0 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins:{legend:{display:false}}, scales:{x:{display:false}, y:{display:false}} }
    });
}

async function fetchDashboard() {
    try {
        const res = await fetch('/api/dashboard/summary');
        const summary = await res.json();

        // Safe defaults
        const liveSeries = summary.liveSeries || [];
        const usersDonut = summary.usersDonut || [];
        const heatmap = summary.heatmap || [];
        const topRooms = summary.topRooms || [];
        const sparkUsers = summary.sparkUsers || [];
        const sparkRooms = summary.sparkRooms || [];
        const sparkAgencies = summary.sparkAgencies || [];
        const sparkGifts = summary.sparkGifts || sparkUsers.map(s=>s*10);

        // Counters
        animateCounter('usersCount', summary.users || 0);
        document.getElementById('usersChange').innerText = (summary.usersChange>0? '↑ ':'') + (summary.usersChange || 0) + '%';
        animateCounter('roomsCount', summary.rooms || 0);
        document.getElementById('roomsChange').innerText = (summary.roomsChange>0? '↑ ':'') + (summary.roomsChange || 0) + '%';
        animateCounter('agenciesCount', summary.agencies || 0);
        animateCounter('giftsMonth', summary.giftsMonth || 0);
        document.getElementById('giftsChange').innerText = (summary.giftsChange>0? '↑ ':'') + (summary.giftsChange || 0) + '%';

        // Sparklines
        createSparkline(document.getElementById('sparkUsers').getContext('2d'), sparkUsers);
        createSparkline(document.getElementById('sparkRooms').getContext('2d'), sparkRooms);
        createSparkline(document.getElementById('sparkAgencies').getContext('2d'), sparkAgencies);
        createSparkline(document.getElementById('sparkGifts').getContext('2d'), sparkGifts);

        // Live line chart
        const lineCtx = document.getElementById('liveLineChart')?.getContext('2d');
        if (lineCtx) {
            window.liveLineChart = new Chart(lineCtx, {
                type: 'line',
                data: { labels: liveSeries.map((_,i)=> i+1), datasets: [{ label: 'Online', data: liveSeries, tension: 0.35, fill:true }] },
                options: { animation:{duration:700}, responsive:true }
            });
        }

        // Top rooms bar
        const barCtx = document.getElementById('topRoomsBar')?.getContext('2d');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: { labels: topRooms.map(r=> r.name), datasets: [{ label:'Gifts', data: topRooms.map(r=> r.gifts) }] },
                options: { responsive:true }
            });
        }

        // Donut chart
        const donutCtx = document.getElementById('usersDonut')?.getContext('2d');
        if (donutCtx) {
            new Chart(donutCtx, { type:'doughnut', data:{ labels:['Male','Female','Other'], datasets:[{ data: usersDonut }] }, options:{ responsive:true } });
        }

        // Top rooms table
        const tBody = document.getElementById('topRoomsTable');
        if(tBody) {
            tBody.innerHTML = '';
            topRooms.forEach((r, idx)=>{
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${idx+1}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="${r.avatar}" class="avatar-sm me-2" alt="">
                            <div>
                                <div class="fw-semibold">${r.name}</div>
                                <small class="text-muted">${r.id} • ${r.viewers} viewers</small>
                            </div>
                        </div>
                    </td>
                    <td>${r.viewers?.toLocaleString() || 0}</td>
                    <td>${r.gifts?.toLocaleString() || 0}</td>
                    <td><span class="badge bg-light text-dark pulse">${r.agency || '-'}</span></td>
                `;
                tBody.appendChild(tr);
            });
        }

        // Heatmap
        const heatmapGrid = document.getElementById('heatmapGrid');
        if(heatmapGrid) {
            heatmapGrid.innerHTML = '';
            const max = Math.max(...heatmap, 1);
            for (let i=0;i<12;i++){
                const col = document.createElement('div');
                col.className = 'col';
                col.style.flex = '0 0 auto';
                col.style.width = (100/12) + '%';
                const val = heatmap[i] || 0;
                const intensity = Math.round((val / max) * 100);
                col.innerHTML = `<div style="height:100%; background: linear-gradient(180deg, rgba(0,0,0,0.03), rgba(0,0,0,0.03)), hsl(10, 70%, ${90 - intensity/1.6}%); display:flex; align-items:center; justify-content:center; font-size:12px;">${val}</div>`;
                heatmapGrid.appendChild(col);
            }
        }

    } catch(err) {
        console.error('Failed to load dashboard data', err);
    }
}

document.addEventListener('DOMContentLoaded', fetchDashboard);
</script>
