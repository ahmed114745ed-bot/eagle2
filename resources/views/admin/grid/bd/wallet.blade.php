@php
    $translated = trans('salary.current_balance');
@endphp
<style>
    .wallet-card {
        padding: 20px;
        /* background-color: #007bff; */
        color: white;
        font-size: 20px;
        text-align: center;
        width: 100%;
        max-width: 500px;
        margin: 40px auto;
        border-radius: 10px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        position: relative;
    }

    .wallet-card-actions {
        margin-top: 15px;
    }

    .wallet-button {
        padding: 8px 16px;
        background-color: white;
        color: #007bff;
        border: none;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }

    .wallet-button:hover {
        background-color: #e2e6ea;
    }

    .wallet-modal {
        display: none;
        position: fixed;
        top: 15%;
        left: 50%;
        transform: translate(-50%, 0);
        background: white;
        padding: 30px;
        border-radius: 12px;
        z-index: 9999;
        width: 90%;
        max-width: 450px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .wallet-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 9998;
    }

    .wallet-modal-content .form-group {
        margin-bottom: 15px;
    }

    .wallet-modal-content input,
    .wallet-modal-content select {
        width: 100%;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    .wallet-modal-actions {
        text-align: right;
        margin-top: 20px;
    }

    .wallet-modal-actions .btn {
        margin-left: 10px;
        padding: 8px 14px;
        border-radius: 6px;
    }

    .transferModal{
        width: 600px;
        height: 332px;
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
        margin: 42px 73px 4px 3px;


    }
    .transferForm .actions button{
        margin-left: 4px;
    }

</style>
<div class="card bg-primary" style="padding: 20px; color: ; font-size: 20px; text-align: center; width: 500px; margin: 42px auto;">
    <strong>{{ $translated }}: </strong> {{ $finalSalary }} 💰

    <div style="margin-top: 15px;">
        <button onclick="openChargeModal()" class="btn btn-light btn-sm">
            {{ __('wallet.charge_wallet') }}
        </button>
    </div>
</div>

{{-- Charge Modal --}}
<div id="chargeModal" class="transferModal" style="display: none; position: fixed; top: 20%; left: 50%; transform: translate(-50%, -20%);
    background: white; padding: ; border-radius: 10px; z-index: 9999; width: 520px;">
    <form id="chargeForm" class="transferForm" method="POST" action="{{ route('admin.bd.wallet.charge') }}">
        @csrf

        <div class="form-group">
            <label for="target_type">{{ __('wallet.select_target') }}</label>
            <select id="target_type" name="target_type" class="form-control" required onchange="toggleTargetFields()">
                <option value="">{{ __('wallet.choose') }}</option>
                <option value="user">{{ __('wallet.user') }}</option>
                <option value="agency">{{ __('wallet.agency') }}</option>
            </select>
        </div>

        <div id="target_fields" style="display: none;">
            <div class="form-group">
                <label for="target_id">{{ __('wallet.enter_id') }}</label>
                <input type="number" name="target_id" id="target_id" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="amount">{{ __('wallet.enter_amount') }}</label>
                <input type="number" name="amount" id="amount" class="form-control" required step="0.01" min="0.01">
            </div>

            <div class="text-right mt-3 actions">
                <button type="submit" class="btn btn-success">{{ __('wallet.confirm_charge') }}</button>
                <button type="button" class="btn btn-secondary" onclick="closeChargeModal()">{{ __('wallet.cancel') }}</button>
            </div>
        </div>
    </form>
</div>

{{-- Overlay --}}
<div id="chargeOverlay" onclick="closeChargeModal()" style="display: none; position: fixed; top: 0; left: 0;
    width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998;"></div>

{{-- JS --}}
<script>
    function openChargeModal() {
        document.getElementById('chargeModal').style.display = 'block';
        document.getElementById('chargeOverlay').style.display = 'block';
    }

    function closeChargeModal() {
        document.getElementById('chargeModal').style.display = 'none';
        document.getElementById('chargeOverlay').style.display = 'none';
    }

    function toggleTargetFields() {
        const type = document.getElementById('target_type').value;
        const fields = document.getElementById('target_fields');
        fields.style.display = type ? 'block' : 'none';
    }
</script>
