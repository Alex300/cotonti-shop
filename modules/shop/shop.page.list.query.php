<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.list.query
Tags=
[END_COT_EXT]
==================== */
/**
 * page.list.query
 *  - Запомнить последнюю категорию
 * 
 * @package shop
 * @author Alex
 * @copyright http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

require_once cot_langfile('shop', 'module');
require_once cot_incfile('shop', 'module');
require_once cot_incfile('shop', 'module', 'resources');
if (inShopCat($c)){
    // Запомнить последнюю категорию
    shop_setLastVisitedCategory($c);

    // Не показываем товар, если он закончился
    if(!cot::$cfg['shop']['use_as_catalog'] && cot::$cfg['shop']['stockhandle'] == 'disableit') {
        $join_columns .= ", (p.page_prod_in_stock - p.page_prod_ordered) AS prod_available";

        $where['shop'] = "(p.page_prod_in_stock - p.page_prod_ordered) > 0";
    }
}