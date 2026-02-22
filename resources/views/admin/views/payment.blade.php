<form id="paymentForm" onsubmit="event.preventDefault(); return extracted()">
    @csrf
    <div class="form-group">
        <label for="amount">{{ __('payment.amount') }}</label>
        <input type="number" class="form-control" id="amount" name="amount" required>
    </div>
    <div class="form-group">
        <label for="type">{{ __('payment.type') }}</label>
        <select name="type" id="type" class="form-control">
            <option value="game_type">{{ __('payment.game_type') }}</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="link_type">{{ __('payment.link_type') }}</label>
        <select name="link_type" id="link_type" class="form-control" required>
            <option value="">{{ __('payment.select_link_type') }}</option>
            <option value="fawry">{{ __('payment.fawry') }}</option>
            <option value="paymob">{{ __('payment.paymob') }}</option>
        </select>
    </div>

    <button type="submit" id="submitButton" class="btn btn-primary">{{ __('payment.save') }}</button>
</form>

<!-- Placeholder for the success message or payment URL -->
<div id="responseMessage"></div>

<script>
function extracted() {
    const form = document.getElementById('paymentForm');
    const submitButton = document.getElementById('submitButton');
    const responseMessage = document.getElementById('responseMessage');
    
    // Validate required fields before submission
    const linkType = document.getElementById('link_type').value;
    const amount = document.getElementById('amount').value;
    
    if (!linkType) {
        responseMessage.innerHTML = `
            <div class="alert alert-danger">
                <strong>{{ __("payment.error") }}!</strong> {{ __("payment.select_link_type") }}
            </div>
        `;
        return false;
    }
    
    if (!amount) {
        responseMessage.innerHTML = `
            <div class="alert alert-danger">
                <strong>{{ __("payment.error") }}!</strong> {{ __("payment.amount") }} {{ __("payment.required") }}
            </div>
        `;
        return false;
    }
    
    // Get form data
    const formData = new FormData(form);
    
    // Explicitly add the link_type to ensure it's sent
    formData.set('link_type', linkType);
    formData.set('amount', amount);
    formData.set('type', document.getElementById('type').value);
    
    // Disable submit button to prevent double submission
    submitButton.disabled = true;
    submitButton.textContent = '{{ __("payment.saving") }}';
    
    // Clear previous messages
    responseMessage.innerHTML = '';
    
    // Send AJAX request
    fetch('/admin/save-payment-with-method', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json',
        }
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            // If it's a validation error (422), return the response for further processing
            if (response.status === 422) {
                return response.json().then(data => {
                    throw new Error(JSON.stringify(data));
                });
            }
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            responseMessage.innerHTML = `
                <div class="alert alert-success">
                    <strong>{{ __("payment.success") }}!</strong> ${data.message}
                    ${data.payment_url ? `<br><a href="${data.payment_url}" target="_blank" class="btn btn-primary mt-2">{{ __("payment.open_payment_link") }}</a>` : ''}
                </div>
            `;
            // Reset form after successful submission
            form.reset();
        } else {
            responseMessage.innerHTML = `
                <div class="alert alert-danger">
                    <strong>{{ __("payment.error") }}!</strong> ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        let errorMessage = '{{ __("payment.network_error") }}';
        
        try {
            // Try to parse validation errors
            const errorData = JSON.parse(error.message);
            if (errorData.errors) {
                const errors = Object.values(errorData.errors).flat();
                errorMessage = errors.join('<br>');
            } else if (errorData.message) {
                errorMessage = errorData.message;
            }
        } catch (parseError) {
            // If parsing fails, check for specific error messages
            if (error.message && error.message.includes('link type')) {
                errorMessage = '{{ __("payment.select_link_type") }}';
            }
        }
        
        responseMessage.innerHTML = `
            <div class="alert alert-danger">
                <strong>{{ __("payment.error") }}!</strong> ${errorMessage}
            </div>
        `;
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.textContent = '{{ __("payment.save") }}';
    });
    
    return false; // Prevent default form submission
}
</script>
