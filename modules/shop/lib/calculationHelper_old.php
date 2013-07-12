<?php

/**
 * Calculation helper class
 *
 * This class provides the functions for the calculations
 *
 * @package shop
 * @subpackage Helpers
 * @author Alex
 */
defined('COT_CODE') or die('Wrong URL.');

class calculationHelper_old {

	private $_db;
    /**
     * Группы пользователя (покупателя)
     * @var array
     */
	private $_shopperGroupId;
	private $_cats;
    /**
     * Текущая дата в формате 'Y-m-d H:i:s'
     * @var string
     */
	private $_now;
    
    /**
     * The null or zero representation of a timestamp for the database driver.
     * @var string
     */
	private $_nullDate;
	//	private $_currency;
	private $_debug;

	private $_deliveryCountry;
	private $_deliveryState;
	private $_currencyDisplay;
    /**
     * @var ShopCart
     */
    private $_cart = null;
	private $_cartPrices;
	private $_cartData;

	public $_amount;

//	public $override = 0;   // Deprecated и проверить getProductPrices
	public $productVendorId;
	public $productCurrency;
	public $product_tax_id = 0;
	public $product_discount_id = 0;
	public $product_marge_id = 0;
	public $vendorCurrency = 0;
	private $exchangeRateVendor = 0;
	private $exchangeRateShopper = 0;
    //private $_internalDigits = 6;
    /**
     * @var int Количество знаков для округления, может и не стоит ставить 2
     *      tandard round function, we round every number with 6 fractionnumbers
     *      We need at least 4 to calculate something like 9.25% => 0.0925
     */
    private $_internalDigits = 2;
    private $_revert = false;
	static $_instance;

	//	public $basePrice;		//simular to costprice, basePrice is calculated in the shopcurrency
	//	public $salesPrice;		//end Price in the product currency
	//	public $discountedPrice;  //amount of effecting discount
	//	public $salesPriceCurrency;
	//	public $discountAmount;

	/** 
	 * Constructor,... sets the actual date and current currency
     * Одиночка
	 */
	private function __construct() {
        global $db, $sys, $cfg;
		$this->_db = $db;
        
		//$now = $sys['now'];
		$this->_now = date('Y-m-d H:i:s', $sys['now']);
		$this->_nullDate = date('Y-m-d H:i:s', 0);

		//Attention, this is set to the mainvendor atm.
		//This means also that atm for multivendor, every vendor must use the shopcurrency as default
		//         $this->vendorCurrency = 1;
		$this->productVendorId = 1;

		if (!class_exists('CurrencyDisplay')) require($cfg['modules_dir'].DS.'shop'.DS.'lib'.DS.'currencydisplay.php');
        
		$this->_currencyDisplay = CurrencyDisplay::getInstance();
		$this->_debug = false;

		if(!empty($this->_currencyDisplay->_vendorCurrency)){
			$this->vendorCurrency = $this->_currencyDisplay->_vendorCurrency;
        // TODO FIX it multix
		}elseif($cfg["shop"]['multix'] != 0){
            $vendorId = 1;
            $this->vendorCurrency = Vendor::getVendorCurrencyId($vendorId);
		}

		$this->setShopperGroupIds();

		$this->setVendorId($this->productVendorId);

        $this->rules['Marge'] = array();
        $this->rules['Tax'] 	= array();
        $this->rules['VatTax'] 	= array();
        $this->rules['DBTax'] = array();
        $this->rules['DATax'] = array();
	}

    /**
     * Получить текущий экземпляр calculationHelper
     * @return calculationHelper
     */
	public function getInstance() {
        global $sys;

		if (!is_object(self::$_instance)) {
			self::$_instance = new calculationHelper();
		} else {
			//$this->_now = date('Y-m-d H:i:s', $sys['now']);
            self::$_instance->_now = date('Y-m-d H:i:s', $sys['now']);
		}
		return self::$_instance;
	}

	public function setVendorCurrency($id) {
		$this->vendorCurrency = $id;
	}
    
    public function setVendorId($id){
         global $db_shop_calcs;
         
         $this->productVendorId = $id;
		 if(empty($this->allrules[$this->productVendorId])){
            $epoints = array('Marge','Tax','VatTax','DBTax','DATax');
			$this->allrules[$this->productVendorId] = array();
			$this->allrules[$this->productVendorId]['Marge'] = array();
			$this->allrules[$this->productVendorId]['Tax'] 	= array();
            $this->allrules[$this->productVendorId]['VatTax'] 	= array();
			$this->allrules[$this->productVendorId]['DBTax'] = array();
			$this->allrules[$this->productVendorId]['DATax'] = array();
            
			$q = "SELECT * FROM $db_shop_calcs 
                WHERE `calc_kind` IN ('".implode("','",$epoints)."')
					AND `calc_published`=1
					AND (`vendor_id`={$this->productVendorId} OR `calc_shared`=1 )
					AND ( calc_publish_up = ".$this->_db->quote($this->_nullDate) ." 
                                                    OR calc_publish_up <= ".$this->_db->quote($this->_now)." )
					AND ( calc_publish_down = ".$this->_db->quote($this->_nullDate)." 
                                                    OR calc_publish_down>=".$this->_db->quote($this->_now)." ) ";

            $sql = $this->_db->query($q);
			$allrules = $sql->fetchAll();
			foreach ($allrules as $rule){
				$this->allrules[$this->productVendorId][$rule["calc_kind"]][] = $rule;
			}
		}

	}
    
	public function getCartPrices() {
		return $this->_cartPrices;
	}

	public function getCartData() {
		return $this->_cartData;
	}
    
    /**
     * Получить группы текущего пользователя
     * @return array
     */
    public function getShopperGroupIds(){
        return $this->_shopperGroupId;
    }

    /**
     * Заполнить группы пользователя
     * @access public т.к. нужно расчитывать прайсы например админу для других пользователей.
     *    Использовать извне этот метод с Осторожностью
     * @param array|int $shopperGroupIds
     * @param int $vendorId
     * @todo почистить код
     *
     */
//	private function setShopperGroupIds($shopperGroupIds=0, $vendorId=1) {
    public function setShopperGroupIds($shopperGroupIds=0, $vendorId=1) {
        global $usr, $db_groups_users;
        
		if (!empty($shopperGroupIds)) {
            if (is_array($shopperGroupIds)){
                $this->_shopperGroupId = $shopperGroupIds;
            }else{
                $this->_shopperGroupId = array((int)$shopperGroupIds);
            }
		} else {
            if ($usr['id'] > 0){
                // Получить группы пользователя
                $tmp1 = $this->_db->query("SELECT gru_groupid FROM $db_groups_users WHERE gru_userid={$usr['id']}")
                    ->fetchAll(PDO::FETCH_COLUMN);
				$this->_shopperGroupId = $tmp1;  
			}elseif (empty($this->_shopperGroupId)) {
				//We just define the shoppergroup with id = 1 to guest default shoppergroup
				$this->_shopperGroupId[] = COT_GROUP_GUESTS;
			}
		}
	}

