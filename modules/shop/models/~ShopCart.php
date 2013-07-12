<?php
/**
 * Model class for the cart
 * 
 * @package shop
 * @subpackage cart
 * @author Alex
 * @copyright http://portal30.ru
 * @todo унаследовать от модели заказа
 *
 */
defined('COT_CODE') or die('Wrong URL.');
// $_SESSION['__shop']['cart'] - сессия корзины

if(!class_exists('ShopProduct')) require_once($cfg['modules_dir'].DS.'shop'.DS.'models'.DS.'product.php');


class ShopCart_ {

	var $products = array();
	protected $_inCheckOut = false;
	protected $_dataValidated = false;
	protected $_confirmDone = false;
	protected $_lastError = null; // Used to pass errmsg to the cart using addJS()
	//todo multivendor stuff must be set in the add function, first product determins ownership of cart, 
    //or a fixed vendor is used
	var $vendorId = 1;
	var $lastVisitedCategory = '';
	var $shipmentmethod_id = 0;
	var $paymentmethod_id = 0;
	var $automaticSelectedShipment = false;
	var $automaticSelectedPayment  = false;
	var $BT = 0;
	var $ST = 0;
	var $tosAccepted = null;
	var $customer_comment = '';
	var $couponCode = '';
	var $cartData = null;
	var $lists = null;
	// 	var $user = null;
//	var $prices = null;
	var $pricesUnformatted = null;
	var $pricesCurrency = null;
	var $paymentCurrency = null;
	var $STsameAsBT = 0;

	private static $_cart = null;
    private static $_triesValidateCoupon;

    /**
     * @deprecated
     */
    protected $_adapterProduct;
    
	var $useSSL = 1;
	// 	static $first = true;

	private function __construct() {
        global $cfg;
        
        $this->_adapterProduct = new ShopProduct();
        
		$this->useSSL = $cfg["shop"]['useSSL'];
		$this->useXHTML = true;
        self::$_triesValidateCoupon = 0;
	}

    /**
     * Get the cart from the session
     * @param bool $setCart
     * @param array $options
     * @internal param array $cart the cart to store in the session
     * @return \ShopCart
     */
	public static function getCart($setCart = true, $options = array()) {

		if(empty(self::$_cart)){
            $cartSession = $_SESSION['__shop']['cart'];

			if (!empty($cartSession)) {
				$cartData = unserialize( $cartSession );
				self::$_cart = new ShopCart;

				self::$_cart->products                      = $cartData->products;
				self::$_cart->vendorId	 					= $cartData->vendorId;
				self::$_cart->lastVisitedCategory	 		= $cartData->lastVisitedCategory;
				self::$_cart->shipmentmethod_id             = $cartData->shipmentmethod_id;
				self::$_cart->paymentmethod_id              = $cartData->paymentmethod_id;
				self::$_cart->automaticSelectedShipment 	= $cartData->automaticSelectedShipment;
				self::$_cart->automaticSelectedPayment 		= $cartData->automaticSelectedPayment;
				self::$_cart->BT 							= $cartData->BT;
				self::$_cart->ST 							= $cartData->ST;
				self::$_cart->tosAccepted 					= $cartData->tosAccepted;
				self::$_cart->customer_comment 				= base64_decode($cartData->customer_comment);
				self::$_cart->couponCode 					= $cartData->couponCode;
				self::$_cart->cartData 						= $cartData->cartData;
                self::$_cart->order_number					= $cartData->order_number;
				self::$_cart->lists 						= $cartData->lists;
				//				self::$_cart->prices 						= $cartData->prices;
				self::$_cart->pricesUnformatted				= $cartData->pricesUnformatted;
				self::$_cart->pricesCurrency				= $cartData->pricesCurrency;
				self::$_cart->paymentCurrency				= $cartData->paymentCurrency;

				self::$_cart->_inCheckOut 					= $cartData->_inCheckOut;
				self::$_cart->_dataValidated				= $cartData->_dataValidated;
				self::$_cart->_confirmDone					= $cartData->_confirmDone;
				self::$_cart->STsameAsBT					= $cartData->STsameAsBT;

			}

		}

		if(empty(self::$_cart)){
			self::$_cart = new ShopCart;
		}

		if ( $setCart == true ) {
			self::$_cart->setPreferred();
			self::$_cart->save();
		}

		return self::$_cart;
	}
    
