<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=shop.payment.calc_price
[END_COT_EXT]
==================== */
/**
 * Calculate the price (value, tax_id) of the selected method
 * It is called by the calculator
 * Вызывается из калькулятора при расчете стоимости в корзине
 * @package shop
 * @subpackage Plugins - Payment
 */
defined('COT_CODE') or die('Wrong URL');
global $db_shop_calcs, $db;

require_once cot_incfile('sp_standart', 'plug');

spst_convert($method);

// Проверка правильности метода, соотвествия суммы заказа, веса и т.п.
if (!spst_checkConditions($cart, $method)){
    $paymentValid = false;
    return;
}
$value = round(spst_getCosts($cart, $method), 2);
$this->cartPrices['paymentValue'] = $value;

$taxrules = array();
if (!empty($method->paym_params['tax_id'])) {
    // Todo в модель
    $res = $db->query("SELECT * FROM $db_shop_calcs WHERE calc_id={$method->paym_params['tax_id']}");
    $taxrules = $res->fetchAll();
}

$calculator = calculationHelper::getInstance();
if (count($taxrules) > 0) {
    $this->cartPrices['salesPricePayment'] = $calculator->roundInternal($calculator->executeCalculation($taxrules, $this->cartPrices['paymentValue']));
    $this->cartPrices['paymentTax'] = $calculator->roundInternal($this->cartPrices['salesPricePayment']) - $this->cartPrices['paymentValue'];
} else {
    $this->cartPrices['salesPricePayment'] = $value;
    $this->cartPrices['paymentTax'] = 0;
}
