<style>
.switch-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 20px;
}

.switch-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.switch-label {
    font-size: 16px;
    font-weight: bold;
}

.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #28a745;
}

input:checked + .slider:before {
    transform: translateX(24px);
}
</style>

<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ __('CP Settings') }}</h3>
    </div>

    <div class="box-body no-padding">
        <div class="switch-container">

            <div class="switch-item" style=" width: 50%;">
                <label for="enableAllGiftsCP" class="switch-label">
                    {{ __('Enable all CP gifts') }}
                </label>

                <label class="switch">
                    <input type="checkbox" id="enableAllGiftsCP"
                           {{ $enableGifts == 1 ? 'checked' : '' }}>
                    <span class="slider round"></span>
                </label>
            </div>

        </div>
    </div>
</div>
<script>
$(document).ready(function () {

    $('#enableAllGiftsCP').on('change', function() {
        var status = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: '{{ admin_url("cp-settings/update") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                cp_enable_all_gifts: status
            },
            success: function(response) {
                toastr.success("{{ __('Updated successfully!') }}");
            },
            error: function() {
                toastr.error("{{ __('Something went wrong!') }}");
            }
        });
    });

});
</script>
