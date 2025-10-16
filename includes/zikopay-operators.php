<?php
if (!defined('ABSPATH')) exit;

/**
 * Get all supported operators
 */
function zikopay_get_operators() {
    return array(
        'mtn_cm' => array(
            'name' => 'MTN Mobile Money',
            'country' => 'Cameroon',
            'country_code' => 'CM',
            'currency' => 'XAF',
            'type' => 'mobile_money',
            'icon' => 'mtn.png'
        ),
        'orange_cm' => array(
            'name' => 'Orange Money',
            'country' => 'Cameroon',
            'country_code' => 'CM',
            'currency' => 'XAF',
            'type' => 'mobile_money',
            'icon' => 'orange.png'
        ),
        'mtn_ci' => array(
            'name' => 'MTN MoMo',
            'country' => 'Côte d\'Ivoire',
            'country_code' => 'CI',
            'currency' => 'XOF',
            'type' => 'mobile_money',
            'icon' => 'mtn.png'
        ),
        'orange_ci' => array(
            'name' => 'Orange Money',
            'country' => 'Côte d\'Ivoire',
            'country_code' => 'CI',
            'currency' => 'XOF',
            'type' => 'mobile_money',
            'icon' => 'orange.png'
        ),
        'moov_ci' => array(
            'name' => 'Moov Money',
            'country' => 'Côte d\'Ivoire',
            'country_code' => 'CI',
            'currency' => 'XOF',
            'type' => 'mobile_money',
            'icon' => 'moov.png'
        ),
        'wave_ci' => array(
            'name' => 'Wave Mobile Money',
            'country' => 'Côte d\'Ivoire',
            'country_code' => 'CI',
            'currency' => 'XOF',
            'type' => 'mobile_money',
            'icon' => 'wave.png'
        ),
        'orange_sn' => array(
            'name' => 'Orange Money',
            'country' => 'Senegal',
            'country_code' => 'SN',
            'currency' => 'XOF',
            'type' => 'mobile_money',
            'icon' => 'orange.png'
        ),
        'free_money_sn' => array(
            'name' => 'Free Money',
            'country' => 'Senegal',
            'country_code' => 'SN',
            'currency' => 'XOF',
            'type' => 'mobile_money',
            'icon' => 'free.png'
        ),
        'expresso_sn' => array(
            'name' => 'Expresso Money',
            'country' => 'Senegal',
            'country_code' => 'SN',
            'currency' => 'XOF',
            'type' => 'mobile_money',
            'icon' => 'expresso.png'
        ),
        'mtn_bj' => array(
            'name' => 'MTN Mobile Money',
            'country' => 'Benin',
            'country_code' => 'BJ',
            'currency' => 'XOF',
            'type' => 'mobile_money',
            'icon' => 'mtn.png'
        ),
        'moov_bj' => array(
            'name' => 'Moov Money',
            'country' => 'Benin',
            'country_code' => 'BJ',
            'currency' => 'XOF',
            'type' => 'mobile_money',
            'icon' => 'moov.png'
        ),
        't_money_tg' => array(
            'name' => 'T Money',
            'country' => 'Togo',
            'country_code' => 'TG',
            'currency' => 'XOF',
            'type' => 'mobile_money',
            'icon' => 'tmoney.png'
        ),
    );
}

/**
 * Get operators by country
 */
function zikopay_get_operators_by_country($country_code = '') {
    $operators = zikopay_get_operators();
    
    if (empty($country_code)) {
        return $operators;
    }
    
    return array_filter($operators, function($op) use ($country_code) {
        return $op['country_code'] === strtoupper($country_code);
    });
}

/**
 * Get countries
 */
function zikopay_get_countries() {
    $operators = zikopay_get_operators();
    $countries = array();
    
    foreach ($operators as $code => $operator) {
        $countries[$operator['country_code']] = $operator['country'];
    }
    
    return array_unique($countries);
}

/**
 * Get card operators
 */
function zikopay_get_card_operators() {
    return array(
        'visa' => array(
            'name' => 'Visa',
            'type' => 'card',
            'icon' => 'visa.png'
        ),
        'mastercard' => array(
            'name' => 'Mastercard',
            'type' => 'card',
            'icon' => 'mastercard.png'
        ),
    );
}