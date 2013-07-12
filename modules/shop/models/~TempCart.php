<?php
/**
 * Model class for the cart
 * 
 * @package shop
 * @subpackage cart
 * @author Alex
 * @copyright http://portal30.ru
 *
 */
defined('COT_CODE') or die('Wrong URL.');
// $_SESSION['__shop']['cart'] - сессия корзины

if(!class_exists('ShopProduct')) require_once($cfg['modules_dir'].DS.'shop'.DS.'models'.DS.'product.php');


class TempCart Extends ShopCart {

    private static $_cart = null;

	var $useSSL = 1;
	// 	static $first = true;

	private function __construct() {
        global $cfg;

	}

    /**
     * Get the cart from the session
     * @return \ShopCart
     */
	public static function getCart() {

		if(empty(self::$_cart)){
			self::$_cart = new TempCart;
		}

		return self::$_cart;
	}
    

    
    /**
	 * Временная корзина не сохраняется
	 */
    public function save() {
		return false;
	}

}
