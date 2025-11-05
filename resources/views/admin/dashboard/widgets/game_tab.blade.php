
<div class="row g-3">
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-gamepad"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('App Profit') }}</span>
                <span class="info-box-number" id="appProfit">0</span>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $.ajax({
            url: '{{ url("admin/statistics/game-summary") }}',
            type: 'GET',
            success: function(data) {
                $('#appProfit').text(data.app_profit);
            },
            error: function() {
                $('#appProfit').text('Error');
            }
        });
    });
</script>
