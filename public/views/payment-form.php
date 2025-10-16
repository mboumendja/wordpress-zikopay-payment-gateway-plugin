<?php
    if (!defined('ABSPATH')) exit;

    $settings = get_option('zikopay_general_settings', array());
    $enabled_operators = $settings['enabled_operators'] ?? array();
    $auto_detect = $settings['auto_detect_country'] ?? 1;
    $enable_mobile = $settings['enable_mobile_money'] ?? 1;
    $enable_cards = $settings['enable_cards'] ?? 1;

    // Get customer country
    $customer_country = WC()->customer ? WC()->customer->get_billing_country() : '';

    // Filter operators by country if auto-detect is enabled
    if ($auto_detect && !empty($customer_country)) {
        $operators_map = array(
            'CM' => 'CM',
            'CI' => 'CI',
            'SN' => 'SN',
            'BJ' => 'BJ',
            'TG' => 'TG'
        );
        
        $country_code = $operators_map[$customer_country] ?? '';
        if ($country_code) {
            $available_operators = zikopay_get_operators_by_country($country_code);
        } else {
            $available_operators = zikopay_get_operators();
        }
    } else {
        $available_operators = zikopay_get_operators();
    }

    // Filter by enabled operators
    if (!empty($enabled_operators)) {
        $available_operators = array_filter($available_operators, function($key) use ($enabled_operators) {
            return in_array($key, $enabled_operators);
        }, ARRAY_FILTER_USE_KEY);
    }

    $card_operators = zikopay_get_card_operators();
?>

<fieldset id="zikopay-payment-form" class="zikopay-payment-form">
    
    <?php if ($this->description): ?>
        <p><?php echo wpautop(wptrim(esc_html($this->description))); ?></p>
    <?php endif; ?>
    
    <?php if ($enable_mobile && $enable_cards): ?>
    <div class="form-row form-row-wide">
        <label for="zikopay_payment_type"><?php _e('Select Payment Method', 'zikopay-payment-gateway'); ?> <span class="required">*</span></label>
        <select name="zikopay_payment_type" id="zikopay_payment_type" class="select" required>
            <option value=""><?php _e('Choose payment method', 'zikopay-payment-gateway'); ?></option>
            <?php if ($enable_mobile && !empty($available_operators)): ?>
                <option value="mobile_money"><?php _e('Mobile Money', 'zikopay-payment-gateway'); ?></option>
            <?php endif; ?>
            <?php if ($enable_cards): ?>
                <option value="card"><?php _e('Card Payment', 'zikopay-payment-gateway'); ?></option>
            <?php endif; ?>
        </select>
    </div>
    <?php endif; ?>
    
    <!-- Mobile Money Fields -->
    <?php if ($enable_mobile && !empty($available_operators)): ?>
    <div id="zikopay_mobile_money_fields" class="zikopay-payment-fields" style="display: none;">
        <div class="form-row form-row-wide">
            <label for="zikopay_operator"><?php _e('Select Your Mobile Money Provider', 'zikopay-payment-gateway'); ?> <span class="required">*</span></label>
            <div class="zikopay-operators-grid">
                <?php foreach ($available_operators as $op_code => $operator): ?>
                <label class="zikopay-operator-option">
                    <input type="radio" name="zikopay_operator" value="<?php echo esc_attr($op_code); ?>" required>
                    <div class="operator-card">
                        <div class="operator-icon">
                            <?php
                            $icon_map = array(
                                'mtn' => '📱',
                                'orange' => '🟠',
                                'moov' => '🔵',
                                'wave' => '🌊',
                                'free' => '🆓',
                                'expresso' => '☕',
                                't_money' => '💰'
                            );
                            $icon_key = explode('_', $op_code)[0];
                            echo $icon_map[$icon_key] ?? '💳';
                            ?>
                        </div>
                        <div class="operator-info">
                            <strong><?php echo esc_html($operator['name']); ?></strong>
                            <small><?php echo esc_html($operator['country']); ?></small>
                        </div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-row form-row-wide">
            <label for="zikopay_phone"><?php _e('Phone Number', 'zikopay-payment-gateway'); ?> <span class="required">*</span></label>
            <input type="tel" class="input-text" name="zikopay_phone" id="zikopay_phone" 
                   placeholder="<?php _e('e.g., 237696447402', 'zikopay-payment-gateway'); ?>" required>
            <small class="description"><?php _e('Enter your mobile money number with country code', 'zikopay-payment-gateway'); ?></small>
        </div>
        
        <div class="zikopay-info-box">
            <span class="dashicons dashicons-info"></span>
            <p><?php _e('You will receive a payment prompt on your phone. Please approve it to complete the transaction.', 'zikopay-payment-gateway'); ?></p>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Card Payment Fields -->
    <?php if ($enable_cards): ?>
    <div id="zikopay_card_fields" class="zikopay-payment-fields" style="display: none;">
        <div class="form-row form-row-wide">
            <label for="zikopay_card_type"><?php _e('Select Card Type', 'zikopay-payment-gateway'); ?> <span class="required">*</span></label>
            <div class="zikopay-cards-grid">
                <?php foreach ($card_operators as $card_code => $card): ?>
                <label class="zikopay-card-option">
                    <input type="radio" name="zikopay_card_type" value="<?php echo esc_attr($card_code); ?>" required>
                    <div class="card-badge">
                        <span class="card-icon"><?php echo $card_code === 'visa' ? '💳' : '🏦'; ?></span>
                        <strong><?php echo esc_html($card['name']); ?></strong>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="zikopay-info-box">
            <span class="dashicons dashicons-lock"></span>
            <p><?php _e('You will be redirected to a secure page to enter your card details. Your information is encrypted and secure.', 'zikopay-payment-gateway'); ?></p>
        </div>
    </div>
    <?php endif; ?>
    
</fieldset>
