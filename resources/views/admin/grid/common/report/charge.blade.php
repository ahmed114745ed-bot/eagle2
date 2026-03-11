<div class="box-body no-padding">
    <div class="nav-scroll-container">
        <ul class="nav nav-pills">
            <li class="{{ request()->name == 'dash' || request()->name == null ? 'active' : '' }}">
                <a href="?name=" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('dash_repo') }}
                </a>
            </li>
            <li class="{{ request()->name == 'shipping-agency-activity' ? 'active' : '' }}">
                <a href="?name=shipping-agency-activity" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('app_repo') }}
                </a>
            </li>
            <li class="{{ request()->name == 'host' ? 'active' : '' }}">
                <a href="?name=host" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('charge host agent') }}
                </a>
            </li>
            <li class="{{ request()->name == 'stripe' ? 'active' : '' }}">
                <a href="?name=stripe" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('payment gateway') }}
                </a>
            </li>
            <li class="{{ request()->name == 'in-app-purchas' ? 'active' : '' }}">
                <a href="?name=in-app-purchas" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('Recharge for self') }}
                </a>
            </li>
            <li class="{{ request()->name == 'exchange' ? 'active' : '' }}">
                <a href="?name=exchange" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('Convert diamonds to coins') }}
                </a>
            </li>
        </ul>
    </div>

    <div class="col">
        <h4 class="details-title">{{ __('Details') }}</h4>

        <!-- Stats Cards Container (loaded via AJAX) -->
        <div class="stats-cards-container" id="ajax-stats-container" style="min-height: 100px;">
            <div style="text-align: center; width: 100%; padding: 20px; color: var(--text-secondary-color, #333);">
                <i class="fa fa-spinner fa-spin fa-2x"></i> <span style="margin-right: 10px;">{{ __('Loading stats...') }}</span>
            </div>
        </div>
    </div>

<script>
    function loadChargeStats(queryString) {
        var url = "{{ url(config('admin.route.prefix') . '/charges-reports-stats') }}" + (queryString || window.location.search);
        var container = document.getElementById('ajax-stats-container');
        container.innerHTML = '<div style="text-align:center;width:100%;padding:20px;"><i class="fa fa-spinner fa-spin fa-2x"></i></div>';

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(function(response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(function(data) {
            container.innerHTML = '';
            if (data && data.stats && data.stats.length) {
                data.stats.forEach(function(stat) {
                    var subValueHtml = stat.sub_value
                        ? '<div style="font-size:.9rem;font-weight:600;opacity:.85;border-top:1px solid rgba(255,255,255,.2);margin-top:5px;padding-top:5px;"><i class="fa fa-dollar-sign" style="font-size:.8rem;"></i> ' + stat.sub_value + '</div>'
                        : '';
                    container.insertAdjacentHTML('beforeend',
                        '<div class="stat-card stat-card-' + stat.color + '">' +
                            '<div class="stat-card-icon"><i class="fa ' + stat.icon + '"></i></div>' +
                            '<div class="stat-card-content">' +
                                '<span class="stat-card-label">' + stat.label + '</span>' +
                                '<span class="stat-card-value">' + stat.value + '</span>' +
                                subValueHtml +
                            '</div>' +
                        '</div>'
                    );
                });
            } else {
                container.innerHTML = '<div style="padding:15px;opacity:.6;">{{ __("No data available") }}</div>';
            }
        })
        .catch(function(err) {
            console.error('Stats error:', err);
            container.innerHTML = '<div style="color:red;padding:15px;">{{ __("Error loading stats. Please reload.") }}</div>';
        });
    }

    // Load on page ready
    document.addEventListener("DOMContentLoaded", function() {
        loadChargeStats();

        // Reload stats when a tab is clicked (tab links use ?name= param)
        document.querySelectorAll('.charge_action').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var href = this.getAttribute('href');
                // Update URL without page reload via pushState
                window.history.pushState({}, '', location.pathname + href);
                loadChargeStats('?' + href.replace(/^\?/, ''));
                // Also trigger the grid pjax reload
                var pjaxContainer = document.querySelector('[data-pjax-container]');
                if (pjaxContainer && typeof $.pjax !== 'undefined') {
                    $.pjax.reload({container: '#pjax-container', url: location.pathname + href});
                } else {
                    window.location.href = location.pathname + href;
                }
            });
        });
    });
</script>
</div>

<style>
    .nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover {
        background-color: var(--primary-color);
    }

    .nav-pills>li.active>a, .nav-pills>li.active>a:hover, .nav-pills>li.active>a:focus {
        border-top-color: var(--primary-color);
    }

    .nav-scroll-container {
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }

    .nav-pills {
        display: inline-flex;
        padding: 10px 0;
    }

    .nav-pills li {
        display: inline-block;
    }

    .details-title {
        margin-bottom: 20px;
        font-weight: 600;
        font-size: 1.2rem;
        color: var(--text-secondary-color);
    }

    /* Stats Cards Styles */
    .stats-cards-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 15px 0;
    }

    .stat-card {
        flex: 1;
        min-width: 250px;
        max-width: 350px;
        background: var(--gradient-primary);
        border-radius: 16px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transition: transform 0.5s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    .stat-card:hover::before {
        transform: scale(2);
    }

    /* Card Colors */
    .stat-card-primary {
        background: var(--gradient-primary);
    }

    .stat-card-success {
        background: var(--gradient-primary);
    }

    .stat-card-warning {
        background: var(--gradient-primary);
    }

    .stat-card-danger {
        background: var(--gradient-primary);
    }

    .stat-card-info {
        background: var(--gradient-primary);
    }

    .stat-card-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-card-icon i {
        font-size: 24px;
        color: var(--text-secondary-color);
    }

    .stat-card-content {
        display: flex;
        flex-direction: column;
        gap: 8px;
        z-index: 1;
    }

    .stat-card-label {
        font-size: 0.9rem;
        color: var(--text-secondary-color);
        font-weight: 500;
    }

    .stat-card-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-secondary-color);
        letter-spacing: -0.5px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-cards-container {
            flex-direction: column;
        }

        .stat-card {
            max-width: 100%;
        }
    }

    /* Dark Mode Support */
    .dark-mode .details-title {
        color: var(--text-secondary-color);
    }
</style>
