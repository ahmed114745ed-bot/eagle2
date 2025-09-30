<div class="card">
    <div class="card-header">
        <h4>{{ __('Peak Hour Analytics') }}</h4>
        <select id="peak-filter" class="form-control" style="width: 200px; display:inline-block;">
            <option value="day">{{ __('Today') }}</option>
            <option value="week">{{ __('This Week') }}</option>
            <option value="month">{{ __('This Month') }}</option>
        </select>
    </div>
    <div class="card-body">
        <canvas id="peakChart" style="height: 300px;"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const translations = {
        count_sessions: "{{ __('count_sessions') }}",

    };
</script>
<script>
    let peakChart;

    function renderChart(labels, data) {
        const ctx = document.getElementById('peakChart').getContext('2d');

        if (peakChart) {
            peakChart.destroy();
        }

        peakChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: translations.count_sessions,
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    function loadPeakData(period = 'day') {
        $.ajax({
            url: "{{ superadmin_url('peak-hours') }}",
            data: { period: period },
            success: function (res) {
                if (res.success) {
                    renderChart(res.labels, res.data);
                } else {
                    renderChart([], []);
                }
            }
        });
    }

    $('#peak-filter').on('change', function () {
        loadPeakData($(this).val());
    });

    loadPeakData();
</script>
