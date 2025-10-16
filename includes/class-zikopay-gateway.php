<?php
if (!defined('ABSPATH')) exit;

class WC_Gateway_Zikopay extends WC_Payment_Gateway {
    
    public function __construct() {
        $this->id = 'zikopay';
        $this->icon = '';
        $this->has_fields = true;
        $this->method_title = __('Zikopay', 'zikopay-payment-gateway');
        $this->method_description = __('Accept Mobile Money and Card payments across Africa via Zikopay', 'zikopay-payment-gateway');
        
        $this->supports = array(
            'products',
            'refunds'
        );
        
        $this->init_form_fields();
        $this->init_settings();
        
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->testmode = 'yes' === $this->get_option('testmode');
        $this->api_key = $this->get_option('api_public_key');
        $this->api_secret = $this->get_option('api_secret_key');
        $this->payment_methods = $this->get_option('payment_methods', array('mobile_money'));
        $this->enabled_operators = $this->get_option('enabled_operators', array());
        $this->auto_detect_country = $this->get_option('auto_detect_country', 'yes');
        
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
    }
    
    /**
     * Initialize form fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'zikopay-payment-gateway'),
                'type' => 'checkbox',
                'label' => __('Enable Zikopay Payment Gateway', 'zikopay-payment-gateway'),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', 'zikopay-payment-gateway'),
                'type' => 'text',
                'description' => __('Payment method title shown during checkout.', 'zikopay-payment-gateway'),
                'default' => __('Mobile Money & Card Payment', 'zikopay-payment-gateway'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Description', 'zikopay-payment-gateway'),
                'type' => 'textarea',
                'description' => __('Payment method description shown during checkout.', 'zikopay-payment-gateway'),
                'default' => __('Pay securely using Mobile Money or Credit/Debit Card.', 'zikopay-payment-gateway'),
                'desc_tip' => true,
            ),
            'testmode' => array(
                'title' => __('Test Mode', 'zikopay-payment-gateway'),
                'type' => 'checkbox',
                'label' => __('Enable Test Mode', 'zikopay-payment-gateway'),
                'default' => 'yes',
                'description' => __('Use test API credentials.', 'zikopay-payment-gateway'),
            ),
            'api_public_key' => array(
                'title' => __('API Public Key', 'zikopay-payment-gateway'),
                'type' => 'text',
                'description' => __('Get from Zikopay dashboard.', 'zikopay-payment-gateway'),
                'default' => '',
            ),
            'api_secret_key' => array(
                'title' => __('API Secret Key', 'zikopay-payment-gateway'),
                'type' => 'password',
                'description' => __('Get from Zikopay dashboard.', 'zikopay-payment-gateway'),
                'default' => '',
            ),
            'payment_methods' => array(
                'title' => __('Payment Methods', 'zikopay-payment-gateway'),
                'type' => 'multiselect',
                'class' => 'wc-enhanced-select',
                'description' => __('Select payment methods to enable.', 'zikopay-payment-gateway'),
                'default' => array('mobile_money'),
                'options' => array(
                    'mobile_money' => __('Mobile Money', 'zikopay-payment-gateway'),
                    'card' => __('Card Payment', 'zikopay-payment-gateway'),
                ),
                'desc_tip' => true,
            ),
            'enabled_operators' => array(
                'title' => __('Mobile Money Operators', 'zikopay-payment-gateway'),
                'type' => 'multiselect',
                'class' => 'wc-enhanced-select',
                'description' => __('Select which operators to enable. Leave empty for all.', 'zikopay-payment-gateway'),
                'default' => array(),
                'options' => $this->get_operator_options(),
                'desc_tip' => true,
            ),
            'auto_detect_country' => array(
                'title' => __('Auto-detect Country', 'zikopay-payment-gateway'),
                'type' => 'checkbox',
                'label' => __('Automatically filter operators by customer billing country', 'zikopay-payment-gateway'),
                'default' => 'yes',
            ),
        );
    }
    
    /**
     * Get operator options for settings
     */
    private function get_operator_options() {
        $operators = zikopay_get_operators();
        $options = array();
        
        foreach ($operators as $code => $operator) {
            $options[$code] = sprintf('%s - %s (%s)', 
                $operator['name'], 
                $operator['country'],
                $operator['currency']
            );
        }
        
        return $options;
    }
    
    /**
     * Payment scripts
     */
    public function payment_scripts() {
        if (!is_checkout()) {
            return;
        }
        
        wp_enqueue_style('zikopay-public', ZIKOPAY_PLUGIN_URL . 'public/css/public-style.css', array(), ZIKOPAY_VERSION);
        wp_enqueue_script('zikopay-public', ZIKOPAY_PLUGIN_URL . 'public/js/public-script.js', array('jquery'), ZIKOPAY_VERSION, true);
    }
    
