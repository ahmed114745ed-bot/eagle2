<div class="box box-success">
    <div class="box-header with-border">
    <h4>{{ __('title_user') }}</h4>
    </div>
    <div class="box-body">
        <canvas id="usersOnlineChart" height="430"></canvas>
    </div>
</div>

@php
        $fetchUrl = admin_url('users-online-stats');

@endphp

@if(request()->is('superadmin') || request()->is('admin/superadmin/statistics') || request()->is('admin'))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            addToAjaxQueue(() => {
                const ctx = document.getElementById('usersOnlineChart').getContext('2d');

                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Users'],
                        datasets: [
                            {
                                label: "{{ __('online_users') }}",
                                data: [0],
                                borderColor: '#28a745',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.3
                            },
                            {
                                label: "{{ __('offline_users') }}",
                                data: [0],
                                borderColor: '#dc3545',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "{{ __('online_users') }}"
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "{{ __('offline_users') }}"
                                }
                            }
                        },
                        plugins: {
                            legend: { position: 'top' }
                        }
                    }
                });

                return fetch("{{ $fetchUrl }}", {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(async res => {
                        const contentType = res.headers.get("content-type");
                        if (contentType && contentType.includes("application/json")) {
                            return res.json();
                        } else {
                            const text = await res.text();
                            throw new Error("Expected JSON, got: " + text);
                        }
                    })
                    .then(data => {
                        chart.data.datasets[0].data = [data.online];
                        chart.data.datasets[1].data = [data.offline];
                        chart.update();
                        console.log("Online:", data.online, "Offline:", data.offline);

                        setInterval(() => {
                            fetch("{{ $fetchUrl }}", {
                                headers: { 'Ac
@endif
