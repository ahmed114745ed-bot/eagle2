{{-- <div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">🎁 {{ __('Top Receivers') }}</h3>
    </div>
    <div class="box-body" style="height:500px;">
        <canvas id="topReceiversRadar" style="width:100%; height:100%;"></canvas>
    </div>
</div>

<script>
    new Chart(document.getElementById("topReceiversRadar"), {
        type: 'radar',
        data: {
            labels: @json($labels),
            datasets: [{
                label: '{{ __("Total Received") }}',
                data: @json($data),
                backgroundColor: 'rgba(34,197,94,0.2)',
                borderColor: '#22c55e',
                pointBackgroundColor: '#22c55e'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: '{{ __("Top 10 Receivers by Gifts Received") }}' }
            }
        }
    });
</script> --}}

<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">🎁 {{ __('Top Receivers') }}</h3>
    </div>
    <div class="box-body" style="height:500px;">
        <canvas id="topReceiversRadar" style="width:100%; height:100%;"></canvas>
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
function loadTopReceiversChart() {
    const canvas = document.getElementById("topReceiversRadar");
    if (!canvas) return;

    // Check if chart already exists
    if (window.topReceiversChartInstance) {
        window.topReceiversChartInstance.destroy();
    }

    // Ensure canvas is visible and has proper dimensions
    const container = canvas.parentElement;
    if (container && container.offsetHeight > 0) {
        fetch('{{ url($prefix . "/statistics/top-receiver") }}')
            .then(response => response.json())
            .then(({ labels, data }) => {
                const ctx = canvas.getContext("2d");

                window.topReceiversChartInstance = new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels,
                        datasets: [{
                            label: '{{ __("Total Received") }}',
                            data,
                            backgroundColor: 'rgba(34,197,94,0.2)',
                            borderColor: '#22c55e',
                            pointBackgroundColor: '#22c55e'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: '{{ __("Top 10 Receivers by Gifts Received") }}'
                            }
                        },
                        scales: {
                            r: {
                                beginAtZero: true,
                                grid: { color: '#d1d5db' },
                                angleLines: { color: '#d1d5db' },
                                pointLabels: { font: { size: 14 } }
                            }
                        }
                    }
                });
            })
            .catch(error => console.error('Error loading chart data:', error));
    } else {
        // If container is not visible yet, wait a bit and try again
        setTimeout(loadTopReceiversChart, 100);
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
            setTimeout(loadTopReceiversChart, 100);
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
            setTimeout(loadTopReceiversChart, 50);
        }
    });

    // Listen for custom tab activation event from chart.blade.php
    $(document).on('tabActivated', function(e, tabId) {
        if (tabId === '#agencies' && !chartLoaded) {
            chartLoaded = true;
            setTimeout(loadTopReceiversChart, 100);
        }
    });
});
</script>

