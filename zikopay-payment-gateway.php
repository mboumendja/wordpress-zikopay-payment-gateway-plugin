<?php
/**
 * Plugin Name: Zikopay Payment Gateway for WooCommerce
 * Plugin URI: https://zikopay.com
 * Description: Accept Mobile Money and Card payments across Africa via Zikopay
 * Version: 1.0.0
 * Author: Zikopay
 * Author URI: https://zikopay.com
 * Text Domain: zikopay-payment-gateway
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ZIKOPAY_VERSION', '1.0.0');
define('ZIKOPAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ZIKOPAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ZIKOPAY_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check if WooCommerce is active
function zikopay_check_woocommerce() {
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        return false;
    }
    return true;
}

function zikopay_woocommerce_missing_notice() {
    echo '<div class="error"><p><strong>' . __('Zikopay Payment Gateway requires WooCommerce to be installed and active.', 'zikopay-payment-gateway') . '</strong></p></div>';
}

// Initialize plugin AFTER WordPress plugins are loaded
add_action('plugins_loaded', 'zikopay_init', 11);


function zikopay_init() {
    // Vérifier si WooCommerce est chargé
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'zikopay_woocommerce_missing_notice');
        return;
    }

    // Charger la traduction
    load_plugin_textdomain('zikopay-payment-gateway', false, dirname(ZIKOPAY_PLUGIN_BASENAME) . '/languages');

    require_once ZIKOPAY_PLUGIN_DIR . 'includes/zikopay-operators.php';
    require_once ZIKOPAY_PLUGIN_DIR . 'includes/class-zikopay-api.php';
    require_once ZIKOPAY_PLUGIN_DIR . 'includes/class-zikopay-webhook.php';

    // Charger le gateway seulement si la classe WC_Payment_Gateway existe
    if (class_exists('WC_Payment_Gateway')) {
        add_filter('woocommerce_payment_gateways', 'zikopay_add_gateway');
    }
    
    if (is_admin()) {
        require_once ZIKOPAY_PLUGIN_DIR . 'admin/class-zikopay-admin.php';
        new Zikopay_Admin();
    }

    require_once ZIKOPAY_PLUGIN_DIR . 'public/class-zikopay-public.php';
    
    new Zikopay_Public();
    new Zikopay_Webhook();
}

function zikopay_add_gateway($gateways) {
    $gateways[] = 'WC_Gateway_Zikopay';
    return $gateways;
}

// Activation hook
register_activation_hook(__FILE__, 'zikopay_activate');

function zikopay_activate() {
    if (!zikopay_check_woocommerce()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires WooCommerce to be installed and active.', 'zikopay-payment-gateway'));
    }
    if (!get_option('zikopay_version')) {
        update_option('zikopay_version', ZIKOPAY_VERSION);
        update_option('zikopay_installed_date', current_time('mysql'));
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'zikopay_deactivate');

function zikopay_deactivate() {
    // Cleanup if needed
}

// Add settings link
add_filter('plugin_action_links_' . ZIKOPAY_PLUGIN_BASENAME, 'zikopay_action_links');

function zikopay_action_links($links) {
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=zikopay') . '">' . __('Settings', 'zikopay-payment-gateway') . '</a>',
        '<a href="' . admin_url('admin.php?page=zikopay-dashboard') . '">' . __('Dashboard', 'zikopay-payment-gateway') . '</a>',
    );
    return array_merge($plugin_links, $links);
}