
<div class="row g-3">

    <!-- Agencies Count -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-olive">
            <span class="info-box-icon"><i class="fa fa-building"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Agencies Count') }}</span>
                <span class="info-box-number" data-stat="agencyCount"></span>
                <a href="{{ admin_url('agencies') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Total Agency Salary -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-lime">
            <span class="info-box-icon"><i class="fa fa-building"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Total Agency Salary') }}</span>
                <span class="info-box-number" data-stat="agency_salaries"></span>
                <a href="{{ admin_url('agencies') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Total Users Salary -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-gray">
            <span class="info-box-icon"><i class="fa fa-money"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Total Users Salary') }}</span>
                <span class="info-box-number" data-stat="user_salaries"></span>
                <a href="{{ admin_url('ag/users') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Active Agencies -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-red">
            <span class="info-box-icon"><i class="fa fa-building"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Active Agencies') }}</span>
                <span class="info-box-number" data-stat="activeAgencies"></span>
                <a href="{{ admin_url('agencies?active=true') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- New Agencies Today -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-teal">
            <span class="info-box-icon"><i class="fa fa-plus"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('New Agencies Today') }}</span>
                <span class="info-box-number" data-stat="newAgenciesToday"></span>
                <a href="{{ admin_url('agencies?created=today') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- New Agencies This Month -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-orange">
            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('New Agencies This Month') }}</span>
                <span class="info-box-number" data-stat="newAgenciesMonth"></span>
                <a href="{{ admin_url('agencies?created=month') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Average Agency Wallet -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-money"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Average Agency Wallet') }}</span>
                <span class="info-box-number" data-stat="avgAgencyWallet"></span>
                <a href="{{ admin_url('agencies') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Total Members in Agencies -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-maroon">
            <span class="info-box-icon"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Total Members in Agencies') }}</span>
                <span class="info-box-number" data-stat="totalMembers"></span>
                <a href="{{ admin_url('users?agencyMembers=1') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Avg Members Per Agency -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-lime">
            <span class="info-box-icon"><i class="fa fa-user"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Avg Members Per Agency') }}</span>
                <span class="info-box-number" data-stat="avgMembersPerAgency"></span>
                <a href="{{ admin_url('agencies') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Pending Join Requests -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-purple">
            <span class="info-box-icon"><i class="fa fa-hourglass"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Pending Join Requests') }}</span>
                <span class="info-box-number" data-stat="pendingJoins"></span>
                <a href="{{ admin_url('agencies?pending=1') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Diamonds Achieved by Hosts -->
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-diamond"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Diamonds Achieved by Hosts') }}</span>
                <span class="info-box-number" data-stat="diamondsAchieved"></span>
                <a href="{{ admin_url('ag/users') }}" class="info-box-more text-white">
                    <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                </a>
            </div>
        </div>
    </div>

</div>

{{-- <style>
    .info-box {
        position: relative;
        min-height: 100px;
        border-radius: 8px;
        overflow: hidden;
        padding: 12px;
    }
    .info-box .info-box-more {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background: rgba(0,0,0,0.15);
        height: 30px;
        font-weight: 600;
        color: #fff;
        text-decoration: none;
        transition: background 0.2s ease;
    }
    .info-box:hover .info-box-more {
        background: rgba(0,0,0,0.3);
    }
</style> --}}

<style>
    .info-box {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 100px;
        border-radius: 12px;
        overflow: hidden;
        padding: 15px 20px;
        background-color: #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .info-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .info-box-icon {
        font-size: 2.5rem;
        width: 65px;
        height: 65px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .info-box-content {
        flex: 1;
        margin-left: 15px;
    }

    .info-box-text {
        font-size: 1.5rem;
        color: #040404;
        margin-bottom: 5px;
    }

    .info-box-number {
        font-size: 1.4rem;
        font-weight: 600;
        color: #222;
    }

    .info-box .info-box-more {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background: rgba(0, 0, 0, 0.15);
        height: 30px;
        font-weight: 600;
        color: #fff;
        text-decoration: none;
        transition: background 0.2s ease;
    }

    .info-box:hover .info-box-more {
        background: rgba(0, 0, 0, 0.3);
    }

    /* For large tablets and small laptops */
    @media (max-width: 1200px) {
        .info-box {
            padding: 12px 16px;
        }

        .info-box-icon {
            font-size: 2rem;
            width: 55px;
            height: 55px;
        }

        .info-box-number {
            font-size: 1.2rem;
        }
    }

    /* For tablets */
    @media (max-width: 992px) {
        .info-box {
            flex-direction: row;
            text-align: left;
        }

        .info-box-content {
            margin-left: 10px;
        }
    }

    /* For small tablets & landscape phones */
    @media (max-width: 768px) {
        .info-box {
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-height: 150px;
            padding: 18px 12px;
        }

        .info-box-icon {
            margin-bottom: 10px;
            font-size: 2.2rem;
        }

        .info-box-content {
            margin-left: 0;
        }

        .info-box-text {
            font-size: 0.95rem;
        }

        .info-box-number {
            font-size: 1.25rem;
        }

        .info-box .info-box-more {
            position: relative;
            margin-top: 8px;
            height: auto;
            background: rgba(0, 0, 0, 0.2);
            padding: 6px 0;
            width: 100%;
        }
    }

    /* For small mobile screens */
    @media (max-width: 576px) {
        .info-box {
            min-height: 130px;
            padding: 14px 10px;
            flex-direction: column;
            align-items: center;
        }

        .info-box-icon {
            font-size: 1.8rem;
            width: 50px;
            height: 50px;
        }

        .info-box-text {
            font-size: 0.9rem;
        }

        .info-box-number {
            font-size: 1.1rem;
        }

        .info-box .info-box-more {
            font-size: 0.85rem;
            padding: 4px 0;
        }
    }
</style>

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
$(function() {
    function updateStats() {
        $.ajax({
            url: '{{ url($prefix . "/statistics/agency-stats") }}', // ✅ matches your route
            type: 'GET',
            beforeSend: function() {
                $('#refreshStats').html('<i class="fa fa-spinner fa-spin"></i> {{ __("Loading...") }}');
            },
            success: function(data) {
                // ✅ update each stat dynamically
                $('[data-stat="agencyCount"]').text(data.agencyCount);
                $('[data-stat="agency_salaries"]').text(data.agency_salaries);
                $('[data-stat="user_salaries"]').text(data.user_salaries);
                $('[data-stat="activeAgencies"]').text(data.activeAgencies);
                $('[data-stat="newAgenciesToday"]').text(data.newAgenciesToday);
                $('[data-stat="newAgenciesMonth"]').text(data.newAgenciesMonth);
                $('[data-stat="avgAgencyWallet"]').text(data.avgAgencyWallet);
                $('[data-stat="totalMembers"]').text(data.totalMembers);
                $('[data-stat="avgMembersPerAgency"]').text(data.avgMembersPerAgency);
                $('[data-stat="pendingJoins"]').text(data.pendingJoins);
                $('[data-stat="diamondsAchieved"]').text(data.diamondsAchieved);

                $('#refreshStats').html('<i class="fa fa-refresh me-1"></i> {{ __("Refresh Stats") }}');
            },
            error: function() {
                alert('{{ __("Error loading stats") }}');
                $('#refreshStats').html('<i class="fa fa-refresh me-1"></i> {{ __("Refresh Stats") }}');
            }
        });
    }

    // Load stats once on page load
    updateStats();

    // Optional refresh button
    $('#refreshStats').on('click', function() {
        updateStats();
    });
});
</script>

