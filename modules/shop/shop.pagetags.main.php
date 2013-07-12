<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=pagetags.main
[END_COT_EXT]
==================== */
/**
 * module shop for Cotonti Siena
 * 
 * @package shop
 * @author Alex
 * @copyright http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

require_once cot_langfile('shop', 'module');
require_once cot_incfile('shop', 'module');
require_once cot_incfile('shop', 'module', 'resources');

/**
 * @todo права пользователя на добавления товара в корзину/просмотр цен. Например 2 и 3
 * @todo функция "Задать вопрос по товару"; Можно прямо в обратную связь
 * @todo при мультипродавце - ссылка на страницу с информацией о продавце
 * @todo Availability Image
 * @todo Product Packaging
 * @todo customfieldsRelatedProducts сопутствующие товары (c этим продуктом покупают)
 * @todo customfieldsRelatedCategories сопутствующие категории
 * @todo недавно просмотренные товары (надо ли?)
 */
if (inShopCat($page_data['page_cat'])){

    $temp_array = $temp_array + Product::generateTags($page_data);

}