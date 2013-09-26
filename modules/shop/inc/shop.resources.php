<?php
/**
 * Resourses
 * module shop for Cotonti Siena
 * 
 * @package shop
 * @copyright http://portal30.ru
 * @license BSD
 */
defined('COT_CODE') or die('Wrong URL.');

global $R;

$R['shop_btn_order_confirm'] = '<a class="btn btn-primary" href="javascript:document.checkoutForm.submit();" ><i
    class=" icon-ok icon-white"></i> '.$L['shop']['order_confirm_mnu'] . '</a>';

$R['shop_btn_pay'] = '<button class="btn btn-primary" type="submit" ><i class=" icon-ok icon-white"></i> '.
    $L['shop']['pay'] . '</button>';

$R['shipmentName'] = '{$title} <em>({$desc})</em>';
$R['paymentName'] = '{$title} <em>({$desc})</em>';

$R['shop_minicart_showcart'] = '<a href="{$url}">{$text}</a>';