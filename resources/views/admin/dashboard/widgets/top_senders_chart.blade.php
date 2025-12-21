{{-- <div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title">💸 {{ __('Top Senders') }}</h3>
    </div>
    <div class="box-body" style="height:500px;">
        <canvas id="topSendersPolar" style="width:100%; height:100%;"></canvas>
    </div>
</div>

<script>
    new Chart(document.getElementById("topSendersPolar"), {
        type: 'polarArea',
        data: {
            labels: @json($labels),
            datasets: [{
                data: @json($data),
                backgroundColor: [
                    '#f87171','#60a5fa','#34d399','#fbbf24',
                    '#a78bfa','#f472b6','#38bdf8','#facc15',
                    '#ef4444','#10b981'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: '{{ __("Top 10 Senders by Gift Value") }}' }
            }
        }
    });
</script> --}}

<div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title">💸 {{ __('Top Senders') }}</h3>
    </div>
    <div class="box-body" style="height:500px;">
        <canvas id="topSendersPolar" style="width:100%; height:100%;"></canvas>
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
function loadTopSendersChart() {
    const canvas = document.getElementById("topSendersPolar");
    if (!canvas) return;

    // Check if chart already exists
    if (window.topSendersChartInstance) {
        window.topSendersChartInstance.destroy();
    }

    // Ensure canvas is visible and has proper dimensions
    const container = canvas.parentElement;
    if (container && container.offsetHeight > 0) {
        $.ajax({
            url: "{{ url($prefix . '/statistics/top-sender') }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                const ctx = canvas.getContext("2d");

                window.topSendersChartInstance = new Chart(ctx, {
                    type: 'polarArea',
                    data: {
                        labels: response.labels,
                        datasets: [{
                            data: response.data,
                            backgroundColor: [
                                '#f87171','#60a5fa','#34d399','#fbbf24',
                                '#a78bfa','#f472b6','#38bdf8','#facc15',
                                '#ef4444','#10b981'
                            ],
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { color: '#333', font: { size: 13 } }
                            },
                            title: {
                                display: true,
                                text: '{{ __("Top 10 Senders by Gift Value") }}'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const name = context.label || '';
                                        const value = context.formattedValue;
                                        return `${name}: ${value}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            r: {
                                ticks: { display: true },
                                grid: { color: '#ddd' }
                            }
                        }
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error("Error loading chart data:", error);
            }
        });
    } else {
        // If container is not visible yet, wait a bit and try again
        setTimeout(loadTopSendersChart, 100);
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
            setTimeout(loadTopSendersChart, 100);
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
            setTimeout(loadTopSendersChart, 50);
        }
    });

    // Listen for custom tab activation event from chart.blade.php
    $(document).on('tabActivated', function(e, tabId) {
        if (tabId === '#agencies' && !chartLoaded) {
            chartLoaded = true;
            setTimeout(loadTopSendersChart, 100);
        }
    });

    // Listen for PJAX completion to reload data
    $(document).on('pjax:complete', function() {
        // Reset chart loaded flag and check if agencies tab is active
        chartLoaded = false;
        setTimeout(function() {
            const agenciesTab = document.getElementById('agencies');
            if (agenciesTab && agenciesTab.classList.contains('active') && agenciesTab.classList.contains('show')) {
                chartLoaded = true;
                setTimeout(loadTopSendersChart, 300);
            }
        }, 200);
    });

    // Also listen for pjax:end as backup
    $(document).on('pjax:end', function() {
        // Reset chart loaded flag and check if agencies tab is active
        chartLoaded = false;
        setTimeout(function() {
            const agenciesTab = document.getElementById('agencies');
            if (agenciesTab && agenciesTab.classList.contains('active') && agenciesTab.classList.contains('show')) {
                chartLoaded = true;
                setTimeout(loadTopSendersChart, 300);
            }
        }, 200);
    });
});
</script>

