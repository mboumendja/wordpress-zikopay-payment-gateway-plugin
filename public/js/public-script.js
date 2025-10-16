jQuery(document).ready(function($) {
    
    // Toggle payment fields based on selection
    $('#zikopay_payment_type').on('change', function() {
        var paymentType = $(this).val();
        
        $('.zikopay-payment-fields').slideUp(300);
        
        if (paymentType === 'mobile_money') {
            $('#zikopay_mobile_money_fields').slideDown(300);
            $('#zikopay_mobile_money_fields').find('input, select').prop('disabled', false);
            $('#zikopay_card_fields').find('input').prop('disabled', true);
        } else if (paymentType === 'card') {
            $('#zikopay_card_fields').slideDown(300);
            $('#zikopay_card_fields').find('input').prop('disabled', false);
            $('#zikopay_mobile_money_fields').find('input, select').prop('disabled', true);
        }
    });
    
    // If only one payment method is available, show it automatically
    if ($('#zikopay_payment_type option').length === 2) {
        $('#zikopay_payment_type option:last').prop('selected', true).trigger('change');
    }
    
    // Phone number formatting
    $('#zikopay_phone').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });
    
    // Validate phone number
    $('form.checkout').on('checkout_place_order', function() {
        if ($('#payment_method_zikopay').is(':checked')) {
            var paymentType = $('#zikopay_payment_type').val();
            
            if (!paymentType) {
                alert('Please select a payment method.');
                return false;
            }
            
            if (paymentType === 'mobile_money') {
                var operator = $('input[name="zikopay_operator"]:checked').val();
                var phone = $('#zikopay_phone').val();
                
                if (!operator) {
                    alert('Please select your mobile money provider.');
                    return false;
                }
                
                if (!phone || phone.length < 9) {
                    alert('Please enter a valid phone number.');
                    return false;
                }
            } else if (paymentType === 'card') {
                var cardType = $('input[name="zikopay_card_type"]:checked').val();
                
                if (!cardType) {
                    alert('Please select a card type.');
                    return false;
                }
            }
        }
    });
});