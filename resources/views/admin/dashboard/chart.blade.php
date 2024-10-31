<div>
    <form action="">
        <input type="month" name="date">
        <button>filter</button>
    </form>
</div>
<style>

.card {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    width: 44%; /* Default width */
    margin: inherit;
    padding: 20px;
    background-color: #e9dbdb;
    margin-top: 10px;
}

@media (max-width: 767px) {
        .card {
            width: 100%; /* Full width for mobile screens */
            padding: 15px;
        }

        /* Centering chart on mobile */
        .chart-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .chart-container canvas {
            max-width: 100%;
        }
    }
ul.list-unstyled {
    font-size: 1.2em;
    font-weight: bold;
}
</style>

<div class="card">
    <div class="row">
        <!-- Left content with table -->
        <div class="col-md-6 d-flex align-items-center justify-content-center">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>{{ __('admin.balance') }}</th>
                        <td>{{ number_format(@$allBalance)}}</td>
                    </tr>
                    <tr>
                        <th>{{ __('admin.availableBalance') }}</th>
                        <td>{{number_format( @$data[1]) }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('admin.balance') }} $</th>
                        <td>{{ number_format(@$balanceDollar)}}</td>
                    </tr>
                    <tr>
                        <th>{{ __('admin.used') }}</th>
                        <td>{{number_format( @$data[0]) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Right content with chart -->
        <div class="col-md-6 chart-container" style="height: 178px!important">
            <canvas id="myChart"></canvas>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 text-center">
            @if ($usePercentage >= 90)
            <div class="row">
                <div class="col-md-12 text-center">
                    <h4 class="text-danger">{{ __('admin.you_must_pay') }}</h4>
                </div>
            </div>
            @endif
            <!-- Add a button here -->
            <button type="button" class="btn btn-primary mt-3" onclick="window.location.href='admin/payment-with-method';">
                {{ __('admin.pay') }} 
            </button>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'doughnut', // نوع المخطط دائري
        data: {
            labels: ['used', 'availabel balance'],
            datasets: [{
                label: 'توزيع البيانات',
                data: <?php echo json_encode($data); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'نسبه شحن الالعاب'
                }
            }
        }
    });
</script>