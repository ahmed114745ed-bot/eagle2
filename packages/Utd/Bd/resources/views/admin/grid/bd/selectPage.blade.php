@php
    $bds = \Utd\Bd\Entities\Bd::all(); // جلب كل الـ BDs
    $defaultBd = \Utd\Bd\Entities\Bd::where('default', 1)->first(); // جلب BD الافتراضي الحالي
@endphp

<style>

     .rtl .bck-bt{
    width: 15%;
    margin: 15px auto;
    text-align: center;
    border-radius: 12px;
    border: 2px;
    background-color: var(--primary-color) !important;
    top: -73px;
    position: relative;
    left: -43%;
}

.ltr .bck-bt{
    width: 15%;
    margin: 15px auto;
    text-align: center;
    border-radius: 12px;
    border: 2px;
    background-color: var(--primary-color) !important;
    top: -73px;
    position: relative;
    right: -43%;
}
</style>
 <!-- *** ***************************************************************************************************** -->

 <style>
.switch {
  position: relative;
  display: inline-block;
  width: 80px;
  height: 34px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}

.slider:before {
  position: absolute;
  content: attr(data-label-off);
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
  line-height: 26px;
  text-align: center;
  font-size: 12px;
  font-weight: bold;
  color: #000;
}

input:checked + .slider {
  background-color: #28a745;
}

input:checked + .slider:before {
  transform: translateX(46px);
  content: attr(data-label-on);
  color: #28a745;
}
</style>
<div style="width:50%; margin:20px auto; text-align:center;">
    <label for="salaryTransferSwitch" style="margin-bottom:10px; display:block;">
        {{ __('salary_transfer_label') }}
    </label>

    <label class="switch">
        <input type="checkbox" id="salaryTransferSwitch"
               {{ $transfer_salary ? 'checked' : '' }}>
        <span class="slider" data-label-on="{{ __('Yes') }}" data-label-off="{{ __('No') }}"></span>
    </label>
</div>

<script>
document.getElementById('salaryTransferSwitch').addEventListener('change', function () {
    let self = this;
    let checked = self.checked;

    fetch("{{ route('admin.bd.toggle-salary-transfer') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ enabled: checked ? 1 : 0 })
    })
    .then(res => res.json())
    .then(data => {
        console.log("تم تحديث حالة تحويل الرواتب:", data.message);
    })
    .catch(() => {
        alert("حدث خطأ، حاول مرة أخرى");
        self.checked = !checked; // لو حصل خطأ نرجع الحالة القديمة
    });
});
</script>





 <!-- *** ***************************************************************************************************** -->
{{--<div style="" class="bck-bt">--}}
{{--    <a href="{{ route('admin.usersBd.index') }}" class="btn btn-secondary mt-3">{{ __('back') }}</a>--}}
{{--</div>--}}
{{--<div style="width: 50%; margin: 20px auto; padding: 10px;  border-radius: 10px;">--}}
{{-- --}}
{{--<p>--}}
{{--&#9432;  --}}
{{--        <!-- <strong> -->--}}
{{--       --}}
{{--            {{ __('default_current') }}:--}}
{{--            <span title="{{ __('default_current_info') }}" style="cursor: help; color: #007bff;">--}}
{{--              --}}
{{--            </span>--}}
{{--        <!-- </strong> -->--}}
{{--       --}}
{{--    </p>--}}
{{--</div>--}}

{{--<form action="{{ route('admin.make-bd-default') }}" method="POST" style="--}}
{{--    padding: 20px 86px;--}}
{{--    border: 2px;--}}
{{--    border-radius: 30px;--}}
{{--    width: 50%;--}}
{{--    margin: 10px auto 88px;--}}
{{--    ">--}}
{{--    @csrf--}}
{{--    <div class="form-group">--}}
{{--        <label for="bd_id">{{ __('select_default_bd') }}:</label>--}}
{{--        <select name="bd_id" id="bd_id" class="form-control" required>--}}
{{--            @foreach($bds as $bd)--}}
{{--                <option value="{{ $bd->id }}" {{ ($defaultBd && $bd->id == $defaultBd->id) ? 'selected' : '' }}>--}}
{{--                    {{ $bd->name }} - {{ $bd->username }}--}}
{{--                </option>--}}
{{--            @endforeach--}}
{{--        </select>--}}
{{--    </div>--}}
{{--    <button type="submit" class="btn btn-primary mt-2">{{ __('set_as_default') }}</button>--}}
{{--</form>--}}

