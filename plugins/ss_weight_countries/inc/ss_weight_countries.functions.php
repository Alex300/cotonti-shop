<?php
/**
 * Получить цену доставки
 * @param ShopCart $cart
 * @param ShipmentMethod $method
 * @return float
 */
function sswc_getCosts(ShopCart $cart, $method) {
    if ($method->shipm_params['free_shipment'] && $cart->order_salesPrice >= $method->shipm_params['free_shipment']) {
        return 0;
    } else {
        return $method->shipm_params['cost'] + $method->shipm_params['package_fee'];
    }
}
/**
 * Проверка способа доствки на соотвествие заказу
 * @param ShopCart $cart
 * @param ShipmentMethod $method
 * @return bool
 * @todo масса заказа
 */
function sswc_checkConditions($cart, $method) {
    global $cfg;

//    $orderWeight = $this->getOrderWeight($cart, $method['shipm_params']['weight_unit']);
    $orderWeight = 0; // Временно
    if(!empty($cart->shipTo) && !$cart->STsameAsBT){
        $address = $cart->shipTo;
    }else{
        $address = $cart->billTo;
    }

    $nbShipment = 0;
    $countries = array();
    if (!empty($method->shipm_params['countries'])) {
        if (!is_array($method->shipm_params['countries'])) {
            $countries[0] = $method->shipm_params['countries'];
        } else {
            $countries = $method->shipm_params['countries'];
        }
    }

    // probably did not gave his BT:ST address
    if (empty($address)) {
        $address = new OrderUserInfo;
        $address->oui_zip = 0;
        $address->oui_country = '00';
    }

    $weight_cond        = sswc_weightCond($orderWeight, $method);
    $nbproducts_cond    = sswc_nbproductsCond($cart, $method);
    $orderamount_cond   = sswc_orderamountCond($cart, $method);

    if (isset($address->oui_zip)) {
        $zip_cond = sswc_zipCond($address->oui_zip, $method);
    } else {
        //no zip in address data normally occurs only, when it is removed from the form by the shopowner
        $zip_cond = true;
    }

    if (!isset($address->oui_country)) $address->oui_country = '00';
    if (count($countries) == 0 || in_array($address->oui_country, $countries)) {

        if ($weight_cond AND $zip_cond AND $nbproducts_cond AND $orderamount_cond) {
            return true;
        }
    }

    return false;
}

function sswc_convert(&$method) {
    //$method->weight_start = (float) $method->weight_start;
    //$method->weight_stop = (float) $method->weight_stop;
    $method->shipm_params['orderamount_start']    = (float) $method->shipm_params['orderamount_start'];
    $method->shipm_params['orderamount_stop']     = (float) $method->shipm_params['orderamount_stop'];
    $method->shipm_params['zip_start']            = (int) $method->shipm_params['zip_start'];
    $method->shipm_params['zip_stop']             = (int) $method->shipm_params['zip_stop'];
    $method->shipm_params['nbproducts_start']     = (int) $method->shipm_params['nbproducts_start'];
    $method->shipm_params['nbproducts_stop']      = (int) $method->shipm_params['nbproducts_stop'];
    $method->shipm_params['free_shipment']        = (float) $method->shipm_params['free_shipment'];
}

/**
 * Check the conditions on Weight
 * @param float $orderWeight
 * @param array $method
 * @return bool if Zip condition is ok or not
 */
function sswc_weightCond($orderWeight, $method) {

    $weight_cond = ( ($orderWeight >= $method->shipm_params['weight_start'] &&
            $orderWeight <= $method->shipm_params['weight_stop']  )
        ||
        ($method->shipm_params['weight_start'] <= $orderWeight && $method->shipm_params['weight_stop'] === ''   ) );

    return $weight_cond;
}
/**
 * @param ShopCart $cart
 * @param $method
 * @return bool
 */
function sswc_nbproductsCond($cart, $method) {
    $nbproducts = 0;
    foreach ($cart->products as $product) {
        $nbproducts += $product->prod_quantity;
    }
    if (!isset($method->shipm_params['nbproducts_start']) && !isset($method->shipm_params['nbproducts_stop'])) {
        return true;
    }
    if ($nbproducts) {
        $nbproducts_cond = ($nbproducts >= $method->shipm_params['nbproducts_start'] &&
            $nbproducts <= $method->shipm_params['nbproducts_stop']
            ||
            ($method->shipm_params['nbproducts_start'] <= $nbproducts &&
                ($method->shipm_params['nbproducts_stop'] == 0) ));
    } else {
        $nbproducts_cond = true;
    }
    return $nbproducts_cond;
}

/**
 * @param ShopCart $cart
 * @param ShipmentMethod $method
 * @return bool
 */
function sswc_orderamountCond($cart, $method) {
    $orderamount = 0;

    if (!isset($method->shipm_params['orderamount_start']) && !isset($method->shipm_params['orderamount_stop'])) {
        return true;
    }
    if ($cart->order_salesPrice) {
        $orderamount_cond = ($cart->order_salesPrice >= $method->shipm_params['orderamount_start'] &&
            $cart->order_salesPrice <= $method->shipm_params['orderamount_stop']
            ||
            ($method->shipm_params['orderamount_start'] <= $cart->order_salesPrice &&
                ($method->shipm_params['orderamount_stop'] == 0) ));
    } else {
        $orderamount_cond = true;
    }
    return $orderamount_cond;
}

/**
 * Check the conditions on Zip code
 * @param int $zip : zip code
 * @param array $method
 * @return bool if Zip condition is ok or not
 */
function sswc_zipCond($zip, $method) {
    $zip = (int)$zip;
    if (!empty($zip)) {
        $zip_cond = (( $zip >=   $method->shipm_params['zip_start'] && $zip <=   $method->shipm_params['zip_stop'] )
            OR
            (  $method->shipm_params['zip_start'] <= $zip && (  $method->shipm_params['zip_stop'] == 0) ));
    } else {
        $zip_cond = true;
    }
    return $zip_cond;
}