    /**
	 * Add a product to the cart
     * @param array $product_ids
     * @param string $errorMsg
     * @return bool
     */
	public function add($product_ids = null, &$errorMsg='') {
        global $L, $cfg;
        
		$success = false;
		if(empty($product_ids)){
			$product_ids = cot_import('shop_product_id', 'P', 'ARR');
		}
		if (empty($product_ids)) {
            $errorMsg = $L['shop']['cart_error_no_product_ids'];
			return false;
		}

        $quantity = cot_import('quantity', 'P', 'ARR');
        $shop_category_id = cot_import('shop_category_id', 'P', 'ARR');
        
        //Iterate through the prod_id's and perform an add to cart for each one
		foreach ($product_ids as $p_key => $product_id) {
            $product = ShopProduct::getById($product_id);
            if (!$product){
               // cot_error($L['shop']['product_not_found']);
                $errorMsg = $L['shop']['product_not_found'];
                return false;
            }
            
            // trying to save some space in the session
            // Не надо хранить в корзине подробное описание товара
            unset($product['page_text']);
            unset($product['page_count']);
            if (isset($product['page_rating'])) unset($product['page_rating']);
            if (isset($product['page_filecount'])) unset($product['page_filecount']);
            unset($product['_price_created_on']);
            unset($product['_price_created_by']);
            unset($product['_price_updated_on']);
            unset($product['_price_updated_by']);
            unset($product['_mf_name']);
            unset($product['_mf_desc']);
            unset($product['_mf_alias']);

			// Check if we have a product
			if ($product) {
                $allowDecQuantity = (bool)$product['page_'.$cfg["shop"]['pextf_allow_decimal_quantity']];
                if ($allowDecQuantity){
                    // Дробное кол-во заказа
                    $quantityPost = trim(str_replace(',','.', $quantity[$p_key]));
                    $quantityPost = (float)$quantityPost;
                }else{
                    $quantityPost = (int)$quantity[$p_key];
                }
                // TODO может и не надо
				if(!empty( $shop_category_id[$p_key])){
					$shop_category_idPost = (int) $shop_category_id[$p_key];
                    // Это точно пока не надо. Мож в будущем для мультикатегорий товара
					//$product['shop_category_id'] = $shop_category_idPost;
				}

				$productKey = $product['page_id'];
				// INDEX NOT FOUND IN JSON HERE
				// changed name field you know exactly was this is
                // todo доделать и проверить
				if (isset($post['customPrice'])) {
					$product->customPrices = $post['customPrice'];
					if (isset($post['customPlugin'])) $product->customPlugin = json_encode($post['customPlugin']);
                    
					$productKey .= '::';
					foreach ($product->customPrices as $customPrice) {
						foreach ($customPrice as $customId => $custom_fieldId) {

							if ( is_array($custom_fieldId) ) {
								foreach ($custom_fieldId as $userfieldId => $userfield) {
									$productKey .= $customId . ':' . $userfieldId . ';';
									$product->userfield[$customId . '-' . $userfieldId] = $userfield;
								}
							} else {
								$productKey .= $customId . ':' . $custom_fieldId . ';';
							}

						}
					}

				}

                // TODO Hook для добавления в корзину

                // Если товар уже есть в корзине
				if (array_key_exists($productKey, $this->products) && (empty($product->customPlugin)) ) {

					$errorMsg = $L['shop']['cart_product_updated'];
					$totalQuantity = $this->products[$productKey]['_quantity'] + $quantityPost;
					if ($this->checkForQuantities($product, $totalQuantity ,$errorMsg)) {
						$this->products[$productKey]['_quantity'] = $totalQuantity;
					} else {
						continue;
					}
                // Если товара в корзине нет
				}  else {
					if ( !empty($product->customPlugin)) {
						$productKey .= count($this->products);
						//print_r($product);
					}
					if ($this->checkForQuantities($product, $quantityPost, $errorMsg)) {
						$this->products[$productKey] = $product;
						$this->products[$productKey]['_quantity'] = $quantityPost;
					} else {
						// PRODUCT OUT OF STOCK
						continue;
					}
				}
				$success = true;
			} else {
				$errorMsg = $L['shop']['product_not_found'];
				return false;
			}
		}
		if ($success== false) return false ;
		// End Iteration through Prod id's

        // Добавили товар. Нужно зайти в корзину и все проверить
        $this->_inCheckOut = false;
        $this->_dataValidated = false;

		$this->save();

        // Для отладки можно очистить корзину
        //$this->removeCartFromSession();
		return true;
	}

    /**
     * Update a product in quantity the cart
     *
     * @param int $product_id
     * @param float $quantity
     * @access public
     * @return boolean
     */
	public function updateQuantity($product_id = 0, $quantity = 0.0) {

		//		foreach($product_ids as $product_id){
		$updated = false;

		if (array_key_exists($product_id, $this->products)) {
			if (!empty($quantity) && $quantity > 0) {
				if ($this->checkForQuantities($this->products[$product_id], $quantity)) {
					$this->products[$product_id]['_quantity'] = $quantity;
                    $this->save();
					$updated = true;
				}
			} else {
				//Todo when quantity is 0,  the product should be removed, maybe necessary to gather in array and execute delete func
				$this->remove($product_id);
				$updated = true;
			}
		}
		//		}
		if ($updated) return true;
		return false;
	}
    
    /**
	 * Remove a product from the cart
	 *
	 * @param int $product_id 
	 * @access public
	 */
	public function remove($product_id) {
		$product_id = (int)$product_id;
		if (empty($product_id)) return false;

		unset($this->products[$product_id]);

		$this->save();
		return true;
	}
    
