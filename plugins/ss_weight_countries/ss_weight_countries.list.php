<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=shop.shipment.list
[END_COT_EXT]
==================== */
/**
 * This Hook is fired to display the shipment methods in the cart 
 * 
 * @package shop
 * @subpackage Plugins - shipment
 * @var CurrencyDisplay $currency
 */
defined('COT_CODE') or die('Wrong URL');
global $db_shop_calcs, $db;

include_once (cot_incfile('ss_weight_countries', 'plug'));

if (!sswc_checkConditions($cart, $method)) {
    $shipmentValid = false;
    return;
}

$value = sswc_getCosts($cart, $method);
$tax_id = $method->shipm_params['tax_id'];

$taxrules = array();
if (!empty($tax_id)) {
    // Todo в модель
    $res = $db->query("SELECT * FROM $db_shop_calcs WHERE calc_id={$tax_id}");
    $taxrules = $res->fetchAll();
}

if (count($taxrules) > 0) {
    $sswc_calculator = calculationHelper::getInstance();
    $shipmentMethods[$key]->totalPrice = $sswc_calculator->roundInternal($sswc_calculator->executeCalculation($taxrules, $value));
} else {
    $shipmentMethods[$key]->totalPrice = $value;
}