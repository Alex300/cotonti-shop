<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Calculation helper class
 *
 * This class provides the functions for the calculations
 *
 * @package shop
 * @subpackage Helpers
 * @author Alex
 */
class calculationHelper {

    /**
     * Группы пользователя (покупателя)
     * @var array
     */
    private $shopperGroups;

    /**
     * @var CurrencyDisplay
     */
    private $currencyDisplay;

    /**
     * @var int
     */
    public $vendorCurrency = 0;

    /**
     * @var int
     */
    public $productVendorId;
    public $product_tax_id = 0;
    public $product_discount_id = 0;
    public $product_marge_id = 0;
    private $cats;
    public $amount;
    /**
     * @var array
     */
    public $rules;

    /**
     * Текущая дата в формате 'Y-m-d H:i:s'
     * @var string
     */
    private $now;

    /**
     * The null or zero representation of a timestamp for the database driver.
     * @var string
     */
    private $nullDate;

    /**
     * @var array
     */
    protected $allrules = array();

    /**
     * @var ShopCart Ссылка на объект корзины
     */
    protected $cart = null;
    private $cartPrices;

    private $revert = false;

    /**
     * @var string
     */
    protected $deliveryCountry;

    /**
     * @var int
     */
    protected $deliveryRegion;

    /**
     * @var int Количество знаков для округления, может и не стоит ставить 2
     *      standard round function, we round every number with 6 fractionnumbers
     *      We need at least 4 to calculate something like 9.25% => 0.0925
     */
    private $internalDigits = 2;

    /**
     * @var calculationHelper
     */
    static $_instance;

