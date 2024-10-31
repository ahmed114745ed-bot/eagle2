function extracted() {
    // Collect CSRF token and amount value
    let token = $('input[name="_token"]').val();
    let amount = $('#amount').val();
    let type = $('#type').val();

    // Disable submit button and show loading text
    let submitButton = $('#submitButton');
    submitButton.attr('disabled', true).text('Loading...');

    // Send data using AJAX
    $.ajax({
        url: "/admin/save-payment-with-method",
        method: 'POST',
        data: {
            _token: token,
            amount: amount,
            type: type
        },
        success: function (response) {
            if (response.status == 0) {
               return $('#responseMessage').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
            }
            // Enable submit button and reset text
            submitButton.attr('disabled', false).text('Save');

            // Handle the success response (e.g., open a payment URL)
            var popup = window.open(response);

            if (popup == null || typeof (popup) == 'undefined') {
                alert('Please allow popups for this website');
            } else {
                popup.focus();
            }
        },
        error: function (xhr) {
            // Re-enable submit button and reset text
            submitButton.attr('disabled', false).text('Save');

            // Display error message
            $('#responseMessage').html('<div class="alert alert-danger">Error: ' + xhr.responseText + '</div>');
        }
    });
    return false;
}

$(document).ready(function() {


    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        e.preventDefault();

        return extracted();

    });
});
