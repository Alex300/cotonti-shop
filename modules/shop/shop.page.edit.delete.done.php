<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.edit.delete.done
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
 * удалени всей информации о товаре
 * не проверяем принадлежность страницы магазину, т.к. при страница товара ранее могла перенестись в категорию не магазина,
 * а потом удалиться
 */
Product::deleteProductInfoByPag($id);

if (inShopCat($pag['page_cat'])){

    
}