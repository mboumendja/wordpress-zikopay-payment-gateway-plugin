<?php
    if (!defined('ABSPATH')) exit;

    if (isset($_POST['zikopay_save_settings']) && check_admin_referer('zikopay_settings_action', 'zikopay_settings_nonce')) {
        update_option('zikopay_general_settings', $_POST['zikopay_general_settings']);
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'zikopay-payment-gateway') . '</p></div>';
    }

    $settings = get_option('zikopay_general_settings', array());
?>

<div class="wrap zikopay-settings">
    <h1><?php _e('Zikopay Settings', 'zikopay-payment-gateway'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('zikopay_settings_action', 'zikopay_settings_nonce'); ?>
        
        <h2 class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'zikopay-payment-gateway'); ?></a>
            <a href="#api" class="nav-tab"><?php _e('API Credentials', 'zikopay-payment-gateway'); ?></a>
            <a href="#operators" class="nav-tab"><?php _e('Operators', 'zikopay-payment-gateway'); ?></a>
            <a href="#advanced" class="nav-tab"><?php _e('Advanced', 'zikopay-payment-gateway'); ?></a>
        </h2>
        
        <!-- General Tab -->
        <div id="general" class="tab-content active">
            <table class="form-table">
                <tr>
                    <th><?php _e('Payment Title', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <input type="text" name="zikopay_general_settings[payment_title]" 
                               value="<?php echo esc_attr($settings['payment_title'] ?? 'Mobile Money & Card Payment'); ?>" 
                               class="regular-text">
                        <p class="description"><?php _e('Title shown to customers at checkout', 'zikopay-payment-gateway'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Payment Description', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <textarea name="zikopay_general_settings[payment_description]" rows="3" class="large-text"><?php 
                            echo esc_textarea($settings['payment_description'] ?? 'Pay securely using Mobile Money or Credit/Debit Card.'); 
                        ?></textarea>
                        <p class="description"><?php _e('Description shown to customers at checkout', 'zikopay-payment-gateway'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Success Message', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <textarea name="zikopay_general_settings[success_message]" rows="3" class="large-text"><?php 
                            echo esc_textarea($settings['success_message'] ?? 'Thank you! Your payment has been received.'); 
                        ?></textarea>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- API Credentials Tab -->
        <div id="api" class="tab-content">
            <table class="form-table">
                <tr>
                    <th><?php _e('Environment', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="zikopay_general_settings[environment]" value="test" 
                                   <?php checked($settings['environment'] ?? 'test', 'test'); ?>>
                            <?php _e('Test Mode', 'zikopay-payment-gateway'); ?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="zikopay_general_settings[environment]" value="live" 
                                   <?php checked($settings['environment'] ?? 'test', 'live'); ?>>
                            <?php _e('Live Mode', 'zikopay-payment-gateway'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th colspan="2"><h3><?php _e('API Credentials', 'zikopay-payment-gateway'); ?></h3></th>
                </tr>
                <tr>
                    <th><?php _e('API Public Key', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <input type="text" name="zikopay_general_settings[api_public_key]" 
                               value="<?php echo esc_attr($settings['api_public_key'] ?? ''); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><?php _e('API Secret key', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <input type="password" name="zikopay_general_settings[api_secret_key]" 
                               value="<?php echo esc_attr($settings['api_secret_key'] ?? ''); ?>" 
                               class="regular-text">
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Operators Tab -->
        <div id="operators" class="tab-content">
            <h3><?php _e('Enable Payment Methods', 'zikopay-payment-gateway'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Mobile Money', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="zikopay_general_settings[enable_mobile_money]" value="1" 
                                   <?php checked($settings['enable_mobile_money'] ?? 1, 1); ?>>
                            <?php _e('Enable Mobile Money payments', 'zikopay-payment-gateway'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Card Payments', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="zikopay_general_settings[enable_cards]" value="1" 
                                   <?php checked($settings['enable_cards'] ?? 1, 1); ?>>
                            <?php _e('Enable Card payments (Visa, Mastercard)', 'zikopay-payment-gateway'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <h3><?php _e('Mobile Money Operators', 'zikopay-payment-gateway'); ?></h3>
            <p class="description"><?php _e('Select which Mobile Money operators to enable. Customers will only see enabled operators.', 'zikopay-payment-gateway'); ?></p>
            
            <?php
                $operators = zikopay_get_operators();
                $countries = zikopay_get_countries();
                $enabled_ops = $settings['enabled_operators'] ?? array();
                
                foreach ($countries as $country_code => $country_name):
                    $country_operators = zikopay_get_operators_by_country($country_code);
                    if (empty($country_operators)) continue;
            ?>
            
                <div class="operator-country-group">
                    <h4><?php echo esc_html($country_name); ?></h4>
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th style="width: 50px;"><?php _e('Enable', 'zikopay-payment-gateway'); ?></th>
                                <th><?php _e('Operator', 'zikopay-payment-gateway'); ?></th>
                                <th><?php _e('Code', 'zikopay-payment-gateway'); ?></th>
                                <th><?php _e('Currency', 'zikopay-payment-gateway'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($country_operators as $op_code => $operator): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="zikopay_general_settings[enabled_operators][]" 
                                        value="<?php echo esc_attr($op_code); ?>" 
                                        <?php checked(in_array($op_code, $enabled_ops), true); ?>>
                                </td>
                                <td><?php echo esc_html($operator['name']); ?></td>
                                <td><code><?php echo esc_html($op_code); ?></code></td>
                                <td><?php echo esc_html($operator['currency']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            
            <?php endforeach; ?>
        </div>
        
        <!-- Advanced Tab -->
        <div id="advanced" class="tab-content">
            <table class="form-table">
                <tr>
                    <th><?php _e('Auto-detect Country', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="zikopay_general_settings[auto_detect_country]" value="1" 
                                   <?php checked($settings['auto_detect_country'] ?? 1, 1); ?>>
                            <?php _e('Automatically show operators based on customer billing country', 'zikopay-payment-gateway'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Debug Mode', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="zikopay_general_settings[debug_mode]" value="1" 
                                   <?php checked($settings['debug_mode'] ?? 0, 1); ?>>
                            <?php _e('Enable debug logging', 'zikopay-payment-gateway'); ?>
                        </label>
                        <p class="description"><?php _e('Log API requests and responses for troubleshooting', 'zikopay-payment-gateway'); ?></p>
                    </td>
                </tr>
                <!-- <tr>
                    <th><?php _e('Callback URL', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <input type="text" readonly value="<?php echo WC()->api_request_url('zikopay_webhook'); ?>" class="large-text" required>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Return URL', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <input type="text" name="zikopay_general_settings[custom_return_url]" required
                               value="<?php echo esc_attr($settings['custom_return_url'] ?? ''); ?>" 
                               class="large-text" placeholder="<?php echo site_url('/success'); ?>">
                        <p class="description"><?php _e('Required', 'zikopay-payment-gateway'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Cancel URL', 'zikopay-payment-gateway'); ?></th>
                    <td>
                        <input type="text" name="zikopay_general_settings[cancel_return_url]" readonly 
                            value="<?php echo wc_get_cart_url(); ?>" class="large-text" required>
                        <p class="description"><?php _e('Required', 'zikopay-payment-gateway'); ?></p>
                    </td>
                </tr> -->
            </table>
        </div>
        
        <p class="submit">
            <button type="submit" name="zikopay_save_settings" class="button button-primary button-large">
                <?php _e('Save Settings', 'zikopay-payment-gateway'); ?>
            </button>
        </p>
    </form>
</div>

<script>
    jQuery(document).ready(function($) {
        $('.nav-tab').click(function(e) {
            e.preventDefault();
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.tab-content').removeClass('active');
            $($(this).attr('href')).addClass('active');
        });
    });
</script>