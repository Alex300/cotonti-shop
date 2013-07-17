<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.list.before_loop
Tags=
[END_COT_EXT]
==================== */
/**
 * Подгрузить сразу всю инфу по товарам, чтобы не грузить ее в цикле. Так быстрее.
 * Для этого дополним массив $sqllist_rowset нужными данными, далее cot_generate_pagetags() работает в обычном режиме
 * @package shop
 * @author Alex
 * @copyright http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

require_once cot_langfile('shop', 'module');
require_once cot_incfile('shop', 'module');
require_once cot_incfile('shop', 'module', 'resources');

if (inShopCat($c)){

    if(count($sqllist_rowset) <= 0) return;

    // todo проверка на необходимость выбора цен;
    $shopWithCalc = true;
    if($cfg['shop']['show_prices'] == 0) $shopWithCalc = false;

    /** @var Product[] $products */
    $products = array();
    $prodIds = array();
    $manufactIds = array();
    foreach($sqllist_rowset as $key => $row){
        $prodIds[] = (int)$row['page_id'];
    }

    if(count($prodIds) <= 0) return false;  // Ничего не найдено
    $tmp = Product::find(array(array('page_id', $prodIds)));
    if(!$tmp) return false;
    foreach($tmp as $prkey => $product){
        $products[$product->prod_id] = $product;
    }


    foreach($sqllist_rowset as $key => $row){
        $sqllist_rowset[$key]['manufacturer'] = $products[$row['page_id']]->manufacturer;
        $sqllist_rowset[$key]['add_prices'] = $products[$row['page_id']]->add_prices;
        $sqllist_rowset[$key]['price'] = $products[$row['page_id']]->price;
        $sqllist_rowset[$key]['prices'] = $products[$row['page_id']]->prices;
    }
    unset($products, $tmp);

    reset($sqllist_rowset);
}