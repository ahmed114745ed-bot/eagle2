


    <style>
    #overview.tab-pane {
        overflow: visible !important;
    }
</style>

<div class="position-relative">
    {{-- Floating Dots Background --}}
    <div class="floating-dots"></div>

    <div class="dashboard-wrap page-padding">

        {{-- Top stats counters --}}
        <div class="row g-3 mb-4" data-aos="fade-up">
            @foreach([
                ['title'=>'المستخدمين','id'=>'users','changeId'=>'usersChange','spark'=>'sparkUsers','class'=>'text-success'],
                ['title'=>'الرومات','id'=>'rooms','changeId'=>'roomsChange','spark'=>'sparkRooms','class'=>'text-success'],
                ['title'=>'الوكالات','id'=>'agencies','changeId'=>'agenciesChange','spark'=>'sparkAgencies','class'=>'text-muted']
            ] as $item)
            <div class="col-12 col-md-4">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted">{{ $item['title'] }}</div>
                            <div id="{{ $item['id'] }}Count" class="stat-number">0</div>
                            <small id="{{ $item['changeId'] }}" class="{{ $item['class'] }}">—</small>
                        </div>
                        <div>
                            <canvas id="{{ $item['spark'] }}" class="sparkline"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Main Charts --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card chart-card p-3" data-aos="fade-up">
                    <h6>المتصلون الآن (خطي — Live)</h6>
                    <canvas id="liveLineChart" style="width:100%; height:260px"></canvas>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card chart-card p-3" data-aos="fade-up">
                    <h6>أعلى 10 رومات (هدايا)</h6>
                    <canvas id="topRoomsBar" style="width:100%; height:260px"></canvas>
                </div>
            </div>
        </div>

        {{-- Distribution & Heatmap --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card p-3" data-aos="fade-up">
                    <h6>توزيع المستخدمين</h6>
                    <canvas id="usersDonut" style="width:100%; height:200px"></canvas>
                </div>
            </div>
            <div class="col-12 col-md-8">
                <div class="card p-3" data-aos="fade-up">
                    <h6>خريطة النشاط (مخطط حرارة تقريبي)</h6>
                    <div class="row g-1" id="heatmapGrid" style="height:200px;"></div>
                </div>
            </div>
        </div>

        {{-- Top Rooms Table --}}
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

        {{-- Footer Stats --}}
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
                    <canvas id="weekActivity" style="width:100%; height:100px"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>


<style>
.floating-dots{position:absolute;inset:0;z-index:0;background:radial-gradient(circle, rgba(0,0,0,0.06) 2px, transparent 3px);background-size:40px 40px;animation:moveDots 40s linear infinite;opacity:.6;pointer-events:none;}
@keyframes moveDots{from{background-position:0 0}to{background-position:400px 400px}}
.dashboard-wrap{position:relative;z-index:1}
.stat-number{font-weight:700;font-size:1.75rem}
.pulse{position:relative;display:inline-block}
.pulse::after{content:'';position:absolute;top:-6px;left:-6px;right:-6px;bottom:-6px;border-radius:8px;background:rgba(0,200,83,.15);animation:pulseAnim 1.6s infinite;z-index:-1}
@keyframes pulseAnim{0%{transform:scale(1);opacity:.9}100%{transform:scale(1.9);opacity:0}}
.sparkline{height:32px}
.chart-card{min-height:220px}
.avatar-sm{width:36px;height:36px;border-radius:6px;object-fit:cover}
.page-padding{padding:1.25rem}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<script>
AOS.init({once:true,duration:650});

// ---- Animate Counter ----
function animateCounter(id,target,decimals=0){
    const el=document.getElementById(id);
    let start=0;
    const duration=900;
    const startTime=performance.now();
    function step(now){
        const progress=Math.min((now-startTime)/duration,1);
        const value=start+(target-start)*progress;
        el.innerText=Number(value).toLocaleString(undefined,{maximumFractionDigits:decimals});
        if(progress<1)requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

// ---- Sparkline ----
function createSparkline(ctx,data){
    return new Chart(ctx,{
        type:'line',
        data:{labels:data.map((_,i)=>i+1),datasets:[{data,tension:0.4,fill:true,pointRadius:0}]},
        options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{display:false},y:{display:false}}}
    });
}

// ---- API Validator ----
function validateKeys(obj,required,apiName){
    required.forEach(key=>{if(!(key in obj))console.error(`❌ API ERROR (${apiName}): Missing key → ${key}`);});
}

// ---- Fetch Dashboard ----
async function fetchDashboard(){
    try {
        const res=await fetch('/api/dashboard/summary');
        const summary=await res.json();
        validateKeys(summary,['users','usersChange','rooms','roomsChange','agencies','giftsMonth','giftsChange','sparkUsers','sparkRooms','sparkAgencies'],'summary');

        animateCounter('usersCount',summary.users);
        document.getElementById('usersChange').innerText=(summary.usersChange>0?'↑ ':'')+summary.usersChange+'%';
        animateCounter('roomsCount',summary.rooms);
        document.getElementById('roomsChange').innerText=(summary.roomsChange>0?'↑ ':'')+summary.roomsChange+'%';
        animateCounter('agenciesCount',summary.agencies);
        animateCounter('giftsMonth',summary.giftsMonth);
        document.getElementById('giftsChange').innerText=(summary.giftsChange>0?'↑ ':'')+summary.giftsChange+'%';

        createSparkline(document.getElementById('sparkUsers').getContext('2d'),summary.sparkUsers);
        createSparkline(document.getElementById('sparkRooms').getContext('2d'),summary.sparkRooms);
        createSparkline(document.getElementById('sparkAgencies').getContext('2d'),summary.sparkAgencies);
        createSparkline(document.getElementById('sparkGifts').getContext('2d'),summary.sparkUsers.map(s=>s*10));

        // Live chart
        const chartsRes=await fetch('/api/dashboard/charts');
        const charts=await chartsRes.json();
        validateKeys(charts,['liveSeries','usersDonut','heatmap'],'charts');

        window.liveLineChart=new Chart(document.getElementById('liveLineChart').getContext('2d'),{
            type:'line',
            data:{labels:charts.liveSeries.map((_,i)=>i+1),datasets:[{label:'Online',data:charts.liveSeries,tension:0.35,fill:true}]},
            options:{animation:{duration:700},responsive:true}
        });

        new Chart(document.getElementById('usersDonut').getContext('2d'),{
            type:'doughnut',
            data:{labels:['Male','Female','Other'],datasets:[{data:charts.usersDonut}]},
            options:{responsive:true}
        });

        // Heatmap
        const heatmap=document.getElementById('heatmapGrid'); heatmap.innerHTML='';
        const max=Math.max(...charts.heatmap);
        for(let i=0;i<12;i++){
            const col=document.createElement('div');
            col.className='col'; col.style.flex='0 0 auto'; col.style.width=(100/12)+'%';
            const val=charts.heatmap[i];
            const intensity=Math.round((val/max)*100);
            col.innerHTML=`<div style="height:100%; background:linear-gradient(180deg, rgba(0,0,0,0.03), rgba(0,0,0,0.03)), hsl(10,70%,${90-intensity/1.6}%); display:flex; align-items:center; justify-content:center; font-size:12px;">${val}</div>`;
            heatmap.appendChild(col);
        }

        // Top Rooms
        const roomsRes=await fetch('/api/dashboard/top-rooms');
        const roomsData=await roomsRes.json();
        validateKeys(roomsData,['topRooms'],'topRooms');
        const tBody=document.getElementById('topRoomsTable'); tBody.innerHTML='';
        roomsData.topRooms.forEach((r,idx)=>{
            const tr=document.createElement('tr');
            tr.innerHTML=`
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
                <td>${r.viewers.toLocaleString()}</td>
                <td>${r.gifts.toLocaleString()}</td>
                <td><span class="badge bg-light text-dark pulse">${r.agency}</span></td>
            `;
            tBody.appendChild(tr);
        });

        // Week activity
        createSparkline(document.getElementById('weekActivity').getContext('2d'),Array.from({length:7}).map(()=> Math.floor(100+Math.random()*400)));

        // Simulate live updater
        setInterval(()=>{
            const next=Math.floor(200+Math.random()*600);
            window.liveLineChart.data.datasets[0].data.push(next);
            if(window.liveLineChart.data.datasets[0].data.length>60) window.liveLineChart.data.datasets[0].data.shift();
            window.liveLineChart.update('none');
        },5000);

    } catch(err){
        console.error('Failed to load dashboard data',err);
    }
}

document.addEventListener('DOMContentLoaded',fetchDashboard);
</script>
