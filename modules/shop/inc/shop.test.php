<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Controller class for the some tests
 *
 * @package shop
 * @subpackage test
 * @author Alex
 * @copyright http://portal30.ru
 */

class TestController{
    
    /**
     * Main (index) Action.
     */
    public function indexAction(){

        $Cart = ShopCart::getInstance();


        // Товары
        $prod = Product::getById(4);
//        var_dump($prod->prices);

        // Добавление товаров в корзину
        $shop_product_ids = array(4,7);
        //$Cart->add(4,2);
        //var_dump($Cart);

    }

}