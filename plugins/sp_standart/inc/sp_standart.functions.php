<?php
/**
 * @param ShopCart $cart
 * @param PaymentMethod $method
 * @return float
 */
function spst_getCosts(ShopCart $cart, $method) {
    if (preg_match('/%$/', $method->paym_params['cost_percent_total'])) {
        $cost_percent_total = substr($method->paym_params['cost_percent_total'], 0, -1);
    } else {
        $cost_percent_total = $method->paym_params['cost_percent_total'];
    }
    $method->totalPrice = ($method->paym_params['cost_per_transaction'] + ($cart->order_salesPrice * $cost_percent_total * 0.01));
    return $method->totalPrice;
}

function spst_convert(&$method) {

    $method->paym_params['min_amount'] = (float) $method->paym_params['min_amount'];
    $method->paym_params['max_amount'] = (float) $method->paym_params['max_amount'];
}

/**
 * Check if the payment conditions are fulfilled for this payment method
 * @param ShopCart $cart cart prices
 * @param PaymentMethod $method
 * @return bool true: if the conditions are fulfilled, false otherwise
 */
function spst_checkConditions($cart, $method) {

    if(!empty($cart->shipTo) && !$cart->STsameAsBT){
        $address = $cart->shipTo;
    }else{
        $address = $cart->billTo;
    }
    $amount = $cart->order_salesPrice;
    $amount_cond = ($amount >= $method->paym_params['min_amount'] && $amount <= $method->paym_params['max_amount']
        || ($method->paym_params['min_amount'] <= $amount && ($method->paym_params['max_amount'] == 0) ) );
    if (!$amount_cond) {
        return false;
    }
    $countries = array();
    if (!empty($method->paym_params['countries'])) {
        if (!is_array($method->paym_params['countries'])) {
            $countries[0] = $method->paym_params['countries'];
        } else {
            $countries = $method->paym_params['countries'];
        }
    }
    // probably did not gave his BT:ST address
    if (empty($address)) {
        $address = new OrderUserInfo();
        $address->oui_country = '00';
    }

    if (!isset($address->oui_country)) $address->oui_country = '00';
    if (count($countries) == 0 || in_array($address->oui_country, $countries)) {
        return true;
    }

    return false;
}