<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=global
Order=12
[END_COT_EXT]
==================== */
 /**
  * глобальные действия
  * - миникорзина
  * 
  * @package shop
  * @todo кеширование принимаемых валют
  */
defined('COT_CODE') or die('Wrong URL.');

// При обращении по старым урлам периодически не находит xTemplate
if(!class_exists('XTemplate'))  require_once $cfg['system_dir'] . '/cotemplate.php';

require_once cot_incfile('forms', 'core');

// Заполнить select c производителями
global $L;
require_once cot_incfile('shop', 'module');
require_once cot_langfile('shop', 'module');
if (empty($db_pages)) require_once cot_incfile('page', 'module');



$shopManufacturers = Manufacturer::getKeyValPairsList();
if (!empty($cot_extrafields[$db_pages][$cfg["shop"]['pextf_manufacturer_id']])){
    if (is_array($shopManufacturers) && count($shopManufacturers) > 0){
        $cot_extrafields[$db_pages][$cfg["shop"]['pextf_manufacturer_id']]["field_variants"] = implode(',', array_keys($shopManufacturers));

        if ($m == 'edit' || $m == 'add' || $m== 'shop' || $env['location'] == 'administration'){
            $cot_extrafields[$db_pages][$cfg["shop"]['pextf_manufacturer_id']]["field_variants"] =
                '0,'.$cot_extrafields[$db_pages][$cfg["shop"]['pextf_manufacturer_id']]["field_variants"];
            $L[$cfg["shop"]['pextf_manufacturer_id'].'_0'] = '---';
        }
        foreach ($shopManufacturers as $key => $val){
            if(empty($L[$cfg["shop"]['pextf_manufacturer_id'].'_'.$key])){
                $L[$cfg["shop"]['pextf_manufacturer_id'].'_'.$key] = htmlspecialchars($val);
            }
        }
    }else{
        $cot_extrafields[$db_pages][$cfg["shop"]['pextf_manufacturer_id']]["field_variants"] = '0';
        $L[$cfg["shop"]['pextf_manufacturer_id'].'_0'] = '---';
    }
}
// /Заполнить select c производителями

// Заполнить select c единицами ДВШ
$shop_lwhUnit = array(
    'M'     => $L['shop']['LWH_unit_M'],
    'CM'    => $L['shop']['LWH_unit_CM'],
    'MM'    => $L['shop']['LWH_unit_MM'],
    'YD'    => $L['shop']['LWH_unit_YD'],
    'FT'    => $L['shop']['LWH_unit_FT'],
    'IN'    => $L['shop']['LWH_unit_IN'],
);
if (!empty($cot_extrafields[$db_pages][$cfg["shop"]['pextf_lwh_uom']])){
    $cot_extrafields[$db_pages][$cfg["shop"]['pextf_lwh_uom']]["field_variants"] = implode(',', array_keys($shop_lwhUnit));
    foreach ($shop_lwhUnit as $key => $val){
        if(empty($L[$cfg["shop"]['pextf_lwh_uom'].'_'.$key])){
            if ($m == 'edit' || $m == 'add' ){
                $L[$cfg["shop"]['pextf_lwh_uom'].'_'.$key] = htmlspecialchars($val);
            }else{
                $L[$cfg["shop"]['pextf_lwh_uom'].'_'.$key] = htmlspecialchars($L['shop']['LWH_unit_symbol_'.$key]);
            }
        }
    }
}
// /Заполнить select c единицами ДВШ

// Заполнить селект с единицами веса
$shop_weightUnit = array(
    'KG'    => $L['shop']['weight_unit_KG'],
    'GR'    => $L['shop']['weight_unit_GR'],
    'MG'    => $L['shop']['weight_unit_MG'],
    'LB'    => $L['shop']['weight_unit_LB'],
    'OZ'    => $L['shop']['weight_unit_OZ'],
);
if (!empty($cot_extrafields[$db_pages][$cfg["shop"]['pextf_weight_uom']])){
    $cot_extrafields[$db_pages][$cfg["shop"]['pextf_weight_uom']]["field_variants"] = implode(',', array_keys($shop_weightUnit));
    foreach ($shop_weightUnit as $key => $val){
        if(empty($L[$cfg["shop"]['pextf_weight_uom'].'_'.$key])){
            if ($m == 'edit' || $m == 'add' ){
                $L[$cfg["shop"]['pextf_weight_uom'].'_'.$key] = htmlspecialchars($val);
            }else{
                $L[$cfg["shop"]['pextf_weight_uom'].'_'.$key] = htmlspecialchars($L['shop']['weight_unit_symbol_'.$key]);
            }
        }
    }
}
// /Заполнить селект с единицами веса

// Выводим выбор валют
if (!defined('COT_ADMIN') && !in_array($m, array('add', edit))){
    $vendorId = 1;  // todo multix
    $shop_AccCurr = Vendor::getAccCurrencies($vendorId);
    if(count($shop_AccCurr) > 0){
        $cur_state = cot_import('currency_id', 'C', 'INT');
        $new_state = cot_import('currency_id', 'P', 'INT');
        if ($new_state > 0){
            cot_setcookie('currency_id', $new_state, '', $cfg['cookiepath'], $cfg['cookiedomain']);
            $usr['currency_id'] = $new_state;
        }else{
            $usr['currency_id'] = $cur_state;
        }
        $shop_selected = ($usr['currency_id']) ? $usr['currency_id'] : Vendor::getCurrencyId($vendorId);
        //$tmp = Currency::find("curr_id IN (".implode(',', $shop_AccCurr).")", 0, 0, 'curr_title', 'ASC');
        $tmp = $db->query("SELECT curr_id, CONCAT_WS(' ', curr_title, curr_symbol) as curr_text FROM $db_shop_currencies
                WHERE curr_id IN (".implode(',', $shop_AccCurr).")")->fetchAll(PDO::FETCH_KEY_PAIR);
        $shop_AccCurrList = $tmp;
        $currencySelect = '<form method="post" action="">'.cot_selectbox($shop_selected, 'currency_id', array_keys($shop_AccCurrList),
            array_values($shop_AccCurrList),false, array('onchange'=>'this.form.submit()'))."</form>";
    }
}
//var_dump(cot_auth('shop', 'any', 'A'));
// /Выводим выбор валют


/**
 * Хеадер пока не выполнился 
 */
$shop_headerDone = false; 
$shop_priceScript = false;
