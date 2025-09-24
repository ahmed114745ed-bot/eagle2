
<div class="d-flex justify-content-center align-items-center" style="min-height:80vh;">
    <div class="card shadow-lg" style="width:400px; border-radius:15px;">
        <div class="card-header text-center bg-primary text-white" style="border-top-left-radius:15px; border-top-right-radius:15px;">
        <h4 class="mb-0">{{ __('Room Cup Settings') }}</h4>
        </div>
        <div class="card-body p-4">

        <div class="card-body p-4">

        <form method="POST" action="{{ admin_url('room-cup-settings/save') }}">
            @csrf

            <!-- Toggle Switch Group -->
            <div class="mb-4 d-flex justify-content-between align-items-center flex-row-reverse">
            <span id="switch-text" class="fw-bold me-3">{{ $settings['enabled'] ? __('ON') : __('OFF') }}</span>
            <div class="d-flex align-items-center">
            <label class="fw-bold mb-0 me-3" for="enabled">{{ __('Enable Room Cup Feature') }}</label>
                 <label class="switch mb-0">
                        <input type="checkbox" name="enabled" id="enabled" {{ $settings['enabled'] ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <!-- Interval Input Group -->
            <div class="mb-4 inp">
                <label for="interval_minutes" class="form-label fw-bold d-block text-end">{{ __('Interval (minutes)') }}</label>
                   <div class="input-group justify-content-end  inp-div">
                    <input type="number" class="form-control text-end" name="interval_minutes" id="interval_minutes" min="1" value="{{ $settings['interval_minutes'] }}" style=";">
                    <span class="input-group-text bg-light"><i class="bi bi-clock-fill"></i></span>
                </div>
            </div>

            <!-- Save Button -->
            <div class="d-flex justify-content-end">
               <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm btn-form">{{ __('Save') }}</button>
            </div>

        </form>

        </div>
    </div>
</div>


<style>

    .inp-div{
        width: 68%;
        margin: auto;
    }
    .inp{
        width: 100%;
        top: 13px;
        position: relative;
    }

    form{
        height: 243px;
        padding: 10px;
        border-radius: 30px;

    }
    .btn-form{
        bottom: -33px;
        position: relative;
    }
.switch {
  position: relative;
  display: inline-block;
  width: 70px;
  height: 38px;
}

.switch input { display: none; }

.slider {
  position: absolute;
  cursor: pointer;
  top:0;
  left:0;
  right:0;
  bottom:0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 38px;
}

.slider:before {
  position: absolute;
  content: "";
  height:30px;
  width:30px;
  left:4px;
  bottom:4px;
  background-color:white;
  transition:0.4s;
  border-radius:50%;
}

input:checked + .slider {
  background-color: #28a745;
}

input:checked + .slider:before {
  transform: translateX(32px);
}

.slider.round { border-radius: 38px; }
.card {
    margin: auto;
    width: 445px;
    border-radius: 15px;
    height: 326px;
}
</style>

<script>
const toggle = document.getElementById('enabled');
const text = document.getElementById('switch-text');

function updateSwitch() {
    text.innerText = toggle.checked ? 'ON' : 'OFF';
    text.style.color = toggle.checked ? 'green' : 'red';
}

toggle.addEventListener('change', updateSwitch);
updateSwitch();
</script>

