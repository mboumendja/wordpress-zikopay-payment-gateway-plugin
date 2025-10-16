<?php
if (!defined('ABSPATH')) exit;

class Zikopay_Webhook {
    
    public function __construct() {
        add_action('woocommerce_api_zikopay_webhook', array($this, 'handle_webhook'));
    }
    
    /**
     * Handle incoming webhook
     */
    public function handle_webhook() {
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);
        
        $this->log_webhook($payload);
        
        if (!$data || !isset($data['reference'])) {
            $this->send_response(400, 'Invalid webhook data');
            return;
        }
        
        $reference = sanitize_text_field($data['reference']);
        $status = isset($data['status']) ? sanitize_text_field($data['status']) : '';
        $transaction_id = isset($data['transaction_id']) ? sanitize_text_field($data['transaction_id']) : '';
        
        $orders = wc_get_orders(array(
            'meta_key' => '_zikopay_reference',
            'meta_value' => $reference,
            'limit' => 1
        ));
        
        if (empty($orders)) {
            $this->send_response(404, 'Order not found');
            return;
        }
        
        $order = $orders[0];
        
        switch ($status) {
            case 'completed':
                if ($order->get_status() !== 'completed' && $order->get_status() !== 'processing') {
                    $order->payment_complete($transaction_id);
                    $order->add_order_note(
                        sprintf(
                            __('Zikopay payment completed. Reference: %s, Transaction ID: %s', 'zikopay-payment-gateway'),
                            $reference,
                            $transaction_id
                        )
                    );
                }
                break;
                
            case 'failed':
                $order->update_status('failed', 
                    sprintf(__('Zikopay payment failed. Reference: %s', 'zikopay-payment-gateway'), $reference)
                );
                break;

            case 'failed':
                $order->update_status('failed', 
                    sprintf(__('Zikopay payment failed. Reference: %s', 'zikopay-payment-gateway'), $reference)
                );
                break;

            case 'cancelled	':
                $order->update_status('cancelled	', 
                    sprintf(__('Zikopay payment cancelled. Reference: %s', 'zikopay-payment-gateway'), $reference)
                );
                break;
                
            case 'cancelled':
                $order->update_status('cancelled', 
                    sprintf(__('Zikopay payment cancelled. Reference: %s', 'zikopay-payment-gateway'), $reference)
                );
                break;
                
            case 'pending':
                $order->update_status('on-hold', 
                    sprintf(__('Zikopay payment pending. Reference: %s', 'zikopay-payment-gateway'), $reference)
                );
                break;
        }
        
        $this->send_response(200, 'Webhook processed successfully');
    }
    
    /**
     * Send response
     */
    private function send_response($code, $message) {
        status_header($code);
        echo wp_json_encode(array('message' => $message));
        exit;
    }
    
    /**
     * Log webhook
     */
    private function log_webhook($payload) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Zikopay Webhook: ' . $payload);
        }
    }
}