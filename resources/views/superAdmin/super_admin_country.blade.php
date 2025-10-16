<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    @php
        $countryName = app()->getLocale() == 'ar' ? $country->name : $country->e_name;
    @endphp
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} – {{ $countryName }}</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            background: #0a0e27;
        }

        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: linear-gradient(270deg, #0a0e27, #1a1f4e, #2d1b69, #6b2d91, #ff006e, #ff4500, #ffd700);
            background-size: 1400% 1400%;
            animation: gradientWave 20s ease infinite;
        }

        @keyframes gradientWave {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 14, 39, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loader {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(255, 215, 0, 0.3);
            border-top-color: #ffd700;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .hidden {
            display: none !important;
        }

        .language-switcher {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 5px;
            border-radius: 50px;
            border: 2px solid rgba(255, 215, 0, 0.5);
        }

        .lang-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 50px;
            background: transparent;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
        }

        .lang-btn.active {
            background: linear-gradient(45deg, #ffd700, #ff6b6b);
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
        }

        .page-title {
            text-align: center;
            font-size: 32px;
            font-weight: 900;
            color: #ffd700;
            margin-top: 40px;
            margin-bottom: 40px;
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .country-flag {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .country-name {
            font-size: 36px;
            font-weight: 900;
            color: #ffd700;
        }

        .super-admin-card {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(255, 107, 107, 0.2));
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 25px;
            margin-bottom: 30px;
            border: 3px solid #ffd700;
            cursor: pointer;
        }

        .admin-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .admin-avatar {
            width: 90px;
            height: 90px;
            border-radius: 20px;
            border: 4px solid #ffd700;
            margin-left: 20px;
        }

        .admin-info h3 {
            font-size: 24px;
            color: #ffd700;
            margin-bottom: 5px;
        }

        .admin-info p {
            color: #fff;
            font-size: 18px;
        }

        .admin-levels {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .level-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 20px;
            text-align: center;
            border: 2px solid rgba(255, 215, 0, 0.3);
        }

        .level-value {
            font-size: 28px;
            font-weight: bold;
            color: #ffd700;
        }

        .stats-section {
            background: linear-gradient(135deg, rgba(78, 205, 196, 0.1), rgba(255, 107, 107, 0.1));
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 25px;
            margin-bottom: 30px;
            border: 2px solid rgba(78, 205, 196, 0.3);
        }

        .stats-title {
            font-size: 22px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            color: #fff;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 20px;
            text-align: center;
            border: 2px solid rgba(255, 215, 0, 0.3);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 900;
            color: #4ecdc4;
        }

        .top-section {
            margin-bottom: 35px;
        }

        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(255, 107, 107, 0.2));
            backdrop-filter: blur(20px);
            border-radius: 20px;
            text-align: center;
            color: #fff;
            font-weight: bold;
            border: 2px solid rgba(255, 215, 0, 0.4);
        }

        .top-list {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .top-item {
            flex: 1;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 20px 15px;
            text-align: center;
            position: relative;
            border: 2px solid rgba(255, 215, 0, 0.3);
        }

        .top-rank {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #ffd700, #ff6b6b);
            color: #000;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 18px;
        }

        .top-avatar {
            width: 70px;
            height: 70px;
            margin: 15px auto;
            border-radius: 15px;
            border: 3px solid #ffd700;
            background-size: cover;
            background-position: center;
        }

        .top-name {
            font-size: 14px;
            margin-top: 10px;
            font-weight: bold;
            color: #fff;
        }

        .top-value {
            font-size: 12px;
            color: #ffd700;
            margin-top: 5px;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .container { padding: 15px; }
            .country-name { font-size: 28px; }
            .admin-levels { grid-template-columns: 1fr; }
            .top-list { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="animated-bg"></div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loader"></div>
</div>

<h1 class="page-title">
    {{ config('app.name') }} – {{ $countryName }}
</h1>

<!-- Language Switcher -->
<div class="language-switcher">
    @php
        $languages = \App\Models\Language::where('is_enabled', 1)->pluck('name', 'code');
    @endphp

    @foreach($languages as $key => $language)
        <button type="button" class="language lang-btn {{ app()->getLocale() === $key ? 'active' : '' }}" data-id="{{ $key }}">
            {{ $language }}
        </button>
    @endforeach
</div>

<div class="container" id="mainContent">
    <div class="header">
        <div class="country-flag">{{ $country->iso }}</div>
        <h1 class="country-name">{{ $countryName }}</h1>
    </div>

    <!-- Super Admin Card -->
    <div class="super-admin-card" id="superAdminCard" onclick="sendMessage(0)">
        <div class="admin-header">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='90' height='90'%3E%3Cdefs%3E%3ClinearGradient id='g'%3E%3Cstop offset='0' stop-color='%23FFD700'/%3E%3Cstop offset='1' stop-color='%23FF6B6B'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='90' height='90' fill='url(%23g)'/%3E%3C/svg%3E" alt="Super Admin" class="admin-avatar"/>
            <div class="admin-info">
                <h3>🌟 {{ __('Super Admin') }} 🌟</h3>
                <p id="adminName">...</p>
                <p id="adminId">...</p>
            </div>
        </div>
        <div class="admin-levels">
            <div class="level-item">
                <div style="color: #fff; margin-bottom: 10px;">{{ __('Sending Level') }}</div>
                <div class="level-value">75</div>
            </div>
            <div class="level-item">
                <div style="color: #fff; margin-bottom: 10px;">{{ __('Receiving Level') }}</div>
                <div class="level-value">82</div>
            </div>
            <div class="level-item">
                <div style="color: #fff; margin-bottom: 10px;">{{ __('Recharge Level') }}</div>
                <div class="level-value">90</div>
            </div>
        </div>
    </div>

    <!-- Online Users -->
    <div class="stats-section">
        <h2 class="stats-title">🎮 {{ __('Active Users') }} 🎮</h2>
        <div style="display:flex;justify-content:center;">
            <div class="stat-card">
                <span class="stat-number" id="onlineUsers">0</span>
            </div>
        </div>
    </div>

    <!-- Top Rooms -->
    <div class="top-section">
        <h3 class="section-title">🏆 {{ __('Top 3 Entertainment Rooms') }} 🎉</h3>
        <div class="top-list" id="topRooms"></div>
    </div>

    <!-- Top Senders -->
    <div class="top-section">
        <h3 class="section-title">💎 {{ __('Top 3 Generous Supporters') }} 💰</h3>
        <div class="top-list" id="topSenders"></div>
    </div>

    <!-- Top Receivers -->
    <div class="top-section">
        <h3 class="section-title">🎤 {{ __('Top 3 Star Hosts') }} ⭐</h3>
        <div class="top-list" id="topReceivers"></div>
    </div>

    <!-- Top Agencies -->
    <div class="top-section">
        <h3 class="section-title">🏢 {{ __('Top 3 Host Agencies') }} 🚀</h3>
        <div class="top-list" id="topAgencies"></div>
    </div>

    <!-- Top Charge Agencies -->
    <div class="top-section">
        <h3 class="section-title">💰 {{ __('Top 3 Recharge Agencies') }} 💵</h3>
        <div class="top-list" id="topChargeAgencies"></div>
    </div>

    <!-- Top BDs -->
    <div class="top-section">
        <h3 class="section-title">👥 {{ __('Top 3 Most Active BD') }} 🎯</h3>
        <div class="top-list" id="topBds"></div>
    </div>

    <!-- Top Gamers -->
    <div class="top-section">
        <h3 class="section-title">🎮 {{ __('Top 3 Gamers') }} 🏅</h3>
        <div class="top-list" id="topGamers"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const countryId = {{ $country->id }};
    const defaultAvatar = "{{ asset('images/businessman-icon.jpg') }}";
    const defaultRoomCover = "{{ asset('images/background_room.jpg') }}";
    const defaultAgencyImg = "{{ asset('images/icon-agency.jpg') }}";

    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    // Language Switcher
    $(".language").click(function() {
        let id = $(this).data('id');
        $.post("{{ url('/locale') }}", { locale: id }, () => location.reload());
    });

    // Fetch Stats via AJAX
    function fetchStats() {
        $('#loadingOverlay').removeClass('hidden');

        $.ajax({
            url: `/api/country/${countryId}/stats`,
            method: 'GET',
            success: function(data) {
                renderStats(data);
                $('#loadingOverlay').addClass('hidden');
            },
            error: function(xhr) {
                console.error('Error fetching stats:', xhr);
                $('#loadingOverlay').addClass('hidden');
                alert('{{ __("Failed to load data. Please refresh the page.") }}');
            }
        });
    }

    function renderStats(data) {
        // Super Admin
        if (data.superAdmin) {
            $('#adminName').text(data.superAdmin.name || '---');
            $('#adminId').text('ID: ' + (data.superAdmin.id || '---'));
            $('#superAdminCard').attr('onclick', `sendMessage(${data.superAdmin.user?.id || 303})`);
        }

        // Online Users
        $('#onlineUsers').text(data.onlineUsers.toLocaleString());

        // Top Rooms
        renderTopRooms(data.topRooms);

        // Top Senders
        renderTopSenders(data.topSenders);

        // Top Receivers
        renderTopReceivers(data.topReceivers);

        // Top Agencies
        renderTopAgencies(data.topAgencies);

        // Top Charge Agencies
        renderTopChargeAgencies(data.topChargeAgencies);

        // Top BDs
        renderTopBds(data.topBds);

        // Top Gamers
        renderTopGamers(data.topGamers);
    }

    function renderTopRooms(rooms) {
        const html = rooms.map((room, index) => `
                <div class="top-item">
                    <span class="top-rank">${index + 1}</span>
                    <div class="top-avatar" style="background-image:url('${getImagePath(room.room_cover) || defaultRoomCover}');"></div>
                    <div class="top-name">${room.room_name || '---'}</div>
                    <div class="top-value">${(room.room_visitors_count || 0).toLocaleString()} {{ __('members') }}</div>
                </div>
            `).join('');
        $('#topRooms').html(html);
    }

    function renderTopSenders(senders) {
        const html = senders.map((sender, index) => `
                <div class="top-item">
                    <span class="top-rank">${index + 1}</span>
                    <div class="top-avatar" style="background-image:url('${getImagePath(sender.sender?.profile?.avatar) || defaultAvatar}');"></div>
                    <div class="top-name">${sender.sender?.name || '---'}</div>
                    <div class="top-value">${((sender.total_sent || 0) / 1000).toFixed(1)}K 💎</div>
                </div>
            `).join('');
        $('#topSenders').html(html);
    }

    function renderTopReceivers(receivers) {
        const html = receivers.map((receiver, index) => `
                <div class="top-item">
                    <span class="top-rank">${index + 1}</span>
                    <div class="top-avatar" style="background-image:url('${getImagePath(receiver.receiver?.profile?.avatar) || defaultAvatar}');"></div>
                    <div class="top-name">${receiver.receiver?.name || '---'}</div>
                    <div class="top-value">${((receiver.total_sent || 0) / 1000).toFixed(1)}K 💎</div>
                </div>
            `).join('');
        $('#topReceivers').html(html);
    }

    function renderTopAgencies(agencies) {
        const html = agencies.map((agency, index) => `
                <div class="top-item">
                    <span class="top-rank">${index + 1}</span>
                    <div class="top-avatar" style="background-image:url('${getImagePath(agency.img) || defaultAgencyImg}');"></div>
                    <div class="top-name">${agency.name || '---'}</div>
                    <div class="top-value">${(agency.members_count || 0).toLocaleString()} {{ __('hosts') }}</div>
                </div>
            `).join('');
        $('#topAgencies').html(html);
    }

    function renderTopChargeAgencies(charges) {
        const html = charges.map((charge, index) => {
            const agency = charge.sender_shipping_agency;
            return `
                    <div class="top-item">
                        <span class="top-rank">${index + 1}</span>
                        <div class="top-avatar" style="background-image:url('${getImagePath(agency?.img) || defaultAgencyImg}');"></div>
                        <div class="top-name">${agency?.name || '---'}</div>
                        <div class="top-value">${(charge.amount || 0).toFixed(2)} 💵</div>
                    </div>
                `;
        }).join('');
        $('#topChargeAgencies').html(html);
    }

    function renderTopBds(bds) {
        const html = bds.map((bd, index) => `
                <div class="top-item">
                    <span class="top-rank">${index + 1}</span>
                    <div class="top-name">${bd.name || '---'}</div>
                    <div class="top-value">${(bd.total_members || 0).toLocaleString()} {{ __('agency') }}</div>
                </div>
            `).join('');
        $('#topBds').html(html);
    }

    function renderTopGamers(gamers) {
        const html = gamers.map((gamer, index) => `
                <div class="top-item">
                    <span class="top-rank">${index + 1}</span>
                    <div class="top-avatar" style="background-image:url('${getImagePath(gamer.user?.profile?.avatar) || defaultAvatar}');"></div>
                    <div class="top-name">${gamer.user?.name || '---'}</div>
                    <div class="top-value">${((gamer.coins || 0) / 1000).toFixed(1)}K ⚡</div>
                </div>
            `).join('');
        $('#topGamers').html(html);
    }

    function getImagePath(path) {
        if (!path) return null;
        if (path.startsWith('http')) return path;
        return `/storage/${path}`;
    }

    function sendMessage(userId) {
        const message = `open_profile:${userId}`;
        window.postMessage(message, '*');
        console.log("✅ Sent message to Flutter:", message);
    }

    // Load stats on page load
    $(document).ready(function() {
        fetchStats();

        // Auto-refresh every 5 minutes
        setInterval(fetchStats, 300000);
    });
</script>
</body>
</html>