    /**
	 * Checks if the quantity is correct
	 */
	public function checkForQuantities($product, &$quantity = 0, &$errorMsg ='') {
        global $cfg, $L;
        
		$stockhandle = $cfg["shop"]['stockhandle'];

		/* Check for a valid quantity */
		if (!is_numeric( $quantity)) {
			$errorMsg = $L['shop']['cart_error_no_valid_quantity'];
			$this->setError($errorMsg);
			return false;
		}
		/* Check for negative quantity */
		//if ($quantity < 1) {
        if ($quantity < 0) {
            $errorMsg = $L['shop']['cart_error_no_valid_quantity'];
			$this->setError($errorMsg);
			return false;
		}
		// Check to see if checking stock quantity
		if ($stockhandle!='none' && $stockhandle!='risetime') {

			$productsleft = $product['page_'.$cfg['shop']['pextf_in_stock']] - $product['page_'.$cfg['shop']['pextf_ordered']];
			// TODO $productsleft = $product->product_in_stock - $product->product_ordered - $quantityincart ;
			if ($quantity > $productsleft ){
				if($productsleft>0 and $stockhandle='disableadd'){
					$quantity = $productsleft;
                    $errorMsg = sprintf($L['shop']['product_out_of_quantity'], $quantity);
					$this->setError($errorMsg);
				} else {
					$errorMsg = $L['shop']['product_out_of_stock'];
					$this->setError($errorMsg); // Private error retrieved with getError is used only by addJS, so only the latest is fine
					return false;
				}
			}
		}

		/* Check for the minimum and maximum quantities */
		$min = (float)$product['page_'.$cfg['shop']['pextf_min_order_level']];
		$max = (float)$product['page_'.$cfg['shop']['pextf_max_order_level']];

        // Продажа упаковками
        $inPack = (float)$product["page_{$cfg['shop']['pextf_in_pack']}"];
        if ($inPack > $min && $product["page_{$cfg["shop"]['pextf_order_by_pack']}"] == '1') {
            $min = $inPack;
        }

		if ($min != 0 && $quantity < $min) {
            $errorMsg = sprintf($L['shop']['cart_min_order'], $min);
			$this->setError($errorMsg);
    		return false;
		}
		if ($max != 0 && $quantity > $max) {
            $errorMsg = sprintf($L['shop']['cart_max_order'], $max);
			$this->setError($errorMsg);
			return false;
		}

        // Продажа упаковками
        if ($product["page_{$cfg["shop"]['pextf_order_by_pack']}"] == '1'){
            $tmp = $quantity % $inPack;
            if ($tmp != 0){
                $quantity = shop_nearMultiple($quantity, $inPack);
                $unit = '';
                if ($product["page_{$cfg["shop"]['pextf_unit']}"] != ''){
                    $unit = '('.$product["page_{$cfg["shop"]['pextf_unit']}"].')';
                }
                $errorMsg = sprintf($L['shop']['product_out_of_pack'], $quantity, $inPack, $unit);
                $this->setError($errorMsg);
            }
        }else{
            // Если не продаем упаковками, то проверяем шаг
            $step = (float)$product["page_{$cfg['shop']['pextf_step']}"];
            if ($step <= 0) $step = 1;
            if($product["page_{$cfg['shop']['pextf_allow_decimal_quantity']}"] != '1') $step = (int)$step;
            $msg = '';
            if ($min != 0 && $min != $step){
                $tmp = $quantity - $min;
                $newQtt = shop_nearMultiple($tmp, $step) + $min;
                $msg = sprintf($L['shop']['product_quantity_corrected_min'], $product["page_title"], $step, $min, $newQtt);
            }else{
                $newQtt = shop_nearMultiple($quantity, $step);
                $msg = sprintf($L['shop']['product_quantity_corrected'], $product["page_title"], $min, $newQtt);
            }
            if ($quantity != $newQtt){
                cot_message($msg);
                $quantity = $newQtt;
            }
        }
		return true;
	}
    
    /**
     * Оформление заказа 
     * @param bool $redirect - Redirection ON?
     */
    function checkout($redirect = true) {
        global $L;
        
		$this->checkoutData($redirect);
		if ($this->_dataValidated && $redirect) {
			//This is dangerous, we may add it as option, direclty calling the confirm is in most countries illegal and
            // can lead to confusion. 
            cot_message($L['shop']['cart_checkout_done_confirm_order']);
            cot_redirect(cot_url('shop', array('m'=>'cart'), '', true));
		}
	}
    
    /**
     * Проверка заказа перед подтверждением и формирование заказа
     */
    public function confirmDone() {
        global $L;

		$this->checkoutData();
		if ($this->_dataValidated) {
			$this->_confirmDone = true;
			return $this->confirmedOrder();
		} else {
            cot_error($L['shop']['cart_data_not_valid']);
            cot_redirect(cot_url('shop', array('m'=>'cart'), '', true));
		}
	}
    
    /**
	 * This function is called, when the order is confirmed by the shopper.
	 *
	 * Here are the last checks done by payment plugins.
	 * The mails are created and send to vendor and shopper
	 * will show the orderdone page (thank you page)
	 *
     *  @return int order Id
	 */
	protected function confirmedOrder() {
        global $cfg;
        
		//Just to prevent direct call
		if ($this->_dataValidated && $this->_confirmDone) {
			$orderModel = new Order();

			if (($orderID = $orderModel->createOrderFromCart($this)) === false) {
				cot_error('No order created');
                cot_redirect(cot_url('shop', 'm=cart'));
			}
			$this->order_id = $orderID;
			
            //$order= $orderModel->getOrder($orderID); TODO getById($orderID)
            
            /* === Hook === */
            $redirectUrl = '';
            // plgVmConfirmedOrder
            foreach (cot_getextplugins('shop.order.confirm.done') as $pl){
                include $pl;
            }
            // Если нет ошибок очищаем корзину
            // Плагин может установить свой $redirectUrl, 
            // TODO хотя плагину ничто не мешает самостоятельно средиректить 
            if (!cot_error_found()){
                $this->emptyCart();   // TODO не забыть включить!
                // Все ок. Ничего не делаем.
                return $orderID;
                //$redirectUrl = ($redirectUrl != '') ? $redirectUrl : cot_url('shop', 'm=cart');
            }else{
                $redirectUrl = ($redirectUrl != '') ? $redirectUrl : cot_url('shop', 'm=cart', '', true);
            }
            if ($redirectUrl != '') cot_redirect($redirectUrl);

		}


	}
    
    /**
	 * emptyCart
	 */
	public function emptyCart(){
        
		//We delete the old stuff
		$this->products = array();
		$this->_inCheckOut = false;
		$this->_dataValidated = false;
		$this->_confirmDone = false;
		$this->customer_comment = '';
		$this->couponCode = '';
		$this->tosAccepted = null;

		$this->save();
	}
    
