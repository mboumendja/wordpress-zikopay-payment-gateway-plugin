jQuery(document).ready(function($) {
    
    // Validate API credentials
    $('#zikopay_test_api_key, #zikopay_test_api_secret').on('blur', function() {
        var apiKey = $('#zikopay_test_api_key').val();
        var apiSecret = $('#zikopay_test_api_secret').val();
        
        if (apiKey && apiSecret) {
            // You can add API validation here
            console.log('Validating credentials...');
        }
    });
    
    // Toggle operator selection by country
    $('.operator-country-group').each(function() {
        var $group = $(this);
        var $checkboxes = $group.find('input[type="checkbox"]');
        
        // Add select all button
        var $selectAll = $('<button type="button" class="button button-small" style="margin-bottom: 10px;">Select All</button>');
        $group.find('h4').after($selectAll);
        
        $selectAll.on('click', function() {
            var allChecked = $checkboxes.filter(':checked').length === $checkboxes.length;
            $checkboxes.prop('checked', !allChecked);
        });
    });
});