<?php
if (!defined('ABSPATH')) exit;

class Zikopay_API {
    
    private $api_key;
    private $api_secret;
    private $testmode;
    private $base_url = 'https://api.payment.zikopay.com/v1';
    
    public function __construct($api_key, $api_secret, $testmode = false) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->testmode = $testmode;
    }
    
    /**
     * Process Mobile Money Payment
     */
    public function process_mobile_money($data) {
        $endpoint = $this->base_url . '/payments/payin/mobile-money';
        return $this->make_request($endpoint, $data);
    }
    
    /**
     * Process Card Payment
     */
    public function process_card($data) {
        $endpoint = $this->base_url . '/payments/payin/card';
        return $this->make_request($endpoint, $data);
    }
    
    /**
     * Verify Payment
     */
    public function verify_payment($reference) {
        $endpoint = $this->base_url . '/payment/status/' . $reference;
        return $this->make_request($endpoint, array(), 'GET');
    }
    
    /**
     * Make API Request
     */
    private function make_request($url, $data = array(), $method = 'POST') {
        $args = array(
            'method' => $method,
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-API-Key' => $this->api_key,
                'X-API-Secret' => $this->api_secret
            ),
            'timeout' => 30,
            'sslverify' => !$this->testmode
        );
        
        if ($method === 'POST' && !empty($data)) {
            $args['body'] = json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            $this->log_error('API Request Error: ' . $response->get_error_message());
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        $this->log_request($url, $data, $result);
        
        return $result;
    }
    
    /**
     * Log API Request
     */
    private function log_request($url, $request, $response) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_entry = array(
                'timestamp' => current_time('mysql'),
                'url' => $url,
                'request' => $request,
                'response' => $response
            );
            error_log('Zikopay API: ' . print_r($log_entry, true));
        }
    }
    
    /**
     * Log Error
     */
    private function log_error($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Zikopay Error: ' . $message);
        }
    }
}