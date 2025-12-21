{{-- <div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title">🏁 {{ __('Agencies Target Comparison') }}</h3>
    </div>
    <div class="box-body" style="height:500px;">
        <canvas id="agenciesCompareChart" style="width:100%; height:100%;"></canvas>
    </div>
</div>

<script>
    new Chart(document.getElementById("agenciesCompareChart"), {
        type: 'bar',
        data: {
            labels: [
                "{{ __('Achieved Targets') }}",
                "{{ __('Not Achieved') }}"
            ],
            datasets: [{
                label: "{{ __('Agencies') }}",
                data: [{{ $achieved }}, {{ $notAchieved }}],
                backgroundColor: ['#22c55e', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: "{{ __('Comparison of Agencies Achieved vs Not Achieved') }}"
                }
            },
            scales: { y: { beginAtZero: true, precision: 0 } }
        }
    });
</script> --}}
<div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title">🏁 {{ __('Agencies Target Comparison') }}</h3>
    </div>
    <div class="box-body" style="height:500px;">
        <canvas id="agenciesCompareChart" style="width:100%; height:100%;"></canvas>
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
function loadAgenciesCompareChart() {
    const canvas = document.getElementById("agenciesCompareChart");
    if (!canvas) return;

    // Check if chart already exists
    if (window.agenciesCompareChartInstance) {
        window.agenciesCompareChartInstance.destroy();
    }

    // Ensure canvas is visible and has proper dimensions
    const container = canvas.parentElement;
    if (container && container.offsetHeight > 0) {
        const endpoint = `/{{ $prefix }}/statistics/comparison-agencies-target`;

        fetch(endpoint)
            .then(response => response.json())
            .then(({ achieved, notAchieved }) => {
                const ctx = canvas.getContext("2d");

                window.agenciesCompareChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [
                            "{{ __('Achieved Targets') }}",
                            "{{ __('Not Achieved') }}"
                        ],
                        datasets: [{
                            label: "{{ __('Agencies') }}",
                            data: [achieved, notAchieved],
                            backgroundColor: ['#22c55e', '#ef4444']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: "{{ __('Comparison of Agencies Achieved vs Not Achieved') }}"
                            }
                        },
                        scales: { y: { beginAtZero: true, precision: 0 } }
                    }
                });
            })
            .catch(error => console.error('Error fetching agency comparison data:', error));
    } else {
        // If container is not visible yet, wait a bit and try again
        setTimeout(loadAgenciesCompareChart, 100);
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
            setTimeout(loadAgenciesCompareChart, 100);
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
            setTimeout(loadAgenciesCompareChart, 50);
        }
    });

    // Listen for custom tab activation event from chart.blade.php
    $(document).on('tabActivated', function(e, tabId) {
        if (tabId === '#agencies' && !chartLoaded) {
            chartLoaded = true;
            setTimeout(loadAgenciesCompareChart, 100);
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
                setTimeout(loadAgenciesCompareChart, 300);
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
                setTimeout(loadAgenciesCompareChart, 300);
            }
        }, 200);
    });
});
</script>