	private function setCountryState($cart = 0) {
        global $cfg;
		if (defined('COT_ADMIN') && COT_ADMIN == true) return;

		if (empty($cart)) {
			$cart = ShopCart::getInstance();
		}
		$this->_cart = $cart;

		if (!empty($this->_cart->ST['country_id'])) {
			$this->_deliveryCountry = $this->_cart->ST['country_id'];
		} else if (!empty($this->_cart->BT['country_id'])) {
			$this->_deliveryCountry = $this->_cart->BT['country_id'];
		}

		if (!empty($this->_cart->ST['state_id'])) {
			$this->_deliveryState = $this->_cart->ST['state_id'];
		} else if (!empty($cart->BT['state_id'])) {
			$this->_deliveryState = $this->_cart->BT['state_id'];
		}
	}

	/** 
	 * function to start the calculation, here it is for the product
     * 
	 * The function first gathers the information of the product (maybe better done with using the model)
	 * After that the function gatherEffectingRulesForProductPrice writes the queries and gets the ids of the rules which affect the product
	 * The function executeCalculation makes the actual calculation according to the rules
	 *
	 * 
	 * @param OrderItem|Product|int $product 	Product or the Id of the product
	 * @param int $catIds 		When the category is already determined, then it makes sense to pass it, if not the function does it for you
	 * @return array $prices	An array of the prices
	 * 							'basePrice'  		basePrice calculated in the shopcurrency
	 * 							'basePriceWithTax'	basePrice with Tax
	 * 							'discountedPrice'	before Tax
	 * 							'priceWithoutTax'	price Without Tax but with calculated discounts AFTER Tax. 
     *                                          So it just shows how much the shopper saves, regardless which kind
     *                                          of tax
	 * 							'discountAmount'	the "you save X money"
	 * 							'salesPrice'		The final price, with all kind of discounts and Tax, except stuff that is only in the checkout
	 *
	 */
    // todo test this
	public function getProductPrices ($product, $catIds=0, $variant=0.0, $amount=0, $ignoreAmount=true,
            $currencydisplay=true) {
        
        global $sys, $cfg;
		//Todo check for shoppergroups

		$costPrice = 0;

        if(is_int($product) && $product > 0)  $product = Product::getById($product);

		//We already have the product array, no need for extra sql
		if ($product instanceof OrderItem || $product instanceof Product) {
			$costPrice = isset($product->price['price'])? $product->price['price'] : 0;
			$this->productCurrency = isset($product->price['curr_id'])? $product->price['curr_id'] : 0;
			$override = isset($product->price['override']) ? $product->price['override'] : 0;
			$product_override_price = isset($product->price['override_price']) ?
                $product->price['override_price'] : 0;
			$this->product_tax_id = isset($product->price['tax_id'])? $product->price['tax_id'] : 0;
			$this->product_discount_id = isset($product->price['discount_id'])? $product->price['discount_id'] : 0;
			$this->productVendorId = isset($product->vendor_id)? $product->vendor_id : 1;
			if (empty($this->productVendorId)) {
				$this->productVendorId = 1;
			}
            // TODO multi cat
            $this->_cats = array($product->page_cat);
            $this->_product = $product;
            
		} //Use it as productId
		else {
            // Получить товар
            cot_error('No product given to getProductPrices');
		}

		if($cfg["shop"]['multix'] != 0 && empty($this->vendorCurrency )){
			$this->vendorCurrency = Vendor::getCurrencyId($this->productVendorId);
		}

		if (!empty($amount)) {
			$this->_amount = $amount;
		}
        
		$this->setCountryState($this->_cart);

        //For Profit, margin, and so on
        $this->rules['Marge'] = $this->gatherEffectingRulesForProductPrice('Marge', $this->product_marge_id);

        // Себестоимость
        $prices['costPrice'] = $costPrice;
        $basePriceShopCurrency = $this->roundInternal($this->_currencyDisplay->convertCurrencyTo((int)$this->productCurrency,
                $costPrice, true));
        // Себестоимость в валюте продавца
        $prices['costPriceShopCurrency'] = $basePriceShopCurrency;
        $basePriceMargin = $this->roundInternal($this->executeCalculation($this->rules['Marge'], $basePriceShopCurrency));
        // Базовая цена
        $this->basePrice = $basePriceShopCurrency = $prices['basePrice'] = !empty($basePriceMargin) ?
                $basePriceMargin : $basePriceShopCurrency;

        $this->rules['Tax'] = $this->gatherEffectingRulesForProductPrice('Tax', $this->product_tax_id);
        $this->rules['VatTax'] = $this->gatherEffectingRulesForProductPrice('VatTax', $this->product_tax_id);
        $this->rules['DBTax'] = $this->gatherEffectingRulesForProductPrice('DBTax', $this->product_discount_id);
        $this->rules['DATax'] = $this->gatherEffectingRulesForProductPrice('DATax', $this->product_discount_id);


        if (!empty($variant)) {
            $basePriceShopCurrency = $basePriceShopCurrency + doubleval($variant);
            $prices['basePrice'] = $prices['basePriceVariant'] = $basePriceShopCurrency;
        }
        if (empty($prices['basePrice'])) {
            return $this->fillVoidPrices($prices);
        }
        if (empty($prices['basePriceVariant'])) {
            $prices['basePriceVariant'] = $prices['basePrice'];
        }

        $prices['basePriceWithTax'] = $this->roundInternal($this->executeCalculation($this->rules['Tax'], $prices['basePrice'], true),
            'basePriceWithTax');
        if(!empty($this->rules['VatTax'])){
            $price = !empty($prices['basePriceWithTax']) ? $prices['basePriceWithTax'] : $prices['basePrice'];
            $prices['basePriceWithTax'] = $this->roundInternal($this->executeCalculation($this->rules['VatTax'],
                $price,true),'basePriceWithTax');
        }
        $prices['discountedPriceWithoutTax'] = $this->roundInternal($this->executeCalculation($this->rules['DBTax'],
            $prices['basePrice']),'discountedPriceWithoutTax');

        if ($override==-1) {
            $prices['discountedPriceWithoutTax'] = $product_override_price;
        }

        $priceBeforeTax = !empty($prices['discountedPriceWithoutTax']) ? $prices['discountedPriceWithoutTax'] : $prices['basePrice'];

        $prices['priceBeforeTax'] = $priceBeforeTax;
        $prices['salesPrice'] = $this->roundInternal($this->executeCalculation($this->rules['Tax'], $priceBeforeTax,
            true),'salesPrice');

        $salesPrice = !empty($prices['salesPrice']) ? $prices['salesPrice'] : $priceBeforeTax;

        $prices['taxAmount'] = $this->roundInternal($salesPrice - $priceBeforeTax);

        if(!empty($this->rules['VatTax'])){
            $prices['salesPrice'] = $this->roundInternal($this->executeCalculation($this->rules['VatTax'], $salesPrice),'salesPrice');
            $salesPrice = !empty($prices['salesPrice']) ? $prices['salesPrice'] : $salesPrice;
        }

        $prices['salesPriceWithDiscount'] = $this->roundInternal($this->executeCalculation($this->rules['DATax'],
            $salesPrice),'salesPriceWithDiscount');

        $prices['salesPrice'] = !empty($prices['salesPriceWithDiscount']) ? $prices['salesPriceWithDiscount'] :
            $salesPrice;

        $prices['salesPriceTemp'] = $salesPrice;
        //Okey, this may not the best place, but atm we handle the override price as salesPrice
        if ($override == 1) {
            // Цена конвертируется в валдюту покупателя на выводе
            $prices['salesPrice'] = $product_override_price;
        }

        // @todo Это не правильно т.к. у нас указывается сразу цена на 1 шт. Цену за укаковку мы не ставим
        if (!empty($product->in_pack)){
            $prodInPack = (double)$product->in_pack;
            if($prodInPack > 0){
                $prices['unitPrice'] = $prices['salesPrice'] / $prodInPack;
            } else {
                $prices['unitPrice'] = 0.0;
            }
        }

        if(!empty($this->rules['VatTax'])){
            $this->_revert = true;
            $prices['priceWithoutTax'] = $prices['salesPrice'] - $prices['taxAmount'];
            $afterTax = $this->roundInternal($this->executeCalculation($this->rules['VatTax'], $prices['salesPrice']),'salesPrice');

            if(!empty($afterTax)){
                $prices['taxAmount'] = $prices['salesPrice'] - $afterTax;
            }
            $this->_revert = false;
        }

        // todo Посмотреть, может именно тут нада получать дополнительные цены
        // Дополнительные цены, Перекрываем даже override!!!
        if (!empty($product->add_prices)){
            $minApr = 0;
            foreach($product->add_prices as $key => $apr) {
                // проверяем группы
                if(!empty($apr['price_groups']) && count(array_intersect($apr['price_groups'], $this->_shopperGroupId))== 0 ){
                    continue;
                }
                // Проверяем кол-во товара:
                if ($ignoreAmount && ($apr['price_quantity_start'] > 0 || $apr['price_quantity_end'] > 0)) continue;
                if (!$ignoreAmount && $this->_amount > 0){
                    if ($this->_amount < $apr['price_quantity_start']) continue;
                    if ($apr['price_quantity_end'] > 0 && $this->_amount > $apr['price_quantity_end']) continue;
                }
                // Проверяем даты
                if (!empty($apr['price_vdate']) && strtotime($apr['price_vdate']) > $sys['now']) continue;
                $eTime = (!empty($apr['price_edate'])) ? strtotime($apr['price_edate']) : 0;
                if ($eTime > 1 && strtotime($apr['price_edate']) < $sys['now']) continue;
                $minApr = ($minApr > 0 && $minApr <= $apr['price_price']) ? $minApr : $apr['price_price'];
            }
            if ($minApr > 0){
                if($prices['salesPrice'] > $minApr) $prices['salesPrice'] = $minApr;
            }
        }
        // /Дополнительные цены

		//The whole discount Amount
        $basePriceWithTax = !empty($prices['basePriceWithTax']) ? $prices['basePriceWithTax'] : $prices['basePrice'];

        //changed
        //		$prices['discountAmount'] = $this->roundInternal($basePriceWithTax - $salesPrice);
        $prices['discountAmount'] = $this->roundInternal($basePriceWithTax - $prices['salesPrice']);

        //price Without Tax but with calculated discounts AFTER Tax. So it just shows how much the shopper saves, regardless which kind of tax
        //		$prices['priceWithoutTax'] = $this->roundInternal($salesPrice - ($salesPrice - $discountedPrice));
// 		$prices['priceWithoutTax'] = $prices['salesPrice'] - $prices['taxAmount'];
        $prices['priceWithoutTax'] = $salesPrice - $prices['taxAmount'];

        $prices['variantModification'] = $variant;

        $prices['DBTax'] = array();
		foreach($this->rules['DBTax'] as $dbtax){
			$prices['DBTax'][] = array($dbtax['calc_title'],$dbtax['calc_value'],$dbtax['calc_value_mathop'],$dbtax['calc_shopper_published']);
		}

		$prices['Tax'] = array();
		foreach($this->rules['Tax'] as $tax){
			$prices['Tax'][] =  array($tax['calc_title'],$tax['calc_value'],$tax['calc_value_mathop'],$tax['calc_shopper_published']);
		}

        $prices['VatTax'] = array();
        foreach($this->rules['VatTax'] as $tax){
            $prices['VatTax'][] =  array($tax['calc_title'],$tax['calc_value'],$tax['calc_value_mathop'],$tax['calc_shopper_published']);
        }

		$prices['DATax'] = array();
		foreach($this->rules['DATax'] as $datax){
			$prices['DATax'][] =  array($datax['calc_title'],$datax['calc_value'],$datax['calc_value_mathop'],$datax['calc_shopper_published']);
		}
		return $prices;
	}

