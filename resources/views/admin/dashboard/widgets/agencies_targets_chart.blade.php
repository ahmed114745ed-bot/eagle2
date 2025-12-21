<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">🏆 {{ __('Top Agencies by Achieved Targets') }}</h3>
    </div>
    <div class="box-body" style="height:500px;">
        <canvas id="agenciesTargetsChart" style="width:100%; height:100%;"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
function loadAgenciesTargetsChart() {
    const canvas = document.getElementById("agenciesTargetsChart");
    if (!canvas) return;

    // Check if chart already exists
    if (window.agenciesTargetsChartInstance) {
        window.agenciesTargetsChartInstance.destroy();
    }

    // Ensure canvas is visible and has proper dimensions
    const container = canvas.parentElement;
    if (container && container.offsetHeight > 0) {
        $.ajax({
            url: "{{ url($prefix . '/statistics/agency-target') }}",
            type: "GET",
            dataType: "json",
            success: function (response) {
                const ctxTargets = canvas.getContext("2d");

                window.agenciesTargetsChartInstance = new Chart(ctxTargets, {
                    type: 'bar',
                    data: {
                        labels: response.labels,
                        datasets: [{
                            label: '{{ __("Achieved Targets") }}',
                            data: response.data,
                            backgroundColor: [
                                '#4ade80','#60a5fa','#f87171','#fbbf24',
                                '#a78bfa','#f472b6','#38bdf8','#facc15',
                                '#ef4444','#10b981'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: true },
                            title: {
                                display: true,
                                text: '{{ __("Top 10 Agencies by Targets Achieved") }}'
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 },
                                title: { display: true, text: '{{ __("Targets") }}' }
                            },
                            y: {
                                title: { display: true, text: '{{ __("Agencies") }}' }
                            }
                        }
                    }
                });
            },
            error: function (xhr, status, error) {
                console.error("Error loading chart data:", error);
            }
        });
    } else {
        // If container is not visible yet, wait a bit and try again
        setTimeout(loadAgenciesTargetsChart, 100);
    }
}

// Load chart when agencies tab is shown
$(document).ready(function () {
    let chartLoaded = false;

    // Function to check and load chart if needed
    function checkAndLoadChart() {
        const agenciesTab = document.getElementById('agencies');
        if (agenciesTab && agenciesTab.classList.contains('active') && agenciesTab.classList.contains('show') && !chartLoaded) {
            chartLoaded = true;
            setTimeout(loadAgenciesTargetsChart, 100);
        }
    }

    // Use MutationObserver to watch for tab changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                checkAndLoadChart();
            }
        });
    });

    // Observe the agencies tab pane
    const agenciesTab = document.getElementById('agencies');
    if (agenciesTab) {
        observer.observe(agenciesTab, {
            attributes: true,
            attributeFilter: ['class']
        });

        // Check immediately and periodically for the first few seconds
        checkAndLoadChart();
        const checkInterval = setInterval(function() {
            checkAndLoadChart();
            // Stop checking after 5 seconds
            if (Date.now() - window.pageLoadTime > 5000) {
                clearInterval(checkInterval);
            }
        }, 200);

        // Store page load time
        if (!window.pageLoadTime) {
            window.pageLoadTime = Date.now();
        }
    }

    // Also listen for Bootstrap tab events as backup
    $('a[data-bs-toggle="tab"][data-bs-target="#agencies"]').on('shown.bs.tab', function (e) {
        if (!chartLoaded) {
            chartLoaded = true;
            setTimeout(loadAgenciesTargetsChart, 50);
        }
    });

    // Listen for custom tab activation event from chart.blade.php
    $(document).on('tabActivated', function(e, tabId) {
        if (tabId === '#agencies' && !chartLoaded) {
            chartLoaded = true;
            setTimeout(loadAgenciesTargetsChart, 100);
        }
    });
});
</script>

