<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=shop.shipment.calc_price
[END_COT_EXT]
==================== */
/**
 * Calculate the price (value, tax_id) of the selected method
 * It is called by the calculator
 * Вызывается из калькулятора при расчете стоимости в корзине
 * @package shop
 * @subpackage Plugins - shipment
 */
defined('COT_CODE') or die('Wrong URL');
global $db_shop_calcs, $db;

require_once cot_incfile('ss_weight_countries', 'plug');

sswc_convert($method);

// Проверка правильности метода, соотвествия суммы заказа, веса и т.п.
if (!sswc_checkConditions($cart, $method)){
    $shipmentValid = false;
    return;
}
$value = round(sswc_getCosts($cart, $method), 2);
$this->cartPrices['shipmentValue'] = $value;

$taxrules = array();
if (!empty($method->shipm_params['tax_id'])) {
    // Todo в модель
    $res = $db->query("SELECT * FROM $db_shop_calcs WHERE calc_id={$method->shipm_params['tax_id']}");
    $taxrules = $res->fetchAll();
}

$calculator = calculationHelper::getInstance();
if (count($taxrules) > 0) {
    $this->cartPrices['salesPriceShipment'] = $calculator->roundInternal($calculator->executeCalculation($taxrules,
                $this->cartPrices['shipmentValue']));
    $this->cartPrices['shipmentTax'] = $calculator->roundInternal($this->cartPrices['salesPriceShipment']) -
                $this->cartPrices['shipmentValue'];
} else {
    $this->cartPrices['salesPriceShipment'] = $value;
    $this->cartPrices['shipmentTax'] = 0;
}
