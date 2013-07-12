<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.edit.update.done,page.add.add.done
Tags=
[END_COT_EXT]
==================== */
/**
 * module shop for Cotonti Siena
 * Product delete
 * @package shop
 * 
 */
defined('COT_CODE') or die('Wrong URL.');

require_once cot_langfile('shop', 'module');
require_once cot_incfile('shop', 'module');
require_once cot_incfile('shop', 'module', 'resources');


/**
 * Сохранение информации, спецефичной для товара
 * @todo артикул должен быть уникальным !!
 * @todo cache
 */
if (inShopCat($rpage['page_cat']) || inShopCat($row_page['page_cat'])){
    
    $rpage['page_id'] = $id;
    
    // Обработка цены
    $rpage['price']['price'] = cot_import('rprod_price', 'P', 'TXT');
    $rpage['price']['curr_id'] = cot_import('rprod_price_currency', 'P', 'INT');
    $rpage['price']['tax_id'] = cot_import('rprod_price_tax_id', 'P', 'INT');
    $rpage['price']['discount_id'] = cot_import('rprod_price_discount_id', 'P', 'INT');
    $rpage['price']['override'] = cot_import('rprice_override', 'P', 'INT', 2);
    $rpage['price']['override_price'] = cot_import('rprice_override_price', 'P', 'TXT');
    // Дополнительные цены
    $rpage['_addprice'] = cot_import('rprod_addprice', 'P', 'ARR');
    $rpage['_addprice_groups'] = cot_import('rprod_addprice_groups', 'P', 'ARR');
    $rpage['_addprice_min_quantity'] = cot_import('rprod_addprice_min_quantity', 'P', 'ARR');
    $rpage['_addprice_max_quantity'] = cot_import('rprod_addprice_max_quantity', 'P', 'ARR');

    $rpage['_price_sales'] = cot_import('rprod_salesPrice', 'P', 'TXT');
    $useDesPrice = cot_import('rprod_use_desired_price', 'P', 'BOL');

    $rpage['_wu_notify'] = cot_import('rprod_wu_notify', 'P', 'BOL');

    // Use desired price
    if($useDesPrice){
        $calculator = calculationHelper::getInstance();
        $rpage['price']['price'] = $calculator->calculateCostprice($rpage);
    }

    Product::saveInfoByPag($rpage);
}