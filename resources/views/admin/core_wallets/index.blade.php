

<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->

<style>
    .modal-backdrop {
        z-index: 1040 !important;
    }
    .modal {
        z-index: 1050 !important;
    }
</style>

<div class="container mt-4">
    <div class="row justify-content-center g-4 wallet_div">
        @foreach ($coreWallets as $wallet)
            @php
                $icon = $icons[$wallet->name] ?? 'fa-solid fa-wallet';
            @endphp
            <div class="col-md-5 col-lg-5 mb-4 px-3 wallet_posation">
                <div class="card shadow-lg position-relative border-0"
                    style="padding-top: 39px; border-radius: 15px; overflow: hidden; background: linear-gradient(135deg,rgb(211, 211, 183),rgb(202, 211, 193)); transition: transform 0.3s ease-in-out; margin-bottom: 20px;">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="{{ $icon }} text-primary" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-2" style="color: #000000;">
                            {{ ucfirst(str_replace('_', ' ', $wallet->name)) }}
                        </h5>
                        <p class="fs-5 fw-semibold" style="color: #000000;">
                            {{ __('Coins') }} : {{ number_format($wallet->coins) }}
                        </p>

                        <button type="button"
                                class="btn btn-primary btn-sm mt-2"
                                data-toggle="modal"
                                data-target="#transferModal"
                                onclick="prepareTransferModal({{ $wallet->id }}, '{{ ucfirst(str_replace('_', ' ', $wallet->name)) }}')">
                            <i class="fas fa-arrow-right-arrow-left"></i> {{ __('Transfer') }}
                        </button>
                    </div>

                    <div class="position-absolute bottom-0 start-0 p-6 m-5">
                        <h5 class="text-muted" style="padding-right: 5px;">
                            {{ $wallet->update_for_human }}
                        </h5>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- ✅ المودال -->
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="transferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="walletTransferForm" method="POST" action="{{ route('admin.wallet.transfer.submit') }}">
            @csrf
            <input type="hidden" name="from_wallet_id" id="from_wallet_id">
            <div class="modal-content" style="background: white; color: black; padding: 20px;">
                <div class="modal-header">
                    <h5 class="modal-title" id="transferModalLabel">{{__('transfer from')}}  <span id="walletName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="to_wallet_id" class="form-label">{{__('to wallet')}} </label>
                        <select class="form-control" name="to_wallet_id" id="to_wallet_id" required>
                            @foreach ($coreWallets as $wallet)
                                <option value="{{ $wallet->id }}">
                                    {{ ucfirst(str_replace('_', ' ', $wallet->name)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">{{__('amount')}}</label>
                        <input type="number" name="amount" class="form-control" min="1" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"> {{__('execute transfer')}}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('cancel')}}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 99999;"></div>

<script>
    function showToast(message, type = 'success') {
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `alert alert-${type}`;
        toast.innerText = message;
        toast.style.marginBottom = '10px';
        document.getElementById('toast-container').appendChild(toast);

        setTimeout(() => {
            document.getElementById(toastId)?.remove();
        }, 3000);
    }

    async function submitWalletTransfer(event) {
        event.preventDefault(); // لا تُرسل النموذج تقليديًا

        const form = document.getElementById('walletTransferForm');
        const from_wallet_id = document.getElementById('from_wallet_id').value;
        const to_wallet_id = document.getElementById('to_wallet_id').value;
        const amount = form.amount.value;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    from_wallet_id,
                    to_wallet_id,
                    amount
                })
            });

            const data = await response.json();

            if (response.ok && data.status === 1) {
                showToast(data.message || 'تم التحويل بنجاح ✅', 'success');
                $('#transferModal').modal('hide'); // أغلق المودال
                form.reset(); // نظف النموذج
                 setTimeout(() => {
                location.reload();
            }, 1000); // wait 1 second before reloading (optional)
            } else {
                showToast(data.message || 'فشل في التحويل ❌', 'danger');
            }
        } catch (error) {
            console.error(error);
            showToast('حدث خطأ في الاتصال بالخادم ❌', 'danger');
        }
    }

    document.getElementById('walletTransferForm').addEventListener('submit', submitWalletTransfer);
</script>

<script>
    function prepareTransferModal(fromId, fromName) {
        document.getElementById('from_wallet_id').value = fromId;
        document.getElementById('walletName').innerText = fromName;

        const select = document.getElementById('to_wallet_id');
        for (let option of select.options) {
            option.style.display = option.value == fromId.toString() ? 'none' : 'block';
        }
    }
</script>

