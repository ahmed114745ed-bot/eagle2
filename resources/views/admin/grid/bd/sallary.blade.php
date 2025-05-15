@php
    $translated = trans('salary.current_balance');
@endphp
<style>
    .transferModal{
        width: 600px;
        height: 294px;
        background-color;:var(--box-background-color)
    }
    .transferForm{
        height: 100%;
        padding: 70px 9px 14px 16px;
    }
    .transferForm .form-group{
        width: 70%;
        margin: auto;
    }
    .transferForm .form-group .amount-input
    {
        margin: 20px 0px 4px 3px;

    }

    .transferForm .actions{
        margin: 66px 79px 4px 3px;

    }

</style>
<div class="card bg-successs" style="padding: 20px; font-size: 20px; text-align: center; width: 469px; margin: 42px auto; height: auto;">
    <strong style="color: white;">{{ $translated }}: </strong>
    <span style="color: white;">{{ $finalSalary }} 💰</span>

    <div style="margin-top: 15px;">
        <button onclick="openTransferModal()" class="btn btn-light btn-sm">
            {{ __('salary.transfer_to_wallet') }}
        </button>
    </div>
</div>

{{-- Modal --}}
<div id="transferModal" class="transferModal" style="display: none; position: fixed; top: 30%; left: 50%; transform: translate(-50%, -30%);
    background: white; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,.5); z-index: 9999;">
    <form id="transferForm" class="transferForm" method="POST" action="{{ route('admin.bd.salary.transfer') }}">
        @csrf
        <input type="hidden" name="bd_id" value="{{ Auth::id() }}">

        <div class="form-group">
            <labe l for="amount">{{ __('salary.enter_amount') }}</label>
            <input type="number" name="amount" id="amount" class="form-control amount-input" required max="{{ $finalSalary }}" step="0.01">
        </div>

        <div class="text-right mt-3 actions">
            <button type="submit" class="btn btn-success">{{ __('salary.confirm_transfer') }}</button>
            <button type="button" class="btn btn-secondary" onclick="closeTransferModal()">{{ __('salary.cancel') }}</button>
        </div>
    </form>
</div>

{{-- Overlay --}}
<div id="modalOverlay" onclick="closeTransferModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100%;
    height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;"></div>

{{-- JS --}}
<script>
    function openTransferModal() {
        document.getElementById('transferModal').style.display = 'block';
        document.getElementById('modalOverlay').style.display = 'block';
    }

    function closeTransferModal() {
        document.getElementById('transferModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
    }
</script>