    /**
     * Redirector
     * @param string $relUrl
     * @param string $redirectMsg
     * @return boolean 
     */
    private function redirecter($relUrl, $redirectMsg = ''){

		$this->_dataValidated = false;
		if($this->_redirect ){
			$this->save();
            if ($redirectMsg) cot_message($redirectMsg, 'warning');
			cot_redirect($relUrl);
		} else {
			$this->save();
			return false;
		}
	}
    
    /**
     * Данные о заказе
     * @return boolean 
     */
    private function checkoutData($redirect = true) {
        global $L, $cfg, $usr;
        
        $this->_redirect = $redirect;
        // Возможно надо безусловно $this->_inCheckOut = true;
        if ($redirect) $this->_inCheckOut = true;
        
		// Возможно нужно ввести по-умолчанию, если tosAccepted - не передано, использовать $this->tosAccepted;
        if (isset($_POST['tosAccepted'])) $this->tosAccepted = cot_import('tosAccepted', 'P', 'INT');
        //$this->tosAccepted = cot_import('tosAccepted', 'P', 'INT');
        
        // Возможно нужно ввести по-умолчанию, если customer_comment - не передано, использовать $this->customer_comment;
        if (isset($_POST['customer_comment'])) $this->customer_comment = cot_import('customer_comment', 'P', 'TXT');
        $shipto = null;
//        if (isset($_REQUEST['shipto'])){
//            $shipto = cot_import('shipto', 'P', 'INT');
//        }
//
//        // TODO FIX it
//		if (($this->selected_shipto = $shipto) !== null) {
//			JModel::addIncludePath(JPATH_VM_ADMINISTRATOR . DS . 'models');
//			$userModel = JModel::getInstance('user', 'VirtueMartModel');
//			$stData = $userModel->getUserAddressList(0, 'ST', $this->selected_shipto);
//			$this->validateUserData('ST', $stData[0]);
//		}
        $bt_as_st = cot_import('bt_as_st', 'P', 'BOL');
        if ($bt_as_st) $this->STsameAsBT = 1;
		$this->save();

		if ( count($this->products) == 0) {
			// Редирект на главную магазина
            $cat = shop_readShopCats();
            $cat = $cat[0];
            $continue_link = cot_url('page', array('c'=>$cat), '', true);
            return $this->redirecter($continue_link, $L['shop']['cart_no_product']);
		} else {
			foreach ($this->products as $product) {
				$redirectMsg = $this->checkForQuantities($product, $product['_quantity']);
				if (!$redirectMsg) {
                    // cot_error($this->getError());
                    //return $this->redirecter(cot_url('shop', array('m'=>'cart'), '', true), $redirectMsg);
                    return $this->redirecter(cot_url('shop', array('m'=>'cart'), '', true), $this->getError());
				}
			}
		}

		// Check if a minimun purchase value is set
		if (($redirectMsg = $this->checkPurchaseValue()) != null) {
            return $this->redirecter(cot_url('shop', 'm=cart', '', true) , $redirectMsg);
		}
        
		//But we check the data again to be sure
		if (empty($this->BT)) {
            $redirectMsg = '';
			return $this->redirecter(cot_url('shop', array('m'=>'user', 'a'=>'editaddress', 
                'addrtype'=> 'BT'), '', true), $redirectMsg);
		} else {
			$redirectMsg = self::validateUserData();
			if ($redirectMsg) {
                return $this->redirecter(cot_url('shop', array('m'=>'user', 'a'=>'editaddress', 
                    'addrtype'=>'BT', 'r'=>'cart'), '', true) , $redirectMsg);
			}
		}
        
        
		if($this->STsameAsBT!==0){
			$this->ST = $this->BT;
		} else {
			//Only when there is an ST data, test if all necessary fields are filled
			if (!empty($this->ST)) {
				$redirectMsg = self::validateUserData('ST');
				if ($redirectMsg) {
                    return $this->redirecter(cot_url('shop', array('m'=>'user', 'a'=>'editaddress',
                    'addrtype'=>'ST', 'r'=>'cart'), '', true) , $redirectMsg);
				}
			}
		}

		// Test Coupon
		if (!empty($this->couponCode)) {
			$prices = $this->getCartPrices();
//            if(!class_exists('Coupon')) require_once $cfg['modules_dir'].DS.'shop'.DS.'models'.DS.'coupon.php';
            $coupon = Coupon::getByCode($this->couponCode);
            $couponValid = true;
            if(self::$_triesValidateCoupon < 8){
                if(!$coupon || !$coupon->isValid($prices['salesPrice'])){
                    $couponValid = false;
                }
            } else{
                $couponValid = false;
                //$redirectMsg = JText::_('COM_VIRTUEMART_CART_COUPON_TOO_MANY_TRIES');
                $redirectMsg = $L['shop']['coupon_notfound'];
            }
            self::$_triesValidateCoupon++;
            if (!$couponValid) {
                $this->couponCode = '';
                return $this->redirecter(cot_url('shop', array('m'=>'cart'), '', true), $redirectMsg);

            }
		}

		//Test Shipment and show shipment plugin
		if (empty($this->shipmentmethod_id)) {
            return $this->redirecter(cot_url('shop', array('m'=>'cart', 'a'=>'edit_shipment'), '', true) , 
                    $L['shop']['cart_no_shipment_selected']);
		} else {
			//Add a hook here for other shipment methods, checking the data of the choosed plugin
            /* === Hook === */
            $cart = array($this); // Чтобы плагины могли работать с корзиной
            foreach (cot_getextplugins('shop.checkout.checkShipmentData') as $pl){
                include $pl;
            }
            if (cot_error_found()){
                // Missing data, ask for it (again)
                // TODO вместо $redirectMsg выводить error от cot_error_found
				return $this->redirecter(cot_url('shop', array('m'=>'cart', 'a'=>'edit_shipment'), '', true), $redirectMsg);
            }
		}
        
		//Test Payment and show payment plugin
		if (empty($this->paymentmethod_id)) {
            return $this->redirecter(cot_url('shop', array('m'=>'cart', 'a'=>'edit_payment'), '', true), 
                    $L['shop']['cart_no_payment_selected']);
		} else {
            $redirectMsg = ''; // Временно
            /* === Hook === */
            $cart = array($this); // Чтобы плагины могли работать с корзиной
            // plgVmOnCheckoutCheckDataPayment
            foreach (cot_getextplugins('shop.checkout.checkPaymentData') as $pl){
                include $pl;
            }
            if (cot_error_found()){
                // Missing data, ask for it (again)
                // TODO вместо $redirectMsg выводить error от cot_error_found
				return $this->redirecter(cot_url('shop', array('m'=>'cart', 'a'=>'edit_payment'), '', true), $redirectMsg);
            }
		}

        // Если обязательно соглашаться с правилами обслуживания, то они должны быть приняты
		if (empty($this->tosAccepted)) {
            $tosNeeded = $cfg["shop"]['agree_to_tos_onorder'] ? true : false;
            //$tosNeeded = false; // для отладки
            if (!$tosNeeded){
                foreach($cfg["shop"]['user_fields']['BT'] as $tmp){
                    if($tmp['name'] == 'agreed' && $tmp['required']) $tosNeeded = true;
                }
            }
			if($tosNeeded){
				return $this->redirecter(cot_url('shop', array('m'=>'cart'), '', true), $L['shop']['cart_please_accept_tos']);
			}
		}

		if($cfg["shop"]['oncheckout_only_registered'] && $usr['id']==0) {
			return $this->redirecter(cot_url('shop', array('m'=>'user', 'a'=>'editaddress', 
                    'addrtype'=>'BT'), '', true) , $L['shop']['cart_only_registered']);
		 }


		//Show cart and checkout data overview
		$this->_inCheckOut = false;
		$this->_dataValidated = true;

		$this->save();

		return true;
	}

