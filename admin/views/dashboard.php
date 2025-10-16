<?php
    if (!defined('ABSPATH')) exit;
?>
<div class="wrap zikopay-dashboard">
    <h1><?php _e('Zikopay Dashboard', 'zikopay-payment-gateway'); ?></h1>
    <div class="zikopay-info-boxes">
        <div class="zikopay-info-box">
            <h2><?php _e('Quick Setup Guide', 'zikopay-payment-gateway'); ?></h2>
            <ol>
                <li><?php _e('Get your API credentials from Zikopay dashboard', 'zikopay-payment-gateway'); ?></li>
                <li><?php _e('Go to Settings and enter your API Key and Secret', 'zikopay-payment-gateway'); ?></li>
                <li><?php _e('Select payment methods and operators', 'zikopay-payment-gateway'); ?></li>
                <li><?php _e('Enable the gateway in WooCommerce settings', 'zikopay-payment-gateway'); ?></li>
                <li><?php _e('Test with a small transaction', 'zikopay-payment-gateway'); ?></li>
            </ol>
        </div>
        
        <div class="zikopay-info-box">
            <h2><?php _e('Supported Countries & Operators', 'zikopay-payment-gateway'); ?></h2>
            <ul class="countries-list">
                <?php
                    $countries = zikopay_get_countries();
                    foreach ($countries as $code => $name) {
                        $operators = zikopay_get_operators_by_country($code);
                        echo '<li><strong>' . esc_html($name) . ':</strong> ';
                        $op_names = array_map(function($op) { return $op['name']; }, $operators);
                        echo esc_html(implode(', ', $op_names));
                        echo '</li>';
                    }
                ?>
            </ul>
        </div>
    </div>
</div>