	private function fillVoidPrices() {

		if (!isset($prices['basePrice']))  $prices['basePrice'] = null;
		if (!isset($prices['basePriceVariant'])) $prices['basePriceVariant'] = null;
		if (!isset($prices['basePriceWithTax'])) $prices['basePriceWithTax'] = null;
		if (!isset($prices['discountedPriceWithoutTax'])) $prices['discountedPriceWithoutTax'] = null;
		if (!isset($prices['priceBeforeTax'])) $prices['priceBeforeTax'] = null;
		if (!isset($prices['taxAmount'])) $prices['taxAmount'] = null;
		if (!isset($prices['salesPriceWithDiscount'])) $prices['salesPriceWithDiscount'] = null;
		if (!isset($prices['salesPrice']))  $prices['salesPrice'] = null;
		if (!isset($prices['discountAmount'])) 	$prices['discountAmount'] = null;
		if (!isset($prices['priceWithoutTax'])) 	$prices['priceWithoutTax'] = null;
		if (!isset($prices['variantModification']))	$prices['variantModification'] = null;

        return $prices;
	}

    /** function to start the calculation, here it is for the invoice in the checkout
     * This function is partly implemented !
     *
     * The function calls getProductPrices for every product except it is already known (maybe changed and adjusted with product amount value
     * The single prices gets added in an array and already summed up.
     *
     * Then simular to getProductPrices first the effecting rules are determined and calculated.
     * Ah function to determine the coupon that effects the calculation is already implemented. But not completly in the calculation.
     *
     * 		Subtotal + Tax + Discount =	Total
     *
     * @param ShopCart $cart 	Shop Cart
     * @param bool $checkAutomaticSelected
     * @return array $prices		An array of the prices
     * 							'resultWithOutTax'	The summed up baseprice of all products
     * 							'resultWithTax'  	The final price of all products with their tax, discount and so on
     * 							'discountBeforeTax'	discounted price without tax which affects only the checkout (the tax of the products is in it)
     * 							'discountWithTax'	discounted price taxed
     * 							'discountAfterTax'	final result
     *
     */
	public function getCheckoutPrices($cart, $checkAutomaticSelected = true) {

		$this->_cart = $cart;

		$pricesPerId = array();
		$this->_cartPrices = array();
		$this->_cartData = array();
		$resultWithTax = 0.0;
		$resultWithOutTax = 0.0;

        // себестоимость в валюте магазина
        $this->_cartPrices['costPriceShopCurrency'] = 0;
        $this->_cartPrices['basePrice'] = 0;
        $this->_cartPrices['basePriceWithTax'] = 0;
        $this->_cartPrices['discountedPriceWithoutTax'] = 0;
        $this->_cartPrices['salesPrice'] = 0;
        $this->_cartPrices['taxAmount'] = 0;
        $this->_cartPrices['salesPriceWithDiscount'] = 0;
        $this->_cartPrices['discountAmount'] = 0;
        $this->_cartPrices['priceWithoutTax'] = 0;
        $this->_cartPrices['subTotalProducts'] = 0;
        $this->_cartData['duty'] = 1;

		$this->_cartData['payment'] = 0; //could be automatically set to a default set in the globalconfig
		$this->_cartData['paymentName'] = '';
		$cartpaymentTax = 0;

		$this->setCountryState($cart);

        $this->_amountCart = 0;
		foreach ($cart->products as $name => $product) {

			$productId = $product->prod_id;
 			
            if (empty($product->prod_quantity) || empty($product->prod_id)) {
                // todo translate it!
                cot_error('Error the quantity of the product for calculation is 0, please notify the shopowner, 
                    the product id ' . $product->prod_id);
				continue;
			}

			$variantmods = $this->parseModifier($name);
			$variantmod = $this->calculateModificators($product, $variantmods);

			$cartproductkey = $name;
            // не игнорируем кол-во товара, иначе зависимость цен от кол-ва не работает
			$product->prices = $pricesPerId[$cartproductkey] = $this->getProductPrices($product, 0, $variantmod,
                    $product->prod_quantity, false, false);

            $this->_amountCart += $product->prod_quantity;
			$this->_cartPrices[$cartproductkey] = $product->prices;

            $this->_cartPrices['costPriceShopCurrency'] += $product->prices['costPriceShopCurrency'] * $product->prod_quantity;

			if($this->_currencyDisplay->_priceConfig['basePrice']){
                $this->_cartPrices['basePrice'] += $product->prices['basePrice'] * $product->prod_quantity;
            }

			if($this->_currencyDisplay->_priceConfig['basePriceWithTax']) {
                $this->_cartPrices['basePriceWithTax'] = $this->_cartPrices['basePriceWithTax'] +
                    $product->prices['basePriceWithTax'] * $product->prod_quantity;
            }
			if($this->_currencyDisplay->_priceConfig['discountedPriceWithoutTax']){
                $this->_cartPrices['discountedPriceWithoutTax'] = $this->_cartPrices['discountedPriceWithoutTax'] +
                    $product->prices['discountedPriceWithoutTax'] * $product->prod_quantity;
            }
			if($this->_currencyDisplay->_priceConfig['salesPrice']){
                $this->_cartPrices['salesPrice'] = $this->_cartPrices['salesPrice'] +
                    $product->prices['salesPrice'] * $product->prod_quantity;
            }
			if($this->_currencyDisplay->_priceConfig['taxAmount']){
                $this->_cartPrices['taxAmount'] = $this->_cartPrices['taxAmount']
                        + $product->prices['taxAmount'] * $product->prod_quantity;
            }
			if($this->_currencyDisplay->_priceConfig['salesPriceWithDiscount']){
                $this->_cartPrices['salesPriceWithDiscount'] = $this->_cartPrices['salesPriceWithDiscount'] +
                    $product->prices['salesPriceWithDiscount'] * $product->prod_quantity;
            }
			if($this->_currencyDisplay->_priceConfig['discountAmount']){
                $this->_cartPrices['discountAmount'] = $this->_cartPrices['discountAmount'] -
                    $product->prices['discountAmount'] * $product->prod_quantity;
            }
			if($this->_currencyDisplay->_priceConfig['priceWithoutTax']){
                $this->_cartPrices['priceWithoutTax'] = $this->_cartPrices['priceWithoutTax'] +
                    $product->prices['priceWithoutTax'] * $product->prod_quantity;
            }


			if($this->_currencyDisplay->_priceConfig['priceWithoutTax']){
                $this->_cartPrices[$cartproductkey]['subtotal'] =
                    $product->prices['priceWithoutTax'] * $product->prod_quantity;
            }
			if($this->_currencyDisplay->_priceConfig['taxAmount']){
                $this->_cartPrices[$cartproductkey]['subtotal_tax_amount'] =
                    $product->prices['taxAmount'] * $product->prod_quantity;
            }
			if($this->_currencyDisplay->_priceConfig['discountAmount']){
                $this->_cartPrices[$cartproductkey]['subtotal_discount'] = -
                    $product->prices['discountAmount'] * $product->prod_quantity;
            }
			if($this->_currencyDisplay->_priceConfig['salesPrice']){
                $this->_cartPrices[$cartproductkey]['subtotal_with_tax'] =
                    $product->prices['salesPrice'] * $product->prod_quantity;
            }

		}

		$this->_cartData['DBTaxRulesBill'] = $DBTaxRules = $this->gatherEffectingRulesForBill('DBTaxBill');

		$shipment_id = empty($cart->shipm_id) ? 0 : $cart->shipm_id;

		$this->calculateShipmentPrice($cart,  $shipment_id, $checkAutomaticSelected);

		$this->_cartData['taxRulesBill'] = $taxRules = $this->gatherEffectingRulesForBill('TaxBill');
		$this->_cartData['DATaxRulesBill'] = $DATaxRules = $this->gatherEffectingRulesForBill('DATaxBill');

		$this->_cartPrices['discountBeforeTaxBill'] = $this->roundInternal($this->executeCalculation($DBTaxRules, $this->_cartPrices['salesPrice']));
		$toTax = !empty($this->_cartPrices['discountBeforeTaxBill']) ? $this->_cartPrices['discountBeforeTaxBill'] : $this->_cartPrices['salesPrice'];

		//We add the price of the Shipment before the tax. The tax per bill is meant for all services. In the other case people should use taxes per
		//  product or method
		$toTax = $toTax + $this->_cartPrices['salesPriceShipment'];

		$this->_cartPrices['withTax'] = $discountWithTax = $this->roundInternal($this->executeCalculation($taxRules, $toTax, true));
		$toDisc = !empty($this->_cartPrices['withTax']) ? $this->_cartPrices['withTax'] : $toTax;
        $cartTax = !empty($toDisc) ? $toDisc - $toTax : 0;

		$discountAfterTax = $this->roundInternal($this->executeCalculation($DATaxRules, $toDisc));
		$this->_cartPrices['withTax'] = $this->_cartPrices['discountAfterTax'] = !empty($discountAfterTax) ? $discountAfterTax : $toDisc;
        $cartdiscountAfterTax = !empty($discountAfterTax) ? $discountAfterTax- $toDisc : 0;

		$paymentId = empty($cart->paym_id) ? 0 : $cart->paym_id;

		$this->calculatePaymentPrice($cart, $paymentId, $checkAutomaticSelected);

		if($this->_currencyDisplay->_priceConfig['salesPrice']){
            $this->_cartPrices['billSub'] = $this->_cartPrices['basePrice'] + $this->_cartPrices['shipmentValue'] +
                $this->_cartPrices['paymentValue'];
        }

        if($this->_currencyDisplay->_priceConfig['discountAmount']) {
            $this->_cartPrices['billDiscountAmount'] = $this->_cartPrices['discountAmount'] +
                $this->_cartPrices['discountBeforeTaxBill'] + $cartdiscountAfterTax;
        }

        if($this->_currencyDisplay->_priceConfig['taxAmount']) {
            $this->_cartPrices['billTaxAmount'] = $this->_cartPrices['taxAmount'] + $this->_cartPrices['shipmentTax'] + $this->_cartPrices['paymentTax'] + $cartTax;

        }
        if($this->_currencyDisplay->_priceConfig['salesPrice']){
            $this->_cartPrices['billTotal'] = $this->_cartPrices['salesPricePayment'] + $this->_cartPrices['withTax'];
        }



		// Last step is handling a coupon, if given
		if (!empty($cart->coupon_code)) {
			$this->couponHandler($cart->coupon_code);
		}

		return $this->_cartPrices;
	}

    /**
     * Расчитать базовую цену
     * @param array $product
     * @return bool|float|mixed
     */
    public function calculateCostPrice($product){
        global $cfg;
        $this->_revert = true;

        if (!$product) return false;
        // Если каких-то данных не хватает - выбрать их из БД (vendorId)
        //$productAdapter->getProductById($product['id']);

        if (!empty($product['_price'])) {
            $this->productCurrency = $product['_price_currency'];
            $this->product_tax_id = $product['_price_tax_id'];
            $this->product_discount_id = $product['_price_discount_id'];
        } else {
            //'cost Price empty, if child, everything okey, this is just a dev note'
            return false;
        }
        $this->productVendorId = !empty($product['_vendor_id']) ? $product['_vendor_id'] : 1;

        $this->_cats = array($product['page_cat']);

        if($cfg['shop']['multix'] != 0 && empty($this->vendorCurrency)){
            $this->vendorCurrency = Vendor::getVendorCurrencyId($this->productVendorId);
        }
        // ???
        if (!empty($amount)) {
            $this->_amount = $product;
        }

        //$this->setCountryState($this->_cart);
        $this->rules['Marge'] = $this->gatherEffectingRulesForProductPrice('Marge', $this->product_marge_id);
        $this->rules['Tax'] = $this->gatherEffectingRulesForProductPrice('Tax', $this->product_tax_id);
        $this->rules['VatTax'] = $this->gatherEffectingRulesForProductPrice('VatTax', $this->product_tax_id);
        $this->rules['DBTax'] = $this->gatherEffectingRulesForProductPrice('DBTax', $this->product_discount_id);
        $this->rules['DATax'] = $this->gatherEffectingRulesForProductPrice('DATax', $this->product_discount_id);

        $salesPrice = $product['_price_sales'];

        $withoutVatTax = $this->roundInternal($this->executeCalculation($this->rules['VatTax'], $salesPrice));
        $withoutVatTax = !empty($withoutVatTax) ? $withoutVatTax : $salesPrice;

        $withDiscount = $this->roundInternal($this->executeCalculation($this->rules['DATax'], $withoutVatTax));
        $withDiscount = !empty($withDiscount) ? $withDiscount : $withoutVatTax;

        $withTax = $this->roundInternal($this->executeCalculation($this->rules['Tax'], $withDiscount));
        $withTax = !empty($withTax) ? $withTax : $withDiscount;

        $basePriceP = $this->roundInternal($this->executeCalculation($this->rules['DBTax'], $withTax));
        $basePriceP = !empty($basePriceP) ? $basePriceP : $withTax;

        $basePrice = $this->roundInternal($this->executeCalculation($this->rules['Marge'], $basePriceP));
        $basePrice = !empty($basePrice) ? $basePrice : $basePriceP;

        $productCurrency = CurrencyDisplay::getInstance();
        $costprice = $productCurrency->convertCurrencyTo( $this->productCurrency, $basePrice,false);

        $this->_revert = false;

        return $costprice;
    }


    public function setRevert($revert){
        $this->_revert = $revert;
    }

	/**
	 * Get coupon details and calculate the value
	 * @param $code Coupon code
     * @return bool
     * @todo Calculate the tax
     */
	private function couponHandler($code) {
        global $cfg;

        /** @var Coupon $coupon  */
		$coupon = Coupon::getByCode($code);
        if(!$coupon) return false;

        //Получить все товары, на которые не распросраняется скидка по купону
        $noDisc = 0;
        foreach($this->_cart->products as $item){
            if($item->prod_no_coupon_discount == 1){
                $noDisc += $this->_cartPrices[$item->prod_id]['subtotal_with_tax'];
            }

        }
        // Сумма от которой считаем купон, если он процентный
        $toDiscCoupon = $this->_cartPrices['salesPrice'] - $noDisc;

		$_value_is_total = ($coupon->coupon_percent_or_total == 'total');
		$this->_cartData['couponCode'] = $code;
		$this->_cartData['couponDescr'] = ($_value_is_total ? '' : ' ('.(round($coupon->coupon_value) . '%)'));
		$this->_cartPrices['couponValue'] = ($_value_is_total ? $coupon->coupon_value :
            ($toDiscCoupon * ($coupon->coupon_value / 100))
		);
		// TODO Calculate the tax


		$this->_cartPrices['couponTax'] = 0;
        $this->_cartPrices['salesPriceCoupon'] = $this->_cartPrices['couponValue'] - $this->_cartPrices['couponTax'];

        // Cумма из которой можно вычесть сумму купона
        $toDisc = $this->_cartPrices['billTotal'] - $noDisc;

        $couponDisc = $toDisc - $this->_cartPrices['salesPriceCoupon'];

        if ($couponDisc < 0) $couponDisc = 0.0;

        $newBillTotal = $couponDisc + $noDisc;
        $this->_cartPrices['salesPriceCoupon'] = $this->_cartPrices['billTotal'] - $newBillTotal;

        $this->_cartPrices['billTotal'] = $newBillTotal;

	}

	/**
	 * Function to execute the calculation of the gathered rules Ids.
	 *
	 * @param 		$rules 		The Ids of the products
	 * @param 		$price 		The input price, if no rule is affecting, 0 gets returned
	 * @return int 	$price  	the endprice
	 */
	function executeCalculation($rules, $baseprice, $relateToBaseAmount=false) {

		if (empty($rules)) return 0;

		$rulesEffSorted = $this->record_sort($rules, 'ordering', $this->_revert);

		$price = $baseprice;
		$finalprice = $baseprice;
		if (isset($rulesEffSorted)) {

			foreach ($rulesEffSorted as $rule) {

				if ($relateToBaseAmount) {
					$cIn = $baseprice;
				} else {
					$cIn = $price;
				}
				$cOut = $this->interpreteMathOp($rule['calc_value_mathop'], $rule['calc_value'], $cIn, 
                        $rule['curr_id']);
				$this->_cartPrices[$rule['calc_id'] . 'Diff'] = $this->roundInternal($this->roundInternal($cOut) - $cIn);

				//okey, this is a bit flawless logic, but should work
				if ($relateToBaseAmount) {
					$finalprice = $finalprice + $this->_cartPrices[$rule['calc_id'] . 'Diff'];
				} else {
					$price = $cOut;
				}
			}
		}

		//okey done with it
		if (!$relateToBaseAmount) {
			$finalprice = $price;
		}

		return $finalprice;
	}

    /**
     * Gatheres the rules which affects the product.
     * @param string  $entrypoint The entrypoint how it should behave. Valid values should be
     *                  Profit (Commission is a profit rule that is shared, maybe we remove shared and make a
     *                          new entrypoint called profit)
     *                  DBTax (Discount for wares, coupons)
     *                  Tax
     *                  DATax (Discount on money)
     *                  Duty
     * @param int $id
     * @return array $rules The rules that effects the product as Ids
     */
	function gatherEffectingRulesForProductPrice($entrypoint, $id) {
        global $db_shop_calc_categories, $db_shop_calc_groups, $db_shop_calc_countries, $db_shop_calc_states;

        $testedRules = array();

		if ($id === -1) return ;

		$countries = '';
		$states = '';
		$shopperGroup = '';

        $entrypoint = (string) $entrypoint;
        if(empty($this->allrules[$this->productVendorId][$entrypoint])){
			return $testedRules;
		}
        $allRules = $this->allrules[$this->productVendorId][$entrypoint];
		//Cant be done with Leftjoin afaik, because both conditions could be arrays.
		foreach ($allRules as $i => $rule) {
            if(!empty($id)){
                if($rule['calc_id'] == $id){
                    $testedRules[] = $rule;
                }
                continue;
            }

            if(!empty($this->allrules[$this->productVendorId][$entrypoint][$i]['for_override'])){
                continue;
            }

			if(!isset($this->allrules[$this->productVendorId][$entrypoint][$i]['cats'])){
				$q = "SELECT `structure_code` FROM $db_shop_calc_categories WHERE `calc_id`={$rule['calc_id']}";
				$res = $this->_db->query($q);
                $this->allrules[$this->productVendorId][$entrypoint][$i]['cats'] = $res->fetchAll(PDO::FETCH_COLUMN);
                $res->closeCursor();
			}
            
            $hitsCategory = true;
			if (isset($this->_cats)) {
				$hitsCategory = $this->testRulePartEffecting(
                                $this->allrules[$this->productVendorId][$entrypoint][$i]['cats'], $this->_cats);
			}

			if(!isset($this->allrules[$this->productVendorId][$entrypoint][$i]['shoppergrps'])){
				$q = "SELECT `grp_id` FROM $db_shop_calc_groups WHERE `calc_id`={$rule['calc_id']}";
				$res = $this->_db->query($q);
				$this->allrules[$this->productVendorId][$entrypoint][$i]['shoppergrps'] = $res->fetchAll(PDO::FETCH_COLUMN);
			}

			$hitsShopper = true;
			if (isset($this->_shopperGroupId)) {
				$hitsShopper = $this->testRulePartEffecting($this->allrules[$this->productVendorId][$entrypoint][$i]['shoppergrps'],
                        $this->_shopperGroupId);
			}

			if(!isset($this->allrules[$this->productVendorId][$entrypoint][$i]['countries'])){
				$q = "SELECT `country` FROM $db_shop_calc_countries WHERE `calc_id`={$rule["calc_id"]}";
				$res = $this->_db->query($q);
				$this->allrules[$this->productVendorId][$entrypoint][$i]['countries'] = $res->fetchAll(PDO::FETCH_COLUMN);
			}

			if(!isset($this->allrules[$this->productVendorId][$entrypoint][$i]['states'])){
				$q = "SELECT `state_id` FROM $db_shop_calc_states WHERE `calc_id`={$rule["calc_id"]}";
				$res = $this->_db->query($q);
				$this->allrules[$this->productVendorId][$entrypoint][$i]['states'] = $res->fetchAll(PDO::FETCH_COLUMN);
			}

			$hitsDeliveryArea = true;
			if (!empty($this->_deliveryCountry) && !empty($this->allrules[$this->productVendorId][$entrypoint][$i]['countries']) && empty($this->allrules[$this->productVendorId][$entrypoint][$i]['states'])) {
				$hitsDeliveryArea = $this->testRulePartEffecting($this->allrules[$this->productVendorId][$entrypoint][$i]['countries'], $this->_deliveryCountry);
			} else if (!empty($this->_deliveryState) && !empty($this->allrules[$this->productVendorId][$entrypoint][$i]['states'])) {
				$hitsDeliveryArea = $this->testRulePartEffecting($this->allrules[$this->productVendorId][$entrypoint][$i]['states'], $this->_deliveryState);
			}

			$hitsAmount = true;
			if (!empty($this->_amount)) {
				//Test
			}
			if ($hitsCategory && $hitsShopper && $hitsDeliveryArea) {
				if ($this->_debug)
				echo '<br/ >Add rule ForProductPrice ' . $rule["calc_id"];

				$testedRules[] = $rule;
			}
		}

		//Test rules in plugins
        // TODO Hook here
//		if(!empty($testedRules)){
//			JPluginHelper::importPlugin('vmcalculation');
//			$dispatcher = JDispatcher::getInstance();
//			$dispatcher->trigger('plgVmInGatherEffectRulesProduct',array(&$this,&$testedRules));
//		}

		return $testedRules;
	}

	/**
	 * Gathers the effecting rules for the calculation of the bill
	 *
	 * @param	$entrypoint
	 * @param	$cartVendorId
	 * @return $rules The rules that effects the Bill as Ids
	 */
	function gatherEffectingRulesForBill($entrypoint, $cartVendorId=1) {
        global $db_shop_calcs, $db_shop_calc_countries, $db_shop_calc_states, $db_shop_calc_groups;
        
		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
		$q = "SELECT * FROM $db_shop_calcs WHERE
                `calc_kind`='{$entrypoint}' AND `calc_published`='1'
                AND (`vendor_id`={$cartVendorId} OR `calc_shared`=1 )
				AND ( calc_publish_up = ". $this->_db->quote($this->_nullDate) ." OR calc_publish_up <= ".$this->_db->quote($this->_now)." )
				AND ( calc_publish_down = ". $this->_db->quote($this->_nullDate) ." OR calc_publish_down >= ".$this->_db->quote($this->_now)." ) ";
		//			$shoppergrps .  $countries . $states ;
        $sql = $this->_db->query($q);
        $rules = $sql->fetchAll();
        $sql->closeCursor();

		$testedRules = array();
		foreach ($rules as $rule) {

			$q = "SELECT `country` FROM $db_shop_calc_countries WHERE `calc_id`={$rule["calc_id"]}";
			$sql = $this->_db->query($q);
			$countries = $sql->fetchAll(PDO::FETCH_COLUMN);

			$q = "SELECT `state_id` FROM $db_shop_calc_states WHERE `calc_id`={$rule["calc_id"]}";
			$sql = $this->_db->query($q);
			$states = $sql->fetchAll(PDO::FETCH_COLUMN);

			$hitsDeliveryArea = true;
			if (!empty($countries) && empty($states)) {
				$hitsDeliveryArea = $this->testRulePartEffecting($countries, $this->_deliveryCountry);
			} else if (!empty($states)) {
				$hitsDeliveryArea = $this->testRulePartEffecting($states, $this->_deliveryState);
			}

			$q = "SELECT `grp_id` FROM $db_shop_calc_groups WHERE `calc_id`={$rule["calc_id"]}";
			$sql = $this->_db->query($q);
			$shoppergrps = $sql->fetchAll(PDO::FETCH_COLUMN);

			$hitsShopper = true;
			if (isset($this->_shopperGroupId)) {
				$hitsShopper = $this->testRulePartEffecting($shoppergrps, $this->_shopperGroupId);
			}
                // debug
				if ($hitsDeliveryArea && $hitsShopper) {
					if ($this->_debug)
					echo '<br/ >Add Checkout rule ' . $rule["calc_id"] . '<br/ >';
					$testedRules[] = $rule;
				}
			}

			//Test rules in plugins
            // TODO add hook
//			if(!empty($testedRules)){
//				JPluginHelper::importPlugin('vmcalculation');
//				$dispatcher = JDispatcher::getInstance();
//				$dispatcher->trigger('plgVmInGatherEffectRulesBill', array(&$this, &$testedRules));
//			}

			return $testedRules;
		}

		/**
		 * Calculates the effecting Shipment prices for the calculation
		 * @todo $ship_id - не нада?
		 * @param ShopCart 	$cart 	The Id of the coupon
         * @param int $ship_id
         * @param bool $checkAutomaticSelected
		 * @return 	array
		 */
		function calculateShipmentPrice($cart, $ship_id, $checkAutomaticSelected = true) {
            global $L, $cfg, $cot_plugins;
            
			$this->_cartData['shipmentName'] = $L['shop']['cart_no_shipment_selected'];
			$this->_cartPrices['shipmentValue'] = 0; //could be automatically set to a default set in the globalconfig
			$this->_cartPrices['shipmentTax'] = 0;
			$this->_cartPrices['shipmentTotal'] = 0;
			$this->_cartPrices['salesPriceShipment'] = 0;
			// check if there is only one possible shipment method
			
            $automaticSelectedShipment =   $cart->CheckAutomaticSelectedShipment($this->_cartPrices, 
                    $checkAutomaticSelected);
			if ($automaticSelectedShipment) $ship_id=$cart->shipmentmethod_id;
			if (empty($ship_id)) return;
            
			// Handling shipment plugins
            $method = ShipmentMethod::getById($ship_id);

            if (!$method || !cot_plugin_active($method->pl_code) || !cot_auth('plug', $method->pl_code, 'R')){
                $cart->shipmentMethod = 0;
                $this->_cartData['shipmentName'] = $L['shop']['cart_no_shipment_selected'];
                return $this->_cartPrices;
            }
             /* === Hook === */
            //plgVmonSelectedCalculatePriceShipment
            // Плагин должен заполнить $this->_cartPrices !!!
            $shipmentValid = true;
            foreach($cot_plugins['shop.shipment.calc_price'] as $k){
                if ($k['pl_code'] == $method->pl_code){
                        include $cfg['plugins_dir'] . '/' . $k['pl_file'];
                }
            }
            /* === /Hook === */

			// Если найдены ошибки или плагин установил $shipmentValid = false
            // TODO А нужен ли $shipmentValid ??? пока не рекомендую его использовать в плагинах ))))
			if (cot_error_found() || !$shipmentValid) {
                $cart->shipmentMethod = 0;
                $this->_cartData['shipmentName'] = $L['shop']['cart_no_shipment_selected'];
			}else{
                // Установить название метода оплаты
                $this->_cartData['shipmentName'] = cot_rc('shipmentName', array(
                            'title' => htmlspecialchars($method->shipm_title),
                            'desc'  => htmlspecialchars($method->shipm_desc),
                        ));
                
            }

			return $this->_cartPrices;
		}

		/**
		 * Calculates the effecting Payment prices for the calculation
		 * @todo $payment_id - не нада?
         * @param ShopCart $cart - shop Cart
		 * @param int	$payment_id 	The Id of the paymentmethod
		 * @param bool	$checkAutomaticSelected
		 * @return 	$array
		 */
		function calculatePaymentPrice(ShopCart $cart, $payment_id, $checkAutomaticSelected = true) {
            global $L, $cfg, $cot_plugins;

			$this->_cartData['paymentName'] = $L['shop']['cart_no_payment_selected'];
			$this->_cartPrices['paymentValue'] = 0; //could be automatically set to a default set in the globalconfig
			$this->_cartPrices['paymentTax'] = 0;
			$this->_cartPrices['paymentTotal'] = 0;
			$this->_cartPrices['salesPricePayment'] = 0;

			// check if there is only one possible payment method
			$cart->automaticSelectedPayment =   $cart->CheckAutomaticSelectedPayment( $this->_cartPrices, $checkAutomaticSelected);
			if ($cart->automaticSelectedPayment) $payment_id=$cart->paym_id;
			if (empty($payment_id)) return;

            // Handling payment plugins
            $method = $cart->paymentMethod;
            if (!$method  || !cot_plugin_active($method->pl_code) || !cot_auth('plug', $method->pl_code, 'R')){
                $cart->paymentMethod = 0;
                $this->_cartData['paymentName'] = $L['shop']['cart_no_payment_selected'];
                return $this->_cartPrices;
            }
            /* === Hook === */
            //plgVmonSelectedCalculatePricePayment
            // Плагин должен заполнить $this->_cartPrices !!!
            $paymentValid = true;
            foreach($cot_plugins['shop.payment.calc_price'] as $k){
                if ($k['pl_code'] == $method->pl_code){
                    include $cfg['plugins_dir'] . '/' . $k['pl_file'];
                }
            }
            /* === /Hook === */

			// Если найдены ошибки или плагин установил $paymentValid = false
            // TODO А нужен ли $paymentValid ??? пока не рекомендую его использовать в плагинах ))))
			if (cot_error_found() || !$paymentValid) {
                $cart->paymentMethod = 0;
                $this->_cartData['paymentName'] = $L['shop']['cart_no_payment_selected'];
			}else{
                // Установить название метода оплаты
                $this->_cartData['paymentName'] = cot_rc('paymentName', array(
                            'title' => htmlspecialchars($method->paym_title),
                            'desc'  => htmlspecialchars($method->paym_desc),
                        ));
                
            }

			return $this->_cartPrices;
		}

		function calculateCustomPriceWithTax($price, $override_id=0) {

			$taxRules = $this->gatherEffectingRulesForProductPrice('Tax', $override_id);
			if(!empty($taxRules)){
				$price = $this->executeCalculation($taxRules, $price, true);
			}

			$price = $this->roundDisplay($price);

			return $price;
		}

		/**
		 * This function just writes the query for gatherEffectingRulesForProductPrice
		 * When a condition is not set, it is handled like a set condition that affects it. So the users have only to add a value
		 * for the conditions they want to (You dont need to enter a start or end date when the rule should count everytime).
		 *
		 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
		 * @author Max Milbers
		 * @param $data		the ids of the rule, for exampel the ids of the categories that affect the rule
		 * @param $field	the name of the field in the db, for exampel calc_categories to write a rule that asks for the field calc_categories
		 * @return $q		The query
		 */
		function writeRulePartEffectingQuery($data, $field, $setAnd=0) {
			$q = '';
			if (!empty($data)) {
				if ($setAnd) {
					$q = ' AND (';
				} else {
					$q = ' (';
				}
				foreach ($data as $id) {
					$q = $q . '`' . $field . '`="' . $id . '" OR';
				}
				$q = $q . '`' . $field . '`="0" )';
			}
			return $q;
		}

    /**
     * This functions interprets the String that is entered in the calc_value_mathop field
     * The first char is the signum of the function. The more this function can be enhanced
     * maybe with function that works like operators, the easier it will be to make more complex
     * disount/commission/profit formulas progressive, nonprogressive and so on.
     *
     * @param string    $mathop     String reprasentation of the mathematical operation
     * @param float    $value         The value that affects the price
     * @param float    $price        The price to calculate
     * @param int|string $currency the currency which should be used
     * @return \The
     */
		function interpreteMathOp($mathop, $value, $price, $currency='') {

			$coreMathOp = array('+','-','+%','-%');

            if(!$this->_revert){
                $plus = '+';
                $minus = '-';
            } else {
                $plus = '-';
                $minus = '+';
            }
			if(in_array($mathop, $coreMathOp)){
				$sign = substr($mathop, 0, 1);

				$calculated = false;
				if (strlen($mathop) == 2) {
					$cmd = substr($mathop, 1, 2);
					if ($cmd == '%') {
                        if(!$this->_revert){
                            $calculated = $price * $value / 100.0;
                        } else {
                            $calculated = $price /(1 +  (100.0 / $value));
                        }
					}
				} else if (strlen($mathop) == 1){
					$calculated = $this->_currencyDisplay->convertCurrencyTo($currency, $value);
				}

				if($sign == $plus){
					return $price + (float)$calculated;
				} else if($sign == $minus){
					return $price - (float)$calculated;
				} else {
					cot_error('Unrecognised mathop '.$mathop.' in calculation rule found');
					return $price;
				}
			} else {
                $calculated = false;
                /* === Hook === */
                foreach (cot_getextplugins('shop.mathOp.interprete') as $pl){
                    include $pl;
                }
				if($calculated && !cot_error_found()){
                    return $price;
				} else {
                    cot_error('Unrecognised mathop '.$mathop.' in calculation rule found, seems you created this rule with plugin not longer accesible (deactivated, uninstalled?)');
					return $price;
				}
			}

		}

        /**
         * Standard round function, we round every number with 6 fractionnumbers
         * We need at least 4 to calculate something like 9.25% => 0.0925
         * 2 digits
         * Should be setable via config (just for the crazy case)
         */
        function roundInternal($value, $name = '') {
            if($name != ''){
                if(isset($this->_currencyDisplay->_priceConfig[$name][1])){
                    return round($value,$this->_currencyDisplay->_priceConfig[$name][1]);
                } else {
                    return round($value, $this->_internalDigits);
                }
            } else {
                return round($value, $this->_internalDigits);
            }
        }

		/**
         * @deprecated
		 * Round function for display with 6 fractionnumbers.
		 * For more information please read http://en.wikipedia.org/wiki/Propagation_of_uncertainty
		 * and http://www.php.net/manual/en/language.types.float.php
		 * So in case of € or $ it is rounded in cents
		 * Should be setable via config
		 */
//		function roundDisplay($value) {
//			return round($value, 2);
//		}

		/**
		 * Can test the tablefields Category, Country, State
		 *  If the the data is 0 false is returned
		 */
		function testRulePartEffecting($rule, $data) {

			if (!isset($rule))
			return true;
			if (!isset($data))
			return true;

			if (is_array($rule)) {
				if (count($rule) == 0)
				return true;
			} else {
				$rule = array($rule);
			}
			if (!is_array($data))
			$data = array($data);

			$intersect = array_intersect($rule, $data);
			if ($intersect) {
				return true;
			} else {
				return false;
			}
		}

		/**
         * Sorts indexed 2D array by a specified sub array key
		 *
		 */
        public static function record_sort($records, $field, $reverse=false) {
			if (is_array($records)) {
				$hash = array();

				foreach ($records as $record) {

					$keyToUse = $record[$field];
					while (array_key_exists($keyToUse, $hash)) {
						$keyToUse = $keyToUse + 1;
					}
					$hash[$keyToUse] = $record;
				}
				($reverse) ? krsort($hash) : ksort($hash);
				$records = array();
				foreach ($hash as $record) {
					$records [] = $record;
				}
			}
			return $records;
		}

		/**
		 * Calculate a pricemodification for a variant
		 *
		 * Variant values can be in the following format:
		 * Array ( [Size] => Array ( [XL] => +1 [M] => [S] => -2 ) [Power] => Array ( [strong] => [middle] => [poor] => =24 ) )
		 *
		 * In the post is the data for the chosen variant, when there is a hit, it gets calculated
		 *
		 * Returns all variant modifications summed up or the highest price set with '='
		 *
		 * @todo could be slimmed a bit down, using smaller array for variantnames, this could be done by using the parseModifiers method, needs to adjust the post
		 * @param int $virtuemart_product_id the product ID the attribute price should be calculated for
		 * @param array $variantnames the value of the variant
		 * @return array The adjusted price modificator
		 */
		public function calculateModificators($product, $variants) {

			$modificatorSum = 0.0;
			$row = 0;
			foreach ($variants as $variant => $selected) {
				if (!empty($selected)) {
					$query = 'SELECT  C.* , field.*
						FROM `#__virtuemart_customs` AS C
						LEFT JOIN `#__virtuemart_product_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
						WHERE `virtuemart_product_id` =' . $product->virtuemart_product_id;
					$query .=' and is_cart_attribute = 1 and field.`virtuemart_customfield_id`=' . $selected;
					$this->_db->setQuery($query);
					$productCustomsPrice = $this->_db->loadObject();
					if ($productCustomsPrice->field_type =='E') {
						if(!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS.DS.'vmcustomplugin.php');
						JPluginHelper::importPlugin('vmcustom');
						$dispatcher = JDispatcher::getInstance();
						$dispatcher->trigger('plgVmCalculateCustomVariant',array($product, &$productCustomsPrice,$selected,$row));
					}
					//$app = JFactory::getApplication();
					if (!empty($productCustomsPrice->custom_price)) {
						//TODO adding % and more We should use here $this->interpreteMathOp
						$modificatorSum = $modificatorSum + $productCustomsPrice->custom_price;
					}
					$row++;
				}
			}
			return $modificatorSum;
		}



}