    /**
     * Function Description
     *
     * @param bool $checkAutomaticSelected
     * @return array of product objects
     */
	public function getCartPrices($checkAutomaticSelected=true) {
		$calculator = calculationHelper::getInstance();

        $this->pricesUnformatted = $calculator->getCheckoutPrices($this, $checkAutomaticSelected);

        return $this->pricesUnformatted;
	}
    
    public function getDataValidated() {
		return $this->_dataValidated;
	}
    
    /**
     * Заказ в процессе оформления?
     * @return bool 
     */
    public function getInCheckOut() {
		return $this->_inCheckOut;
	}
    
    /**
	 * Set the cart in the session
	 * @access public
	 * @param array $cart the cart to store in the session
     * TODO дописать функцию
	 */
	//public function setCartIntoSession() {
    public function save() {
		$sessionCart = new stdClass();

//		$products = array();
		if ($this->products) {
			foreach($this->products as $key => &$product){
                //Important DO NOT UNSET _price
                // === TODO проверить
                unset($product['_prices']);
                unset($product->pricesUnformatted);
                unset($product['_mf_name']);
                unset($product['_mf_desc']);
                unset($product['_mf_alias']);
                unset($product['_mf_url']);

                unset($product->salesPrice);
                unset($product->basePriceWithTax);
                unset($product->subtotal);
                unset($product->subtotal_with_tax);
                unset($product->subtotal_tax_amount);
                unset($product->subtotal_discount);

                unset($product['_price_vdate']);
                unset($product['_price_edate']);

//                unset($product['_add_prices']);

			}
		}
		// 		$sessionCart->products = $products;
		$sessionCart->products = $this->products;

		// 		echo '<pre>'.print_r($products,1).'</pre>';die;
		$sessionCart->vendorId	 				  = $this->vendorId;
		$sessionCart->lastVisitedCategory	 	  = $this->lastVisitedCategory;
		$sessionCart->shipmentmethod_id           = $this->shipmentmethod_id;
		$sessionCart->paymentmethod_id            = $this->paymentmethod_id;
		$sessionCart->automaticSelectedShipment   = $this->automaticSelectedShipment;
		$sessionCart->automaticSelectedPayment 	  = $this->automaticSelectedPayment;
        $sessionCart->order_number 		          = $this->order_number;
		$sessionCart->BT 						  = $this->BT;
		$sessionCart->ST 						  = $this->ST;
		$sessionCart->tosAccepted 				  = $this->tosAccepted;
		$sessionCart->customer_comment 			  = base64_encode($this->customer_comment);
		$sessionCart->couponCode 				  = $this->couponCode;
		$sessionCart->cartData 					  = $this->cartData;
		$sessionCart->lists 					  = $this->lists;
		// 		$sessionCart->user 									= $this->user;
//		$sessionCart->prices 								= $this->prices;
		$sessionCart->pricesUnformatted			  = $this->pricesUnformatted;
		$sessionCart->pricesCurrency			  = $this->pricesCurrency;
		$sessionCart->paymentCurrency			  = $this->paymentCurrency;

		//private variables
		$sessionCart->_inCheckOut 				  = $this->_inCheckOut;
		$sessionCart->_dataValidated			  = $this->_dataValidated;
		$sessionCart->_confirmDone				  = $this->_confirmDone;
		$sessionCart->STsameAsBT				  = $this->STsameAsBT;

        if(!empty($sessionCart->pricesUnformatted)){
            foreach($sessionCart->pricesUnformatted as &$prices){
                if(is_array($prices)){
                    foreach($prices as &$price){
                        if(!is_array($price)){
                            $price = (string)$price;
                        }
                    }
                } else {
                    $prices = (string)$prices;
                }
            }
        }
        $_SESSION['__shop']['cart'] = serialize($sessionCart);
	}
    
