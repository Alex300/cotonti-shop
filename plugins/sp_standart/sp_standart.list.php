<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=shop.payment.list
[END_COT_EXT]
==================== */
/**
 * This Hook is fired to display the payment methods in the cart
 * 
 * @package shop
 * @subpackage Plugins - payment
 * @var CurrencyDisplay $currency
 */
defined('COT_CODE') or die('Wrong URL');
global $db_shop_calcs, $db;

include_once (cot_incfile('sp_standart', 'plug'));

if (!spst_checkConditions($cart, $method)) {
    $paymentValid = false;
    return;
}

$value = spst_getCosts($cart, $method);
$tax_id = !empty($method->paym_params['tax_id']) ? $method->paym_params['tax_id'] : 0;

$taxrules = array();
if (!empty($tax_id)) {
    // Todo в модель
    $res = $db->query("SELECT * FROM $db_shop_calcs WHERE calc_id={$tax_id}");
    $taxrules = $res->fetchAll();
}

if (count($taxrules) > 0) {
    $spst_calculator = calculationHelper::getInstance();
    $paymentMethods[$key]->totalPrice = $spst_calculator->roundInternal($spst_calculator->executeCalculation($taxrules, $value));
} else {
    $paymentMethods[$key]->totalPrice = $value;
}