    /**
     * Payment fields
     */
    public function payment_fields() {
        include ZIKOPAY_PLUGIN_DIR . 'public/views/payment-form.php';
    }
    
    /**
     * Process payment
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $payment_type = isset($_POST['zikopay_payment_type']) ? sanitize_text_field($_POST['zikopay_payment_type']) : '';
        
        if (empty($payment_type)) {
            wc_add_notice(__('Please select a payment method.', 'zikopay-payment-gateway'), 'error');
            return;
        }
        
        $api = new Zikopay_API($this->api_key, $this->api_secret, $this->testmode);
        
        if ($payment_type === 'mobile_money') {
            return $this->process_mobile_money($order, $order_id, $api);
        } elseif ($payment_type === 'card') {
            return $this->process_card($order, $order_id, $api);
        }
        
        wc_add_notice(__('Invalid payment method.', 'zikopay-payment-gateway'), 'error');
        return;
    }
    
    /**
     * Process mobile money payment
     */
    private function process_mobile_money($order, $order_id, $api) {
        $operator = isset($_POST['zikopay_operator']) ? sanitize_text_field($_POST['zikopay_operator']) : '';
        $phone = isset($_POST['zikopay_phone']) ? sanitize_text_field($_POST['zikopay_phone']) : '';
        
        if (empty($operator) || empty($phone)) {
            wc_add_notice(__('Please provide operator and phone number.', 'zikopay-payment-gateway'), 'error');
            return;
        }
        
        $payload = array(
            'amount' => floatval($order->get_total()),
            'currency' => $order->get_currency(),
            'phoneNumber' => $phone,
            'operator' => $operator,
            'return_url' => $this->get_return_url($order),
            'cancel_url' => wc_get_checkout_url(),
            'callback_url' => WC()->api_request_url('zikopay_webhook'),
            'description' => sprintf(__('Order #%s', 'zikopay-payment-gateway'), $order->get_order_number()),
            'payment_details' => array(
                'order_id' => $order_id
            ),
            'customer' => array(
                'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'phone' => $order->get_billing_phone(),
                'email' => $order->get_billing_email()
            )
        );
        
        $response = $api->process_mobile_money($payload);
        
        if ($response && isset($response['success']) && $response['success']) {
            $reference = $response['data']['reference'];
            $order->update_meta_data('_zikopay_reference', $reference);
            $order->update_meta_data('_zikopay_payment_type', 'mobile_money');
            $order->update_meta_data('_zikopay_operator', $operator);
            $order->save();
            
            $order->update_status('on-hold', __('Awaiting Mobile Money payment confirmation.', 'zikopay-payment-gateway'));
            
            wc_reduce_stock_levels($order_id);
            WC()->cart->empty_cart();
            
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        } else {
            $error = isset($response['message']) ? $response['message'] : __('Payment failed.', 'zikopay-payment-gateway');
            wc_add_notice($error, 'error');
            return;
        }
    }

    /**
     * Process card payment
     */
    private function process_card($order, $order_id, $api) {
        $card_type = isset($_POST['zikopay_card_type']) ? sanitize_text_field($_POST['zikopay_card_type']) : '';
        
        if (empty($card_type)) {
            wc_add_notice(__('Please select a card type.', 'zikopay-payment-gateway'), 'error');
            return;
        }
        
        $payload = array(
            'amount' => floatval($order->get_total()),
            'currency' => $order->get_currency(),
            'operator' => $card_type,
            'return_url' => $this->get_return_url($order),
            'cancel_url' => wc_get_checkout_url(),
            'callback_url' => WC()->api_request_url('zikopay_webhook'),
            'description' => sprintf(__('Order #%s', 'zikopay-payment-gateway'), $order->get_order_number()),
            'payment_details' => array(
                'order_id' => $order_id
            ),
            'customer' => array(
                'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'phone' => $order->get_billing_phone(),
                'email' => $order->get_billing_email()
            )
        );
        
        $response = $api->process_card($payload);
        
        if ($response && isset($response['success']) && $response['success'] && isset($response['data']['payment_url'])) {
            $reference = $response['data']['reference'];
            $payment_url = $response['data']['payment_url'];
            
            $order->update_meta_data('_zikopay_reference', $reference);
            $order->update_meta_data('_zikopay_payment_type', 'card');
            $order->save();
            
            $order->update_status('pending', __('Awaiting card payment.', 'zikopay-payment-gateway'));
            
            wc_reduce_stock_levels($order_id);
            WC()->cart->empty_cart();
            
            return array(
                'result' => 'success',
                'redirect' => $payment_url
            );
        } else {
            $error = isset($response['message']) ? $response['message'] : __('Payment failed.', 'zikopay-payment-gateway');
            wc_add_notice($error, 'error');
            return;
        }
    }
}