    /**
	 * Remove the cart from the session
	 * @access public
	 */
	public function removeCartFromSession() {
		$_SESSION['__shop']['cart'] = 0;
	}

	public function setDataValidation($valid=false) {
		$this->_dataValidated = $valid;
	}
    
    /**
	 * CheckAutomaticSelectedShipment
	 * If only one shipment is available for this amount, then automatically select it
	 * @todo hook, чтобы плагин доставки проверял возможность использования его при заказе,
	 */
	function CheckAutomaticSelectedShipment($cart_prices, $checkAutomaticSelected ) {
        global $cfg, $usr;

		$nbShipment = 0;
		$shipmentmethod_id = 0;


		if ($cfg["shop"]['automatic_shipment'] && $checkAutomaticSelected) {
            if (!class_exists('ShipmentMethod')) require_once($cfg['modules_dir'].DS.'shop'.DS.'models'.DS.'shipmentmethod.php');
            $ships = ShipmentMethod::getListByUserId($usr['id'], $this->vendorId);

            /* == Hook == */
            // см $returnValues = $dispatcher->trigger('plgVmOnCheckAutomaticSelectedShipment', array(  $this,$cart_prices, &$shipCounter));

            $nbShipment = count($ships);
            $tmp = array_shift($ships);
            $shipmentmethod_id = (int)$tmp->shipm_id;

			if ($nbShipment==1 && $shipmentmethod_id) {
				$this->automaticSelectedShipment=true;
				$this->setShipmentMethod($shipmentmethod_id);
				return true;
			} else {
				$this->automaticSelectedShipment=false;
				$this->save();
				return false;
			}
		} else {
			return false;
		}


	}

	/**
	 * CheckAutomaticSelectedPayment
	 * If only one payment is available for this amount, then automatically select it
     * @todo hook, чтобы плагин доставки проверял возможность использования его при заказе,
	 */
	function CheckAutomaticSelectedPayment($cart_prices,  $checkAutomaticSelected = true) {
        global $cfg, $usr;
        
		$nbPayment = 0;
		$paymentmethod_id=0;

		if ($cfg["shop"]['automatic_payment'] && $checkAutomaticSelected ) {
            if (!class_exists('PaymentMethod')) require_once($cfg['modules_dir'].DS.'shop'.DS.'models'.DS.'paymentmethod.php');
            $pays = PaymentMethod::getListByUserId($usr['id'], $this->vendorId);

            /* == Hook == */
            // см $returnValues = $dispatcher->trigger('plgVmOnCheckAutomaticSelectedPayment', array( $this, $cart_prices, &$paymentCounter));

            $nbPayment = count($pays);
            $tmp = array_shift($pays);
            $paymentmethod_id = (int)$tmp->paym_id;

			if ($nbPayment==1 && $paymentmethod_id) {
                $this->automaticSelectedPayment=true;
				$this->setPaymentMethod($paymentmethod_id);
				return true;
			} else {
				$this->automaticSelectedPayment=false;
				$this->save();
				return false;
			}
		} else {
			return false;
		}

	}

    /**
     * prepare display of cart
     * @param bool $checkAutomaticSelected
     * @return array|bool
     */
	public function prepareCartData($checkAutomaticSelected = true){

		/* Get the products for the cart */
		$product_prices = $this->getCartPrices($checkAutomaticSelected);

		if (empty($product_prices)) return false;

		$currency = CurrencyDisplay::getInstance();
        $calculator = calculationHelper::getInstance();

        $this->pricesCurrency = $currency->getCurrencyDisplay();

        // TODO возможно hook для плагинов влюяющих на валюту оплаты

        $cartData = $calculator->getCartData();

        return $cartData ;
	}
    
