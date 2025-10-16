<?php
    if (!defined('ABSPATH')) exit;

class Zikopay_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Zikopay Dashboard', 'zikopay-payment-gateway'),
            __('Zikopay', 'zikopay-payment-gateway'),
            'manage_woocommerce',
            'zikopay-dashboard',
            array($this, 'dashboard_page'),
            'dashicons-money-alt',
            56
        );
        
        add_submenu_page(
            'zikopay-dashboard',
            __('Settings', 'zikopay-payment-gateway'),
            __('Settings', 'zikopay-payment-gateway'),
            'manage_woocommerce',
            'zikopay-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'zikopay') === false) {
            return;
        }
        
        wp_enqueue_style('zikopay-admin', ZIKOPAY_PLUGIN_URL . 'admin/css/admin-style.css', array(), ZIKOPAY_VERSION);
        wp_enqueue_script('zikopay-admin', ZIKOPAY_PLUGIN_URL . 'admin/js/admin-script.js', array('jquery'), ZIKOPAY_VERSION, true);
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('zikopay_settings', 'zikopay_general_settings');
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        include ZIKOPAY_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        include ZIKOPAY_PLUGIN_DIR . 'admin/views/settings-page.php';
    }
}
