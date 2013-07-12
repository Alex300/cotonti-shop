<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.tags
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
//require_once cot_incfile('shop', 'module', 'resources');


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
if (inShopCat($pag['page_cat'])){
    
    // TODO для мультикатегорий товаров - получить категорию из запроса.
    // если в запросе не передано, тогда взять ее из $pag['page_cat']
    shop_setLastVisitedCategory($pag['page_cat']);
    shop_addProductToRecent($pag['page_id']);
    
    // TODO Получить соседние продукты (для ссылок вперед/назад)
	//$pag['_neighbours'] = $product_model->getNeighborProducts($product);
//    var_dump($pag);
//    die;

    
}
