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

//$R['shop_link_pls_configure'] = '<a href="{$link}">{$cot_img_down}</a>';
$R['shipmentName'] = '{$title} <em>({$desc})</em>';
$R['paymentName'] = '{$title} <em>({$desc})</em>';