    /**
     * Render the code for Ajax Cart
     * @return array
     */
	function prepareAjaxData(){
        global $cfg;

		//$vars["zone_qty"] = 0;
		$this->prepareCartData(false);
		$weight_total = 0;
		$weight_subtotal = 0;

        $data = new stdClass();
		$data->products = array();
		$data->totalProduct = 0;
        $currency = CurrencyDisplay::getInstance();
		$i=0;
		foreach ($this->products as $priceKey => $product){

			//$vars["zone_qty"] += $product["_quantity"];
			//$category_id = $this->getCardCategoryId($product->page_id);
            $category_id = $product["page_cat"];
			// Create product URL
            $product['page_alias'] = trim($product['page_alias']);
            $url = array('c' => $category_id);
            if ($product['page_alias'] != ''){
                $url['al'] = $product['page_alias'];
            }else{
                $url['id'] = $product['page_id'];
            }
			$url = cot_url('page', $url);
            // /Create product URL

			// @todo Add variants
            // @TODO i18n
			$data->products[$i]['product_name'] = '<a href="'.$url.'">'.$product['page_title'].'</a>';
            $data->products[$i]['page_title'] = $product['page_title'];
            // Для вывода в AJAX без '_'
            $data->products[$i]['url'] = $url;

			// Add the variants
//			if (!is_numeric($priceKey)) {
//				if(!class_exists('VirtueMartModelCustomfields'))require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'customfields.php');
//				//  custom product fields display for cart
//				$this->data->products[$i]['attributes'] = VirtueMartModelCustomfields::CustomsFieldCartModDisplay($priceKey,$product);
//
//			}
			$data->products[$i]['product_sku'] = $product["page_{$cfg["shop"]['pextf_sku']}"];

			//** @todo WEIGHT CALCULATION
			//$weight_subtotal = vmShipmentMethod::get_weight($product["virtuemart_product_id"]) * $product['_quantity'];
			//$weight_total += $weight_subtotal;


			// product Price total for ajax cart
            $data->products[$i]['pricesUnformatted'] = $this->pricesUnformatted[$priceKey]['subtotal_with_tax'];
            $data->products[$i]['prices'] = $currency->priceDisplay( $this->pricesUnformatted[$priceKey]['subtotal_with_tax'] );

			// other possible option to use for display
			$data->products[$i]['subtotal'] = $this->pricesUnformatted[$priceKey]['subtotal'];
			$data->products[$i]['subtotal_tax_amount'] = $this->pricesUnformatted[$priceKey]['subtotal_tax_amount'];
			$data->products[$i]['subtotal_discount'] = $this->pricesUnformatted[$priceKey]['subtotal_discount'];
			$data->products[$i]['subtotal_with_tax'] = $this->pricesUnformatted[$priceKey]['subtotal_with_tax'];

			// UPDATE CART / DELETE FROM CART
            $data->products[$i]['quantity'] = (float)$product['_quantity'];
			$data->totalProduct += (float)$product['_quantity'] ;

			$i++;
		}
        $data->billTotal = $currency->priceDisplay( $this->pricesUnformatted['billTotal'] );
        $data->dataValidated = $this->_dataValidated;
		return $data ;
	}
    
    
    /**
	 * Prepare the datas for cart/mail views
	 * set product, price, user, adress and vendor as Object
	 */
	function prepareCartViewData(){
		//$data = new stdClass();
		/* Get the products for the cart */
		$this->cartData = $this->prepareCartData();

//		$this->prepareCartPrice( $this->pricesUnformatted ) ;
        
        // реквизиты и адреса адрес доставки...
		$this->prepareAddressDataInCart();
        $this->prepareAddressDataInCart('ST');
        
        // TODO получить продавца deprecated
		//$this->prepareVendor();
	}


    /**
     * Пока deprected Не храним больше цены в самой корзине
     * @deprecated
     * @param $prices
     */
    private function prepareCartPrice( $prices ){

		foreach ($this->products as $cart_item_id => $product){

            $tmp = array('c' => $product['page_cat']);
            if (isset($product['page_alias']) && $product['page_alias'] != ''){
                $tmp['al'] = $product['page_alias'];
            }else{
                $tmp['id'] = $product['page_id'];
            }
            $product['_url'] = cot_url('page', $tmp);


//			if(!empty($product->customfieldsCart)){
//				if(!class_exists('VirtueMartModelCustomfields'))require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'customfields.php');
//				$product->customfields = VirtueMartModelCustomfields::CustomsFieldCartDisplay($cart_item_id,$product);
//			} else {
//				$product->customfields ='';
//			}

		}
	}

	function prepareAddressDataInCart($type='BT',$new = false){
        global $cfg;
        
		$data = (array)$this->$type;
		if($new){
			$data = null;
		}

        $preFix = '';

		$addresstype = $type.'address';
        // Реквизиты покупателя берем из профиля пользователя
        // Поля заполняемые пользователями
        $this->$addresstype = Userfields::getFieldsData($data, $type, $preFix);
	}

    /**
     * Set Coupon Code
     * @param string $code
     */
    public function setCouponCode($code) {
        $this->couponCode = $code;
        $this->save();
    }

    /**
	 * Check the selected shipment data and store the info in the cart
	 * @param integer $shipment_id Shipment ID taken from the form data
	 */
	public function setShipmentMethod($shipment_id) {
	    $this->shipmentmethod_id = $shipment_id;
	    $this->save();

	}
    
    /**
     * Set selected Payment Method
     * @param int $payment_id 
     */
	public function setPaymentMethod($payment_id) {
		$this->paymentmethod_id = $payment_id;
		$this->save();
	}

    /**
	 * Set non product info in object
     * @todo дописать метод
	 */
	public function setPreferred() {
        global $usr, $cfg;

        // обработка адреса пользователя
		if (empty($this->BT) || (!empty($this->BT) && count($this->BT) <=1) ) {
            $userInfo = array();
			foreach($cfg["shop"]['user_fields']['BT'] as $field){
                $userInfo[$field['field_name']] = $usr["profile"]['user_'.$field['field_name']];
                //var_dump($usr);
            }
            
            $this->saveAddress($userInfo, 'BT', false);
		}

		if (empty($this->shipmentmethod_id) && !empty($user->shipmentmethod_id)) {
			$this->shipmentmethod_id = $user->shipmentmethod_id;
		}

		if (empty($this->paymentmethod_id) && !empty($user->paymentmethod_id)) {
			$this->paymentmethod_id = $user->paymentmethod_id;
		}

		//$this->tosAccepted is due session stuff always set to 0, so testing for null does not work
        // TODO FIX it
		if((!empty($user->agreed) || !empty($this->BT['agreed'])) && !$cfg["shop"]['agree_to_tos_onorder'] ){
				$this->tosAccepted = 1;
		}
	}
    
