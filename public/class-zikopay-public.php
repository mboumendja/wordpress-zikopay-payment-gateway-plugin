<?php
if (!defined('ABSPATH')) exit;

class Zikopay_Public {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
    }
    
    /**
     * Enqueue public assets
     */
    public function enqueue_public_assets() {
        if (is_checkout()) {
            wp_enqueue_style('zikopay-public', ZIKOPAY_PLUGIN_URL . 'public/css/public-style.css', array(), ZIKOPAY_VERSION);
            wp_enqueue_script('zikopay-public', ZIKOPAY_PLUGIN_URL . 'public/js/public-script.js', array('jquery'), ZIKOPAY_VERSION, true);
            
            wp_localize_script('zikopay-public', 'zikopayData', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('zikopay_public_nonce')
            ));
        }
    }
}