    /**
     * Constructor,... sets the actual date and current currency
     * Одиночка
     */
    private function __construct() {
        global $db, $sys, $cfg;

        $this->now = date('Y-m-d H:i:s', $sys['now']);
        $this->nullDate = date('Y-m-d H:i:s', 0);

        //Attention, this is set to the mainvendor atm.
        //This means also that atm for multivendor, every vendor must use the shopcurrency as default
        $this->productVendorId = 1;

        $this->currencyDisplay = CurrencyDisplay::getInstance();

        if(!empty($this->currencyDisplay->_vendorCurrency)){
            $this->vendorCurrency = $this->currencyDisplay->vendorCurrency;
            // TODO FIX it multix
        }elseif($cfg["shop"]['multix'] != 0){
            $vendorId = 1;
            $this->vendorCurrency = Vendor::getVendorCurrencyId($vendorId);
        }

        $this->setShopperGroups();

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
    public static function getInstance() {
        global $sys;

        if (!is_object(self::$_instance)) {
            self::$_instance = new calculationHelper();
        } else {
            self::$_instance->now = date('Y-m-d H:i:s', $sys['now']);
        }
        return self::$_instance;
    }

    public function setShopperGroups($shopperGroupIds=0) {
        global $usr, $db_groups_users, $db;

        if (!empty($shopperGroupIds)) {
            if (is_array($shopperGroupIds)){
                $this->shopperGroups = $shopperGroupIds;
            }else{
                $this->shopperGroups = array((int)$shopperGroupIds);
            }
        } else {
            if ($usr['id'] > 0){
                // Получить группы пользователя
                $tmp1 = $db->query("SELECT gru_groupid FROM $db_groups_users WHERE gru_userid={$usr['id']}")
                    ->fetchAll(PDO::FETCH_COLUMN);
                $this->shopperGroups = $tmp1;
            }elseif (empty($this->shopperGroups)) {
                //We just define the shoppergroup with id = 1 to guest default shoppergroup
                $this->shopperGroups[] = COT_GROUP_GUESTS;
            }
        }
    }

    /**
     * Получить группы текущего пользователя
     * @return array
     */
    public function getShopperGroups(){
        return $this->shopperGroups;
    }

    public function setVendorId($id){
        global $db_shop_calcs, $db;

        $this->productVendorId = $id;
        if(empty($this->allrules[$this->productVendorId])){
            $epoints = array('Marge','Tax','VatTax','DBTax','DATax');
            $this->allrules[$this->productVendorId] = array(
                'Marge' => array(),
                'Tax' => array(),
                'VatTax' => array(),
                'DBTax' => array(),
                'DATax' => array(),

            );

            $cond = array(
                array('calc_kind', $epoints),
                array('calc_published', 1),
                array('SQL', "`vendor_id`={$this->productVendorId} OR `calc_shared`=1"),
                array('SQL', "calc_publish_up = ".$db->quote($this->nullDate) ."
                                                    OR calc_publish_up <= ".$db->quote($this->now)),
                array('SQL', "calc_publish_down = ".$db->quote($this->nullDate)."
                                                    OR calc_publish_down>=".$db->quote($this->now)),
            );

            $allrules = Calc::find($cond, 0, 0, 'calc_kind ASC');
            if($allrules){
                foreach ($allrules as $rule){
                    $this->allrules[$this->productVendorId][$rule->calc_kind][] = $rule;
                }
            }
        }

    }

    /**
     * Расчитать базовую цену
     * @param array $product
     * @return bool|float|mixed
     */
    public function calculateCostPrice($product){
        global $cfg;
        $this->revert = true;

        if (!$product) return false;
        // Если каких-то данных не хватает - выбрать их из БД (vendorId)
        //$productAdapter->getProductById($product['id']);

        if (!empty($product['price'])) {
            $this->productCurrency = $product['price']['curr_id'];
            $this->product_tax_id = $product['price']['tax_id'];
            $this->product_discount_id = $product['price']['discount_id'];
        } else {
            //'cost Price empty, if child, everything okey, this is just a dev note'
            return false;
        }
        $this->productVendorId = !empty($product['vendor_id']) ? $product['vendor_id'] : 1;

        $this->cats = array($product['page_cat']);

        if($cfg['shop']['multix'] != 0 && empty($this->vendorCurrency)){
            $this->vendorCurrency = Vendor::getCurrencyId($this->productVendorId);
        }
        // ???
        if (!empty($amount)) {
            $this->amount = $product;
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

        $this->revert = false;

        return $costprice;
    }


    public function setRevert($revert){
        $this->revert = $revert;
    }

    /**
     * function to start the calculation, here it is for the invoice in the checkout
     * This function is partly implemented !
     *
     * The function calls setProductPrices for every product except it is already known (maybe changed and adjusted with product amount value
     * The single prices gets added in an array and already summed up.
     *
     * Then simular to getProductPrices first the effecting rules are determined and calculated.
     * Ah function to determine the coupon that effects the calculation is already implemented. But not completly in the calculation.
     *
     * 		Subtotal + Tax + Discount =	Total
     *
     * @param ShopCart $cart 	Shop Cart
     * @param bool $checkAutomaticSelected
     *
     * @return array
     *
     * @todo перенести в корзину. Имя метода в корзине такое же как и заказа Order::recalculate
     */
    public function setCheckoutPrices($cart, $checkAutomaticSelected = true) {
        $this->cart = $cart;

        $pricesPerId = array();
        $this->cartPrices = array();
        $this->cartData = array();
        $resultWithTax = 0.0;
        $resultWithOutTax = 0.0;

        // себестоимость в валюте магазина
        $this->cartPrices['costPriceShopCurrency'] = 0;
        $this->cartPrices['basePrice'] = 0;
        $this->cartPrices['basePriceWithTax'] = 0;
        $this->cartPrices['discountedPriceWithoutTax'] = 0;
        $this->cartPrices['salesPrice'] = 0;
        $this->cartPrices['taxAmount'] = 0;
        $this->cartPrices['salesPriceWithDiscount'] = 0;
        $this->cartPrices['discountAmount'] = 0;
        $this->cartPrices['priceWithoutTax'] = 0;
        $this->cartPrices['subTotalProducts'] = 0;
        $this->cartData['duty'] = 1;

        $this->cartData['payment'] = 0; //could be automatically set to a default set in the globalconfig
        $this->cartData['paymentName'] = '';
        $cartpaymentTax = 0;

        $this->setCountryState($cart);

        $this->amountCart = 0;
        foreach ($cart->products as $name => $product) {

            $productId = $product->prod_id;

            if (empty($product->prod_quantity) || empty($product->prod_id)) {
                // todo translate it!
                cot_error('Error the quantity of the product for calculation is 0, please notify the shopowner,
                    the product id ' . $product->prod_id);
                continue;
            }

            $cartproductkey = $name;
            // не игнорируем кол-во товара, иначе зависимость цен от кол-ва не работает
            $product->prices = $pricesPerId[$cartproductkey] = $this->setProductPrices($product, $product->prod_quantity, false);

            $this->amountCart += $product->prod_quantity;
            $this->cartPrices[$cartproductkey] = $product->prices;

            $this->cartPrices['costPriceShopCurrency'] += $product->prices['costPriceShopCurrency'] * $product->prod_quantity;

            if($this->currencyDisplay->_priceConfig['basePrice']){
                $this->cartPrices['basePrice'] += $product->prices['basePrice'] * $product->prod_quantity;
            }

            if($this->currencyDisplay->_priceConfig['basePriceWithTax']) {
                $this->cartPrices['basePriceWithTax'] = $this->cartPrices['basePriceWithTax'] +
                    $product->prices['basePriceWithTax'] * $product->prod_quantity;
            }
            if($this->currencyDisplay->_priceConfig['discountedPriceWithoutTax']){
                $this->cartPrices['discountedPriceWithoutTax'] = $this->cartPrices['discountedPriceWithoutTax'] +
                    $product->prices['discountedPriceWithoutTax'] * $product->prod_quantity;
            }
            if($this->currencyDisplay->_priceConfig['salesPrice']){
                $this->cartPrices['salesPrice'] = $this->cartPrices['salesPrice'] +
                    $product->prices['salesPrice'] * $product->prod_quantity;
            }
            if($this->currencyDisplay->_priceConfig['taxAmount']){
                $this->cartPrices['taxAmount'] = $this->cartPrices['taxAmount']
                    + $product->prices['taxAmount'] * $product->prod_quantity;
            }
            if($this->currencyDisplay->_priceConfig['salesPriceWithDiscount']){
                $this->cartPrices['salesPriceWithDiscount'] = $this->cartPrices['salesPriceWithDiscount'] +
                    $product->prices['salesPriceWithDiscount'] * $product->prod_quantity;
            }
            if($this->currencyDisplay->_priceConfig['discountAmount']){
                $this->cartPrices['discountAmount'] = $this->cartPrices['discountAmount'] -
                    $product->prices['discountAmount'] * $product->prod_quantity;
            }
            if($this->currencyDisplay->_priceConfig['priceWithoutTax']){
                $this->cartPrices['priceWithoutTax'] = $this->cartPrices['priceWithoutTax'] +
                    $product->prices['priceWithoutTax'] * $product->prod_quantity;
            }


            if($this->currencyDisplay->_priceConfig['priceWithoutTax']){
                $this->cartPrices[$cartproductkey]['subtotal'] =
                    $product->prices['priceWithoutTax'] * $product->prod_quantity;
            }
            if($this->currencyDisplay->_priceConfig['taxAmount']){
                $this->cartPrices[$cartproductkey]['subtotal_tax_amount'] = $product->prod_subtotal_tax =
                    $product->prices['taxAmount'] * $product->prod_quantity;
            }
            if($this->currencyDisplay->_priceConfig['discountAmount']){
                $this->cartPrices[$cartproductkey]['subtotal_discount'] = $product->prod_subtotal_discount = -
                    $product->prices['discountAmount'] * $product->prod_quantity;
            }
            if($this->currencyDisplay->_priceConfig['salesPrice']){
                $this->cartPrices[$cartproductkey]['subtotal_with_tax'] = $product->prod_subtotal_with_tax =
                    $product->prices['salesPrice'] * $product->prod_quantity;
            }

        }

        $this->cartData['DBTaxRulesBill'] = $DBTaxRules = $this->gatherEffectingRulesForBill('DBTaxBill');

        $shipment_id = empty($cart->shipm_id) ? 0 : $cart->shipm_id;

        $this->calculateShipmentPrice($cart,  $shipment_id, $checkAutomaticSelected);

        $this->cartData['taxRulesBill'] = $taxRules = $this->gatherEffectingRulesForBill('TaxBill');
        $this->cartData['DATaxRulesBill'] = $DATaxRules = $this->gatherEffectingRulesForBill('DATaxBill');

        $this->cartPrices['discountBeforeTaxBill'] = $this->roundInternal($this->executeCalculation($DBTaxRules, $this->cartPrices['salesPrice']));
        $toTax = !empty($this->cartPrices['discountBeforeTaxBill']) ? $this->cartPrices['discountBeforeTaxBill'] : $this->cartPrices['salesPrice'];

        //We add the price of the Shipment before the tax. The tax per bill is meant for all services. In the other case people should use taxes per
        //  product or method
        $toTax = $toTax + $this->cartPrices['salesPriceShipment'];

        $this->cartPrices['withTax'] = $discountWithTax = $this->roundInternal($this->executeCalculation($taxRules, $toTax, true));
        $toDisc = !empty($this->cartPrices['withTax']) ? $this->cartPrices['withTax'] : $toTax;
        $cartTax = !empty($toDisc) ? $toDisc - $toTax : 0;

        $discountAfterTax = $this->roundInternal($this->executeCalculation($DATaxRules, $toDisc));
        $this->cartPrices['withTax'] = $this->cartPrices['discountAfterTax'] = !empty($discountAfterTax) ? $discountAfterTax : $toDisc;
        $cartdiscountAfterTax = !empty($discountAfterTax) ? $discountAfterTax- $toDisc : 0;

        $paymentId = empty($cart->paym_id) ? 0 : $cart->paym_id;

        $this->calculatePaymentPrice($cart, $paymentId, $checkAutomaticSelected);

        if($this->currencyDisplay->_priceConfig['salesPrice']){
            $this->cartPrices['billSub'] = $this->cartPrices['basePrice'] + $this->cartPrices['shipmentValue'] +
                $this->cartPrices['paymentValue'];
        }

        if($this->currencyDisplay->_priceConfig['discountAmount']) {
            $this->cartPrices['billDiscountAmount'] = $this->cartPrices['discountAmount'] +
                $this->cartPrices['discountBeforeTaxBill'] + $cartdiscountAfterTax;
        }

        if($this->currencyDisplay->_priceConfig['taxAmount']) {
            $this->cartPrices['billTaxAmount'] = $this->cartPrices['taxAmount'] + $this->cartPrices['shipmentTax'] + $this->cartPrices['paymentTax'] + $cartTax;

        }
        if($this->currencyDisplay->_priceConfig['salesPrice']){
            $this->cartPrices['billTotal'] = $this->cartPrices['salesPricePayment'] + $this->cartPrices['withTax'];
        }



        // Last step is handling a coupon, if given
        if (!empty($cart->coupon_code)) {
            $this->couponHandler($cart->coupon_code);
        }

        // Заполним цену заказа
        $cart->order_total = $this->cartPrices['billTotal'];
        $cart->order_salesPrice = $this->cartPrices['salesPrice'];
        $cart->order_billTaxAmount = $this->cartPrices['billTaxAmount'];
        $cart->order_billDiscountAmount = $this->cartPrices['billDiscountAmount'];
        $cart->order_discountAmount = $this->cartPrices['discountAmount'];
        $cart->order_subtotal = $this->cartPrices['priceWithoutTax'];
        $cart->order_subtotal_cost = $this->cartPrices['costPriceShopCurrency'];
        $cart->order_tax = $this->cartPrices['taxAmount'];
        $cart->order_shipment = $this->cartPrices['shipmentValue'];
        $cart->order_shipment_tax = $this->cartPrices['shipmentTax'];
        $cart->order_payment = $this->cartPrices['paymentValue'];
        $cart->order_payment_tax = $this->cartPrices['paymentTax'];
        if (!empty($cart->coupon_code)) {
            $cart->coupon_discount = $this->cartPrices['salesPriceCoupon'];
        }
        $cart->order_discount = $this->cartPrices['discountAmount'];  // зачем оно?

        return $this->cartPrices;
    }

    /**
     *
     * start the calculation, here it is for the product
     *
     * The function first gathers the information of the product (maybe better done with using the model)
     * After that the function gatherEffectingRulesForProductPrice writes the queries and gets the ids of the rules which affect the product
     * The function executeCalculation makes the actual calculation according to the rules
     *
     *
     * @param OrderItem|Product|int $product    Product or the Id of the product
     * @param float|int $amount Product quantity
     * @param bool $ignoreAmount
     *
     * @return array
     */
    public function setProductPrices ($product, $amount=0, $ignoreAmount=true) {
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
            $this->cats = array($product->page_cat);

        } //Use it as productId
        else {
            // Получить товар
            cot_error('No product given to getProductPrices');
        }

        if($cfg["shop"]['multix'] != 0 && empty($this->vendorCurrency )){
            $this->vendorCurrency = Vendor::getCurrencyId($this->productVendorId);
        }

        if (!empty($amount)) {
            $this->amount = $amount;
        }

        $this->setCountryState($this->cart);

        //For Profit, margin, and so on
        $this->rules['Marge'] = $this->gatherEffectingRulesForProductPrice('Marge', $this->product_marge_id);

        // Себестоимость
        $prices['costPrice'] = $costPrice;
        $basePriceShopCurrency = $this->roundInternal($this->currencyDisplay->convertCurrencyTo((int)$this->productCurrency,
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
            $this->revert = true;
            $prices['priceWithoutTax'] = $prices['salesPrice'] - $prices['taxAmount'];
            $afterTax = $this->roundInternal($this->executeCalculation($this->rules['VatTax'], $prices['salesPrice']),'salesPrice');

            if(!empty($afterTax)){
                $prices['taxAmount'] = $prices['salesPrice'] - $afterTax;
            }
            $this->revert = false;
        }

        // todo Посмотреть, может именно тут нада получать дополнительные цены
        // Дополнительные цены, Перекрываем даже override!!!
        if (!empty($product->add_prices)){
            $minApr = 0;

            foreach($product->add_prices as $key => $apr) {
                // проверяем группы
                if(!empty($apr['price_groups']) && count(array_intersect($apr['price_groups'], $this->shopperGroups))== 0 ){
                    continue;
                }
                // Проверяем кол-во товара:
                if ($ignoreAmount && ($apr['price_quantity_start'] > 0 || $apr['price_quantity_end'] > 0)) continue;
                if (!$ignoreAmount && $this->amount > 0){
                    if ($this->amount < $apr['price_quantity_start']) continue;
                    if ($apr['price_quantity_end'] > 0 && $this->amount > $apr['price_quantity_end']) continue;
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

        $prices['discountAmount'] = $this->roundInternal($basePriceWithTax - $prices['salesPrice']);

        //price Without Tax but with calculated discounts AFTER Tax. So it just shows how much the shopper saves, regardless which kind of tax
        $prices['priceWithoutTax'] = $salesPrice - $prices['taxAmount'];

        // Заполним в любом случае, чтобы можно было показывать цену без скидки
        $prices['basePriceWithTax'] = $basePriceWithTax;

        $prices['variantModification'] = $variant;

        $prices['DBTax'] = array();
        foreach($this->rules['DBTax'] as $dbtax){
            /** @var Calc $dbtax */
            $prices['DBTax'][] = array($dbtax->calc_title, $dbtax->calc_value, $dbtax->calc_value_mathop, $dbtax->calc_shopper_published);
        }

        $prices['Tax'] = array();
        foreach($this->rules['Tax'] as $tax){
            /** @var Calc $tax */
            $prices['Tax'][] =  array($tax->calc_title, $tax->calc_value, $tax->calc_value_mathop, $tax->calc_shopper_published);
        }

        $prices['VatTax'] = array();
        foreach($this->rules['VatTax'] as $tax){
            /** @var Calc $tax */
            $prices['VatTax'][] =  array($tax->calc_title, $tax->calc_value, $tax->calc_value_mathop, $tax->calc_shopper_published);
        }

        $prices['DATax'] = array();
        foreach($this->rules['DATax'] as $datax){
            /** @var Calc $datax */
            $prices['DATax'][] =  array($datax->calc_title, $datax->calc_value, $datax->calc_value_mathop, $datax->calc_shopper_published);
        }

        // Заполним цены в самом товаре
        $product->prod_price = $prices['costPriceShopCurrency'];
        $product->prod_base_price = $prices['basePrice'];
        $product->prod_basePriceWithTax = $prices['basePriceWithTax'];
        $product->prod_sales_price = $prices['salesPrice'];
        $product->prod_tax = $prices['taxAmount'];

        return $prices;
    }


    /**
     * Get coupon details and calculate the value
     * @param $code Coupon code
     * @return bool
     * @todo Calculate the tax
     */
    protected function couponHandler($code) {
        global $cfg;

        /** @var Coupon $coupon  */
        $coupon = Coupon::getByCode($code);
        if(!$coupon) return false;

        //Получить все товары, на которые не распросраняется скидка по купону
        $noDisc = 0;
        foreach($this->cart->products as $item){
            if($item->prod_no_coupon_discount == 1){
                $noDisc += $this->cartPrices[$item->prod_id]['subtotal_with_tax'];
            }

        }
        // Сумма от которой считаем купон, если он процентный
        $toDiscCoupon = $this->cartPrices['salesPrice'] - $noDisc;

        $_value_is_total = ($coupon->coupon_percent_or_total == 'total');
        $this->cartData['couponCode'] = $code;
        $this->cartData['couponDescr'] = ($_value_is_total ? '' : ' ('.(round($coupon->coupon_value) . '%)'));
        $this->cartPrices['couponValue'] = ($_value_is_total ? $coupon->coupon_value :
            ($toDiscCoupon * ($coupon->coupon_value / 100))
        );

        // TODO Calculate the tax
        $this->cartPrices['couponTax'] = 0;
        $this->cartPrices['salesPriceCoupon'] = $this->cartPrices['couponValue'] - $this->cartPrices['couponTax'];

        // Cумма из которой можно вычесть сумму купона
        $toDisc = $this->cartPrices['billTotal'] - $noDisc;

        $couponDisc = $toDisc - $this->cartPrices['salesPriceCoupon'];

        if ($couponDisc < 0) $couponDisc = 0.0;

        $newBillTotal = $couponDisc + $noDisc;
        $this->cartPrices['salesPriceCoupon'] = $this->cartPrices['billTotal'] - $newBillTotal;

        $this->cartPrices['billTotal'] = $newBillTotal;

    }

    /**
     * Function to execute the calculation of the gathered rules Ids.
     *
     * @param Calc[] $rules
     * @param float $baseprice
     * @param bool $relateToBaseAmount

     * @return float  the endprice
     */
    function executeCalculation($rules, $baseprice, $relateToBaseAmount=false) {

        if (empty($rules)) return 0;

        /** @var Calc[]  $rulesEffSorted */
        $rulesEffSorted = $this->record_sort($rules, 'ordering', $this->revert);

        $price = $baseprice;
        $finalprice = $baseprice;
        if (isset($rulesEffSorted)) {

            foreach ($rulesEffSorted as $rule) {

                if ($relateToBaseAmount) {
                    $cIn = $baseprice;
                } else {
                    $cIn = $price;
                }
                $cOut = $this->interpreteMathOp($rule->calc_value_mathop, $rule->calc_value, $cIn, $rule->curr_id);
                $this->cartPrices[$rule->calc_id . 'Diff'] = $this->roundInternal($this->roundInternal($cOut) - $cIn);

                //okey, this is a bit flawless logic, but should work
                if ($relateToBaseAmount) {
                    $finalprice = $finalprice + $this->cartPrices[$rule->calc_id . 'Diff'];
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

        if(!$this->revert){
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
                    if(!$this->revert){
                        $calculated = $price * $value / 100.0;
                    } else {
                        $calculated = $price /(1 +  (100.0 / $value));
                    }
                }
            } else if (strlen($mathop) == 1){
                $calculated = $this->currencyDisplay->convertCurrencyTo($currency, $value);
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

    private function setCountryState($cart = 0) {

        if (defined('COT_ADMIN') && COT_ADMIN == true) return;

        if (empty($cart)) {
            $cart = $this->cart;
        }
        if (empty($cart)) {
            $cart = ShopCart::getInstance();
        }
        $this->cart = $cart;

        if (!empty($this->cart->shipTo->oui_country)) {
            $this->deliveryCountry = $this->cart->shipTo->oui_country;
        } else if (!empty($this->cart->billTo->oui_country)) {
            $this->deliveryCountry = $this->cart->billTo->oui_country;
        }

        if (!empty($this->cart->shipTo->oui_region)) {
            $this->deliveryRegion = $this->cart->shipTo->oui_region;
        } else if (!empty($this->cart->billTo->oui_region)) {
            $this->deliveryRegion = $this->cart->billTo->oui_region;
        }
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
        /** @var Calc[] $allRules */
        $allRules = $this->allrules[$this->productVendorId][$entrypoint];

        //Cant be done with Leftjoin afaik, because both conditions could be arrays.
        foreach ($allRules as $i => $rule) {
            if(!empty($id)){
                if($rule->calc_id == $id){
                    $testedRules[] = $rule;
                }
                continue;
            }

            if(!empty($this->allrules[$this->productVendorId][$entrypoint][$i]->for_override)){
                continue;
            }

            $hitsCategory = true;
            if (isset($this->cats)) {
                $hitsCategory = $this->testRulePartEffecting(
                    $this->allrules[$this->productVendorId][$entrypoint][$i]->categories, $this->cats);
            }

            $hitsShopper = true;
            if (isset($this->shopperGroups)) {
                $hitsShopper = $this->testRulePartEffecting($this->allrules[$this->productVendorId][$entrypoint][$i]->user_groups,
                    $this->shopperGroups);
            }

            $hitsDeliveryArea = true;
            if (!empty($this->deliveryCountry) && !empty($this->allrules[$this->productVendorId][$entrypoint][$i]->countries)
                            && empty($this->allrules[$this->productVendorId][$entrypoint][$i]->regions)) {
                $hitsDeliveryArea = $this->testRulePartEffecting($this->allrules[$this->productVendorId][$entrypoint][$i]->countries, $this->deliveryCountry);
            } else if (!empty($this->deliveryRegion) && !empty($this->allrules[$this->productVendorId][$entrypoint][$i]->regions)) {
                $hitsDeliveryArea = $this->testRulePartEffecting($this->allrules[$this->productVendorId][$entrypoint][$i]->regions, $this->deliveryRegion);
            }

            $hitsAmount = true;
            if (!empty($this->amount)) {
                //Test
            }
            if ($hitsCategory && $hitsShopper && $hitsDeliveryArea) {
                $testedRules[] = $rule;
            }
        }

        //Test rules in plugins
        // TODO Hook here

        return $testedRules;
    }

    /**
     * Gathers the effecting rules for the calculation of the bill
     *
     * @param	$entrypoint
     * @param int $cartVendorId
     * @return array The rules that effects the Bill as Ids
     */
    function gatherEffectingRulesForBill($entrypoint, $cartVendorId = 1) {
        global $db;

        //Test if calculation affects the current entry point
        //shared rules counting for every vendor seems to be not necessary

        $cond = array(
            array('calc_kind', $entrypoint),
            array('calc_published', 1),
            array('SQL', "`vendor_id`={$cartVendorId} OR `calc_shared`=1"),
            array('SQL', "calc_publish_up = ".$db->quote($this->nullDate) ."
                                                    OR calc_publish_up <= ".$db->quote($this->now)),
            array('SQL', "calc_publish_down = ".$db->quote($this->nullDate)."
                                                    OR calc_publish_down>=".$db->quote($this->now)),
        );
        $rules = Calc::find($cond, 0, 0, 'calc_kind ASC');
        if(!$rules) return array();

        $testedRules = array();
        foreach ($rules as $rule) {
            $hitsDeliveryArea = true;
            if (!empty($rule->countries) && empty($rule->regions)) {
                $hitsDeliveryArea = $this->testRulePartEffecting($rule->countries, $this->deliveryCountry);
            } else if (!empty($rule->regions)) {
                $hitsDeliveryArea = $this->testRulePartEffecting($rule->regions, $this->deliveryRegion);
            }

            $hitsShopper = true;
            if (isset($this->shopperGroups)) {
                $hitsShopper = $this->testRulePartEffecting($rule->user_groups, $this->shopperGroups);
            }
            if ($hitsDeliveryArea && $hitsShopper) {
                $testedRules[] = $rule;
            }
        }

        //Test rules in plugins
        // TODO add hook

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

        $this->cartData['shipmentName'] = $L['shop']['cart_no_shipment_selected'];
        $this->cartPrices['shipmentValue'] = 0; //could be automatically set to a default set in the globalconfig
        $this->cartPrices['shipmentTax'] = 0;
        $this->cartPrices['shipmentTotal'] = 0;
        $this->cartPrices['salesPriceShipment'] = 0;
        // check if there is only one possible shipment method

        $automaticSelectedShipment =   $cart->CheckAutomaticSelectedShipment($this->cartPrices, $checkAutomaticSelected);
        if ($automaticSelectedShipment) $ship_id = $cart->shipm_id;
        if (empty($ship_id)) return;

        // Handling shipment plugins
        $method = ShipmentMethod::getById($ship_id);

        if (!$method || !cot_plugin_active($method->pl_code) || !cot_auth('plug', $method->pl_code, 'R')){
            $cart->shipmentMethod = 0;
            $this->cartData['shipmentName'] = $L['shop']['cart_no_shipment_selected'];
            return $this->cartPrices;
        }
        /* === Hook === */
        //plgVmonSelectedCalculatePriceShipment
        // Плагин должен заполнить $this->cartPrices !!!
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
            $this->cartData['shipmentName'] = $L['shop']['cart_no_shipment_selected'];
        }else{
            // Установить название метода оплаты
            $this->cartData['shipmentName'] = cot_rc('shipmentName', array(
                'title' => htmlspecialchars($method->shipm_title),
                'desc'  => htmlspecialchars($method->shipm_desc),
            ));

        }

        return $this->cartPrices;
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

        $this->cartData['paymentName'] = $L['shop']['cart_no_payment_selected'];
        $this->cartPrices['paymentValue'] = 0; //could be automatically set to a default set in the globalconfig
        $this->cartPrices['paymentTax'] = 0;
        $this->cartPrices['paymentTotal'] = 0;
        $this->cartPrices['salesPricePayment'] = 0;

        // check if there is only one possible payment method
        $cart->automaticSelectedPayment =   $cart->CheckAutomaticSelectedPayment( $this->cartPrices, $checkAutomaticSelected);
        if ($cart->automaticSelectedPayment) $payment_id=$cart->paym_id;
        if (empty($payment_id)) return;

        // Handling payment plugins
        $method = $cart->paymentMethod;
        if (!$method  || !cot_plugin_active($method->pl_code) || !cot_auth('plug', $method->pl_code, 'R')){
            $cart->paymentMethod = 0;
            $this->cartData['paymentName'] = $L['shop']['cart_no_payment_selected'];
            return $this->cartPrices;
        }
        /* === Hook === */
        //plgVmonSelectedCalculatePricePayment
        // Плагин должен заполнить $this->cartPrices !!!
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
            $this->cartData['paymentName'] = $L['shop']['cart_no_payment_selected'];
        }else{
            // Установить название метода оплаты
            $this->cartData['paymentName'] = cot_rc('paymentName', array(
                'title' => htmlspecialchars($method->paym_title),
                'desc'  => htmlspecialchars($method->paym_desc),
            ));

        }

        return $this->cartPrices;
    }

    /**
     * Standard round function, we round every number with 6 fractionnumbers
     * We need at least 4 to calculate something like 9.25% => 0.0925
     * 2 digits
     * Should be setable via config (just for the crazy case)
     */
    function roundInternal($value, $name = '') {
        if($name != ''){
            if(isset($this->currencyDisplay->_priceConfig[$name][1])){
                return round($value,$this->currencyDisplay->_priceConfig[$name][1]);
            } else {
                return round($value, $this->internalDigits);
            }
        } else {
            return round($value, $this->internalDigits);
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

                $keyToUse = $record->$field;
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

    public function getCartData() {
        return $this->cartData;
    }

}