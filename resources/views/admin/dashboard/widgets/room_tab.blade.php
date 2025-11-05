
<style>
    .info-box:hover .info-box-more {
        background: rgba(0,0,0,0.3);
    }
</style>

<div id="roomStatsRow" class="row">
    <div class="col-md-3">
        <div class="info-box bg-blue" style="position: relative; overflow: hidden;">
            <span class="info-box-icon"><i class="fa fa-headphones"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Audio Rooms') }}</span>
                <span class="info-box-number" id="audioRooms">--</span>
            </div>

            <a href="{{ admin_url('rooms?online=1') }}"
           class="info-box-more text-white"
           style="
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                background: rgba(0,0,0,0.15);
                height: 35px;
                font-weight: 600;
                text-decoration: none;
                text-align: center;
                color: #fff;
                transition: background 0.2s ease;">
           {{ __('more') }} <i class="fa fa-arrow-circle-right me-1"></i>
        </a>
        </div>
    </div>

<div class="col-md-3">
    <div class="info-box bg-green" style="position: relative; overflow: hidden;">
        <span class="info-box-icon"><i class="fa fa-microphone"></i></span>
        <div class="info-box-content">
            <span class="info-box-text">{{ __('Live Rooms') }}</span>
            <span class="info-box-number" id="liveRooms">--</span>
        </div>

        <!-- More Button -->
        <a href="{{ admin_url('live-rooms?online=1') }}"
           class="info-box-more text-white"
           style="
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                background: rgba(0,0,0,0.15);
                height: 35px;
                font-weight: 600;
                text-decoration: none;
                text-align: center;
                color: #fff;
                transition: background 0.2s ease;">
           {{ __('more') }} <i class="fa fa-arrow-circle-right me-1"></i>
        </a>
    </div>
</div>

    <div class="col-md-3">
        <div class="info-box bg-purple" style="position: relative; overflow: hidden;">
            <span class="info-box-icon"><i class="fa fa-microphone"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Live Rooms (Active)') }}</span>
                <span class="info-box-number" id="activeRooms">--</span>
            </div>
            <a href="{{ admin_url('live-rooms?is_live=1') }}"
           class="info-box-more text-white"
           style="
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                background: rgba(0,0,0,0.15);
                height: 35px;
                font-weight: 600;
                text-decoration: none;
                text-align: center;
                color: #fff;
                transition: background 0.2s ease;">

           {{ __('more') }} <i class="fa fa-arrow-circle-right me-1"></i>
        </a>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box bg-red" style="position: relative; overflow: hidden;">
            <span class="info-box-icon"><i class="fa fa-microphone-slash"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Live Rooms (Inactive)') }}</span>
                <span class="info-box-number" id="inactiveRooms">--</span>
            </div>
            <a href="{{ admin_url('live-rooms?is_live=0') }}"
           class="info-box-more text-white"
           style="
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                background: rgba(0,0,0,0.15);
                height: 35px;
                font-weight: 600;
                text-decoration: none;
                text-align: center;
                color: #fff;
                transition: background 0.2s ease;">
           {{ __('more') }} <i class="fa fa-arrow-circle-right me-1"></i>
        </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        addToAjaxQueue(() => {
            return fetch(`{{ admin_url('statistics/room-stats') }}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('audioRooms').innerText = data.audio;
                    document.getElementById('liveRooms').innerText = data.live;
                    document.getElementById('activeRooms').innerText = data.active;
                    document.getElementById('inactiveRooms').innerText = data.inactive;
                })
                .catch(err => console.error('Error loading room stats:', err));
        });
    });
</script>

