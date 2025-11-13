<div class="box box-success">
    <div class="box-header"><h4>📈 {{ __('Live Hours') }}</h4></div>
    <div class="box-body" style="height:380px;">
        <canvas id="salaryChart" style="width:100%; height:100%;"></canvas>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        var ctx = document.getElementById('salaryChart').getContext('2d');
        var salaryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: '{{ __("Live Hours") }}',
                    data: [],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        fetch('{{ url($prefix . "/statistics/top-users-data") }}')
            .then(response => response.json())
            .then(data => {
                salaryChart.data.labels = data.labels;
                salaryChart.data.datasets[0].data = data.data;
                salaryChart.update();
            })
            .catch(err => console.error('Error loading chart data:', err));
    })();
</script>