    /**
     * Сохранить адрес в сессии корзины
     * @param type $data
     * @param string $type 'BT' или 'ST'
     * @param bool $putIntoSession - сразу сохранить в сессию
     */
    function saveAddress($data, $type, $putIntoSession = true) {
        global $cfg, $usr;
        
        $prepareUserFields = Userfields::getUserFields($type);

        $prefix = '';
		//STaddress may be obsolete
		if ($type == 'STaddress' || $type =='ST') {
			$prefix = 'ui_';

		} else { // BT
			if(!empty($data['agreed'])){
				$this->tosAccepted = $data['agreed'];
			} else if (!empty($data['agreed'])){
				$this->tosAccepted = $data['agreed'];
			}

			if(empty($data['email']) && $usr['id'] > 0){
                $data['email'] = $usr["profile"]["user_email"];
			}
		}
		$address = array();
        if(!is_array($data)) $data = (array)$data;

        if ($type =='ST') {
           if(!empty($data[$prefix.'id'])){
               $address['ui_id'] = $data[$prefix.'id'];
           }elseif(!empty($data['id'])){
               $address['ui_id'] = $data['id'];
           }
        }

        foreach ($prepareUserFields as $fld) {
            if(!empty($fld['field_name'])){
                $name = $fld['field_name'];
                if(!empty($data[$prefix.$name])){
                    $address[$name] = $data[$prefix.$name];
                }elseif(!empty($data[$name])){
                    $address[$name] = $data[$name];
                }
            }
        }
		//dont store passwords in the session
		unset($address['password']);

		$this->{$type} = $address;

		if($putIntoSession){
			$this->save();
		}

	}
    
    /**
	 * Check if a minimum purchase value for this order has been set, and if so, if the current
	 * value is equal or hight than that value.
	 * @return An error message when a minimum value was set that was not eached, null otherwise
     * @todo - это заглушка. Дописать, когда будет введен продавец
	 */
	private function checkPurchaseValue() {
        global $cfg;
//		if (!class_exists('VirtueMartModelVendor'))
//		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
        if (!class_exists('Vendor')){
            require_once($cfg['modules_dir'].DS.'shop'.DS.'models'.DS.'vendor.php');
        }
        // TODO обработка продавца
//		$vendor = new Vendor();
//		$vendor->setId($this->vendorId);
//		$store = $vendor->getVendor();
        $store = new stdClass();    // временно
		if ($store->vendor_min_pov > 0) {
			$prices = $this->getCartPrices();
			if ($prices['salesPrice'] < $store->vendor_min_pov) {
				if (!class_exists('CurrencyDisplay'))
				require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
				$currency = CurrencyDisplay::getInstance();
				$minValue = $currency->priceDisplay($min);
				return JText::sprintf('COM_VIRTUEMART_CART_MIN_PURCHASE', $currency->priceDisplay($store->vendor_min_pov));
			}
		}
		return null;
	}
    
    
    /**
	 * Test userdata if valid
	 *
	 * @param String if BT or ST
	 * @param Object If given, an object with data address data that must be formatted to an array
	 * @return redirectMsg, if there is a redirectMsg, the redirect should be executed after
     * @todo Test ST
     * @todo вынести в модель пользователя
	 */
	private function validateUserData($type='BT', $obj = null) {
        global $cfg, $L;

//
//		$neededFields = $userFieldsModel->getUserFields(
        $neededFields = $cfg["shop"]['user_fields'][$type];
//		$fieldtype
//		, array('required' => true, 'delimiters' => true, 'captcha' => true, 'system' => false)
//		, array('delimiter_userinfo', 'name','username', 'password', 'password2', 'address_type_name', 'address_type', 'user_is_vendor', 'agreed'));

		$redirectMsg = false;

        // Пока ST не тестируем
        if ($type == 'ST') return $redirectMsg;

		$i = 0 ;

		foreach ($neededFields as $field) {
            // Галочку "Я согласен с условиями обслуживания пропускаем"
            if ($field['name'] == $cfg["shop"]['uextf_agreed'] || $field['name'] == 'agreed') continue;
            
            // TODO разобраться с 'state_id'
			if($field['required'] && empty($this->{$type}[$field['name']]) && $field['name'] != 'state_id'){
                //$fTitle = '';
                // TODO заполнить название поля
				//$redirectMsg = sprintf($L['shop']['missing_value_for_field'], $field['title']);
                $redirectMsg = sprintf($L['shop']['missing_value_for_field'], $field['name']); // временно
				$i++;
				//more than four fields missing, this is not a normal error (should be catche by js anyway, so show the address again.
				if($i>2 && $type=='BT'){
					$redirectMsg = $L['shop']['checkout_please_enter_address'];
				}
			}

			if ($obj !== null && is_array($this->{$type})) {
				$this->{$type}[$field['name']] = $obj->{$field['name']};
			}

			//This is a special test for the state_id. There is the speciality that the state_id could be 0 but is valid.
			if ($field['name'] == 'state_id') {
				//if (!class_exists('VirtueMartModelState')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'state.php');
				if(!empty($this->{$type}['country']) && !empty($this->{$type}['state_id']) ){
					if (!$msg = VirtueMartModelState::testStateCountry($this->{$type}['virtuemart_country_id'], $this->{$type}['virtuemart_state_id'])) {
						$redirectMsg = $msg;
					}
				}

			}
		}

		return $redirectMsg;
	}
    
    /**
	 * Set the last error that occured.
	 * This is used on error to pass back to the cart when addJS() is invoked.
	 * @param string $txt Error message
	 */
	private function setError($txt) {
		$this->_lastError = $txt;
	}

	/**
	 * Retrieve the last error message
	 * @return string The last error message that occured
	 */
	public function getError() {
		return ($this->_lastError);
	}
    
}
