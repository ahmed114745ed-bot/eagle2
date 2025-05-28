@php
    $bds = \App\Models\Bd::all(); // جلب كل الـ BDs
    $defaultBd = \App\Models\Bd::where('default', 1)->first(); // جلب BD الافتراضي الحالي
@endphp

<style>

     .rtl .bck-bt{
    width: 8%;
    margin: 20px auto;
    text-align: center;
    border-radius: 36px;
    border: 2px;
    background-color: var(--primary-color) !important;
    top: -73px;
    position: relative;
    left: -43%;
}

.ltr .bck-bt{
    width: 8%;
    margin: 20px auto;
    text-align: center;
    border-radius: 36px;
    border: 2px;
    background-color: var(--primary-color) !important;
    top: -73px;
    position: relative;
    right: -43%;
}
</style>
<div style="" class="bck-bt">
    <a href="{{ route('admin.usersBd.index') }}" class="btn btn-secondary mt-3">{{ __('back') }}</a>
</div>
<div style="width: 50%; margin: 20px auto; padding: 10px;  border-radius: 10px;">
 
<p>
&#9432;  
        <!-- <strong> -->
       
            {{ __('default_current') }}:
            <span title="{{ __('default_current_info') }}" style="cursor: help; color: #007bff;">
              
            </span>
        <!-- </strong> -->
       
    </p>
</div>

<form action="{{ route('admin.make-bd-default') }}" method="POST" style="
    padding: 20px 86px;
    border: 2px;
    border-radius: 30px;
    width: 50%;
    margin: 10px auto 88px;
    ">
    @csrf
    <div class="form-group">
        <label for="bd_id">{{ __('select_default_bd') }}:</label>
        <select name="bd_id" id="bd_id" class="form-control" required>
            @foreach($bds as $bd)
                <option value="{{ $bd->id }}" {{ ($defaultBd && $bd->id == $defaultBd->id) ? 'selected' : '' }}>
                    {{ $bd->name }} - {{ $bd->username }}
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary mt-2">{{ __('set_as_default') }}</button>
</form>


