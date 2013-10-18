<?php
/**
 * Model class for the cart
 *
 * @package shop
 * @subpackage cart
 * @author Alex
 * @copyright http://portal30.ru
 * @todo orderExtra Felds
 */
defined('COT_CODE') or die('Wrong URL.');

// $_SESSION['__shop']['cart'] - сессия корзины

/**
 * Class ShopCart
 * @property bool $dataValid;
 */
class ShopCart Extends Order{
    /**
     * SQL table name
     * @var string
     */
    public static $_table_name = '';

    /**
     * @var string
     */
    public static $_primary_key = '';

    /**
     * Column definitions
     * @var array
     */
    public static $_columns = array();

    // ==== Свойства корзины ====
    protected $_inCheckOut = false;
    protected $_dataValidated = false;
    protected $_confirmDone = false;
    protected $_lastError = null; // Used to pass errmsg to the cart using ajax methods
    protected $_redirect;
    protected $automaticSelectedShipment = false;
    protected $automaticSelectedPayment  = false;

    public $STsameAsBT  =   false;
    public $tosAccepted = null;      // Условия обслуживания приняты
    /**
     * Расширенный массив цен. Нужен при выводе товара и формировании заказа:
     * Типа: Старая цена, цена со скидкой, НДС и т.п.
     * @var array
     */
    var $pricesUnformatted = null;

    /**
     *
     * @var null
     */
    var $cartData = null;

    /**
     * Для хранения единственного экземпляра
     * @var null
     */
    private static $_cart = null;
    /**
     * Число попыток проверки купона
     * @var int
     */
    private static $_triesValidateCoupon;

    /**
     * Static constructor
     */
    public static function __init(){
        global $db_shop_orders;

        self::$_table_name = $db_shop_orders;
        self::$_primary_key = 'order_id';

        parent::__init();
    }

    public function __construct() {
        global $cfg;

        $class = get_called_class();
        $backtrace = debug_backtrace();

        // Делаем конструктор приватным. Делаем так потому что у родителя конструктор публичный
        if(mb_strpos($backtrace[0]['file'], 'models'.DS.'ShopCart.php') === false){
            throw new Exception("Creating new Instance of \"{$class}\" not allowed. Use \"{$class}::getInstance\" instead");
        }

        parent::__construct();

        $this->useSSL = $cfg["shop"]['useSSL'];
        self::$_triesValidateCoupon = 0;

        $this->_data['vendor_id'] = 1;

    }

    /**
     * запрещаем клонирование объекта
     */
    private function __clone() { }
    private function __wakeup() { }



    /**
     * Get the cart from the session
     * Глобальная точка доступа к единственнгому экземпляру корзины
     * @param bool $setCart
     * @param array $options
     * @internal param array $cart the cart to store in the session
     * @return \ShopCart
     */
    public static function getInstance($setCart = true, $options = array()) {
        if(empty(self::$_cart)){
            if (!empty($_SESSION['__shop']['cart'])) {
                //$cartData = unserialize( $_SESSION['__shop']['cart'] );
                self::$_cart = unserialize( $_SESSION['__shop']['cart'] );
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
     * Set non product info in object
     * @todo дописать метод
     */
    public function setPreferred() {
        global $usr, $cfg;

        // обработка адреса пользователя
        if (empty($this->billTo) && $usr['id'] > 0 ) {
            $this->saveAddress($usr["profile"], 'BT', false);
        }

        // Пользователь мог авторизоваться и по этому нужно проверять не только установлен ли user_id, но и он должен
        // быть больше нуля
        if(empty($this->_data['user_id'])) $this->_data['user_id'] = $usr['id'];
        if($this->_data['user_id'] != $usr['id'] && !cot_auth('shop', 'any', 'A')) $this->_data['user_id'] = $usr['id'];

        // @todo fix me
//        if (empty($this->shipmentmethod_id) && !empty($user->shipmentmethod_id)) {
//            $this->shipmentmethod_id = $user->shipmentmethod_id;
//        }
//
//        if (empty($this->paymentmethod_id) && !empty($user->paymentmethod_id)) {
//            $this->paymentmethod_id = $user->paymentmethod_id;
//        }

        //$this->tosAccepted is due session stuff always set to 0, so testing for null does not work
        // TODO FIX it
        if((!empty($user->agreed) || !empty($this->billTo->agreed)) && !$cfg["shop"]['agree_to_tos_onorder'] ){
            $this->tosAccepted = 1;
        }
    }

    public function add($product, $quantity, &$errorMsg=''){
        global $cfg, $L;
//        global $cfg, $usr, $db_shop_order_items, $db, $sys;

        if(is_int($product)) $product = Product::getById($product);
        if (!$product){
            $errorMsg = $L['shop']['product_not_found'];
            return false;
        }

        if ($product->allow_decimal_quantity){
            // Дробное кол-во товара
            $quantity = trim(str_replace(',','.', $quantity));
            $quantity = (float)$quantity;
        }else{
            $quantity = (int)$quantity;
        }

        // TODO Hook для добавления в корзину

        // Если товар уже есть в корзине
        if (array_key_exists($product->prod_id, $this->products) ) {

            $errorMsg = $L['shop']['cart_product_updated'];
            $totalQuantity = $this->products[$product->prod_id]->prod_quantity + $quantity;
            if ($product->checkForQuantities($totalQuantity ,$errorMsg)) {
                $this->products[$product->prod_id]->prod_quantity = $totalQuantity;
            }
            // Если товара в корзине нет
        }  else {
            if ($product->checkForQuantities($quantity, $errorMsg)) {
                $this->products[$product->prod_id] = new OrderItem($product);
                $this->products[$product->prod_id]->prod_quantity = $quantity;
            } else {
                // PRODUCT OUT OF STOCK
            }
        }

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
    public function updateProduct($product_id = 0, $quantity = 0.0) {

        //		foreach($product_ids as $product_id){
        $updated = false;

        if (array_key_exists($product_id, $this->products)) {
            $product = Product::getById($product_id);
            if(!$product) return false;

            if (!empty($quantity) && $quantity > 0) {
                $errorMsg = '';
                if ($product->checkForQuantities($quantity, $errorMsg)) {
                    $this->products[$product_id]->prod_quantity = $quantity;
                    $this->save();
                    $updated = true;
                }else{
                    $this->setError($errorMsg);
                }
            } else {
                //Todo when quantity is 0,  the product should be removed, maybe necessary to gather in array and execute delete func
                $this->deleteProduct($product_id);
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
     * @param int $prod_id
     * @return bool
     */
    public function deleteProduct($prod_id) {
        $prod_id = (int)$prod_id;
        if (empty($prod_id)) return false;

        unset($this->products[$prod_id]);

        $this->save();
        return true;
    }

    /**
     * Заказ в процессе оформления?
     * @return bool
     */
    public function getInCheckOut() {
        return $this->_inCheckOut;
    }

    /**
     * Save Cart
     */
    public function save(){
        // Сэкономим не много места в сесии
        if ($this->products) {
            foreach($this->products as $key => &$product){
                //Important DO NOT UNSET price
                $product->prices = NULL;
            }
        }
        $_SESSION['__shop']['cart'] = serialize($this);
    }

    /**
     * Remove the cart from the session
     * @access public
     */
    public function removeFromSession() {
        $_SESSION['__shop']['cart'] = 0;
    }

    /**
     * Сохранить адрес в сессии корзины
     * @param type $data
     * @param string $type 'BT' или 'ST'
     * @param bool $putIntoSession - сразу сохранить в сессию
     */
    function saveAddress($data, $type, $putIntoSession = true) {
        global $cfg, $usr;

//        $prepareUserFields = Userfields::getUserFields($type);

        if ($type =='ST') {
            if(!($data instanceof OrderUserInfo)){
                if(is_array($data)) $data['oui_address_type'] = 'ST';
                $data = new OrderUserInfo($data);
            }
        } else { // BT
            if(!($data instanceof OrderUserInfo)){
                $data['oui_address_type'] = 'BT';
                $data = new OrderUserInfo($data);
            }
            if(!empty($data->agreed)){
                $this->tosAccepted = $data->agreed;
            }

            if(empty($data->email) && $usr['id'] > 0){
                $data->email = $usr["profile"]["user_email"];
            }
        }

        $type = ($type == 'BT') ? 'billTo' : 'shipTo';

        $this->{$type} = $data;

        if($putIntoSession){
            $this->save();
        }

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

            $ships = ShipmentMethod::getListByUserId($usr['id'], $this->vendor_id);

            /* == Hook == */
            // см $returnValues = $dispatcher->trigger('plgVmOnCheckAutomaticSelectedShipment', array(  $this,$cart_prices, &$shipCounter));

            $nbShipment = count($ships);
            if($nbShipment == 0){
                $this->automaticSelectedShipment=false;
                $this->save();
                return false;
            }

            $tmp = array_shift($ships);
            $shipmentmethod_id = (int)$tmp->shipm_id;

            if ($nbShipment==1 && $shipmentmethod_id) {
                $this->automaticSelectedShipment=true;
                $this->shipmentMethod = $shipmentmethod_id;
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
            $pays = PaymentMethod::getListByUserId($usr['id'], $this->vendor_id);

            /* == Hook == */
            // см $returnValues = $dispatcher->trigger('plgVmOnCheckAutomaticSelectedPayment', array( $this, $cart_prices, &$paymentCounter));

            $nbPayment = count($pays);
            if($nbPayment == 0){
                $this->automaticSelectedPayment=false;
                $this->save();
                return false;
            }
            $tmp = array_shift($pays);
            $paymentmethod_id = (int)$tmp->paym_id;

            if ($nbPayment==1 && $paymentmethod_id) {
                $this->automaticSelectedPayment=true;
                $this->paymentMethod = $paymentmethod_id;
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

        $this->order_user_currency_id = $currency->getCurrencyDisplay();

        // TODO возможно hook для плагинов влюяющих на валюту оплаты

        $this->cartData = $calculator->getCartData();

        // Скорее всего в оформленном заказе не нужно перезаписывать правила расчета, по этому добавим их тут
        $calculation_kinds = array('DBTaxRulesBill', 'taxRulesBill', 'DATaxRulesBill');
        $this->calc_rules = array();
        foreach($calculation_kinds as $calculation_kind) {
            foreach($this->cartData[$calculation_kind] as $rule){
                $data = array(
                    'vendor_id' => $this->vendor_id,
                    'calc_title' => $rule->calc_title,
                    'calc_kind' => $calculation_kind,
                    'calc_value' => $this->pricesUnformatted[$rule->calc_id.'Diff'],
                );
                // Set it to cart
                $this->calc_rules[] = $data;
            }
        }

    }

    /**
     * Calculate Cart Prices
     *
     * @param bool $checkAutomaticSelected
     * @return array prices
     */
    public function getCartPrices($checkAutomaticSelected=true) {
        $calculator = calculationHelper::getInstance();
        $this->pricesUnformatted = $calculator->setCheckoutPrices($this, $checkAutomaticSelected);
        return $this->pricesUnformatted;
    }

    /**
     * emptyCart
     */
    public function emptyCart(){

        //We delete the old stuff
        $this->order_id = 0;
        $this->billTo->order_id = 0;
        $this->billTo->oui_id = 0;
        if(!empty($this->shipTo)){
            $this->shipTo->oui_id = 0;
            $this->shipTo->order_id = 0;
        }
        $this->products = array();
        $this->_inCheckOut = false;
        $this->_dataValidated = false;
        $this->_confirmDone = false;
        $this->order_customer_note = '';
        $this->coupon_code = '';
        $this->tosAccepted = null;

        foreach($this->_data as $key => $val){
            if(!in_array($key, array('user_id', 'vendor_id', 'paym_id', 'shipm_id'))){
                $this->_data[$key] = NULL;
            }
        }


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
     * Validate order Data
     * @param bool $redirect
     * @return boolean
     * @todo перенести в контроллер заполнение данных CartController::checkoutAction()
     */
    public function checkoutData($redirect = true) {
        global $L, $cfg, $usr, $cot_extrafields, $db_shop_orders;

        $this->_redirect = $redirect;
        // Возможно надо безусловно $this->_inCheckOut = true;
        if ($redirect) $this->_inCheckOut = true;

        // Возможно нужно ввести по-умолчанию, если tosAccepted - не передано, использовать $this->tosAccepted;
        if (isset($_POST['tosAccepted'])) $this->tosAccepted = cot_import('tosAccepted', 'P', 'INT');

        // Возможно нужно ввести по-умолчанию, если customer_comment - не передано, использовать $this->customer_comment;
        if (isset($_POST['customer_comment'])) $this->order_customer_note = cot_import('customer_comment', 'P', 'TXT');

        // Extra fields
        if(!empty($cot_extrafields[$db_shop_orders])){
            foreach ($cot_extrafields[$db_shop_orders] as $exfld){
                $field = "order_{$exfld['field_name']}";
                $this->{$field} = cot_import_extrafields('order'.$exfld['field_name'], $exfld, 'P', $this->{$field});
            }
        }


        $shipto = null;

        $bt_as_st = cot_import('bt_as_st', 'P', 'BOL');
        if ($bt_as_st) $this->STsameAsBT = true;
        $this->save();

        if ( count($this->products) == 0) {
            // Редирект на главную магазина
            $cat = shop_readShopCats();
            $cat = $cat[0];
            $continue_link = cot_url('page', array('c'=>$cat), '', true);
            return $this->redirecter($continue_link, $L['shop']['cart_no_product']);
        } else {
            foreach ($this->products as $item) {
                $redirectMsg = '';
                $product = Product::getById($item->prod_id);
                if (!$product->checkForQuantities($item->prod_quantity, $redirectMsg)) {
                    return $this->redirecter(cot_url('shop', array('m'=>'cart'), '', true), $this->getError());
                }
            }
        }

        // Check if a minimun purchase value is set
        if (($redirectMsg = $this->checkPurchaseValue()) != null) {
            return $this->redirecter(cot_url('shop', 'm=cart', '', true) , $redirectMsg);
        }

        //But we check the data again to be sure
        if (empty($this->billTo)) {
            $redirectMsg = '';
            return $this->redirecter(cot_url('shop', array('m'=>'user', 'a'=>'editaddress',
                                                           'addrtype'=> 'BT'), '', true), $redirectMsg);
        } else {
            $redirectMsg = $this->billTo->validate();
            if ($redirectMsg !== TRUE) {
                return $this->redirecter(cot_url('shop', array('m'=>'user', 'a'=>'editaddress',
                                                               'addrtype'=>'BT', 'r'=>'cart'), '', true) , $redirectMsg);
            }
        }


        if($this->STsameAsBT){
            // Не надо копировать адрес
//            $this->shipTo = $this->billTo;
//            $this->shipTo->oui_address_type = 'ST';
        } else {
            //Only when there is an ST data, test if all necessary fields are filled
            if (!empty($this->shipTo)) {
                $redirectMsg = $this->shipTo->validate();
                if ($redirectMsg !== TRUE) {
                    return $this->redirecter(cot_url('shop', array('m'=>'user', 'a'=>'editaddress',
                                                                   'addrtype'=>'ST', 'r'=>'cart'), '', true) , $redirectMsg);
                }
            }
        }

        // Test Coupon
        if (!empty($this->couponCode)) {
            $prices = $this->getCartPrices();
            $coupon = Coupon::getByCode($this->couponCode);
            $couponValid = true;
            if(self::$_triesValidateCoupon < 8){
                if(!$coupon || !$coupon->isValid($prices['salesPrice'])){
                    $couponValid = false;
                }
            } else{
                $couponValid = false;
                $redirectMsg = $L['shop']['coupon_notfound'];
            }
            self::$_triesValidateCoupon++;
            if (!$couponValid) {
                $this->couponCode = '';
                return $this->redirecter(cot_url('shop', array('m'=>'cart'), '', true), $redirectMsg);

            }
        }

        //Test Shipment and show shipment plugin
        if (empty($this->shipm_id)) {
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
        if (empty($this->paym_id)) {
            return $this->redirecter(cot_url('shop', array('m'=>'cart', 'a'=>'edit_payment'), '', true),
                $L['shop']['cart_no_payment_selected']);
        } else {
            $redirectMsg = ''; // Временно
            /* === Hook === */
            $cart = array($this); // Чтобы плагины могли работать с корзиной

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
     * Проверка на минимальную сумму заказа (из нстроек продавца)
     * Check if a minimum purchase value for this order has been set, and if so, if the current
     * value is equal or hight than that value.
     * @return An error message when a minimum value was set that was not eached, null otherwise
     * @todo - это заглушка. Дописать
     */
    private function checkPurchaseValue() {
        global $cfg;

        return null;
    }

    /**
     * Создать заказ и сохранить его
     * This function is called, when the order is confirmed by the shopper.
     * Here are the last checks done by payment plugins.
     *
     * @return int|void
     */
    public function createOrder(){
        global $L;

        $this->checkoutData();
        if (!$this->_dataValidated) {
            cot_error($L['shop']['cart_data_not_valid']);
            cot_redirect(cot_url('shop', array('m'=>'cart'), '', true));
        }
        $this->_confirmDone = true;

        if($this->order_id > 0) return $this->order_id;

        if(empty($this->_data['order_currency'])){
            $this->_data['order_currency'] = Vendor::getCurrencyId($this->_data['vendor_id']);
        }
        $currency = CurrencyDisplay::getInstance();
        if(!empty($currency->exchangeRateShopper)){
            $this->order_user_currency_rate = $currency->exchangeRateShopper;
        } else {
            $this->order_user_currency_rate = 1.0;
        }

        $order_id = parent::save();

        /* === Hook === */
        $redirectUrl = '';
        foreach (cot_getextplugins('shop.order.confirm.done') as $pl){
            include $pl;
        }
        // Если нет ошибок очищаем корзину
        // Плагин может установить свой $redirectUrl,
        // TODO хотя плагину ничто не мешает самостоятельно средиректить
        if (!cot_error_found()){
            $this->emptyCart();   // TODO не забыть включить!
            // Все ок. Ничего не делаем.
            return $order_id;

        }else{
            $redirectUrl = ($redirectUrl != '') ? $redirectUrl : cot_url('shop', 'm=cart', '', true);
        }
        if ($redirectUrl != '') cot_redirect($redirectUrl);

    }

    /**
     * Render the code for Ajax Cart
     * @return array
     */
    function prepareAjaxData(){
        global $cfg;

        $this->prepareCartData(false);
        $weight_total = 0;
        $weight_subtotal = 0;

        $data = new stdClass();
        $data->products = array();
        $data->totalProduct = 0;
        $currency = CurrencyDisplay::getInstance();
        $i=0;
        foreach ($this->products as $priceKey => $product){

            $category_id = $product->page_cat;
            // Create product URL
            $product->page_alias = trim($product->page_alias);
            $url = array('c' => $category_id);
            if ($product->page_alias != ''){
                $url['al'] = $product->page_alias;
            }else{
                $url['id'] = $product->page_id;
            }
            $url = cot_url('page', $url);
            // /Create product URL

            // @todo Add variants
            // @TODO i18n
            $data->products[$i]['product_name'] = '<a href="'.$url.'">'.$product->prod_title.'</a>';
            $data->products[$i]['page_title'] = $product->prod_title;
            // Для вывода в AJAX без '_'
            $data->products[$i]['url'] = $url;

            $data->products[$i]['product_sku'] = $product->prod_sku;

            //** @todo WEIGHT CALCULATION
            // product Price total for ajax cart
            $data->products[$i]['pricesUnformatted'] = $product->prod_subtotal_with_tax;
            $data->products[$i]['prices'] = $currency->priceDisplay( $product->prod_subtotal_with_tax );


            // other possible option to use for display
            $data->products[$i]['subtotal'] = $this->pricesUnformatted[$priceKey]['subtotal']; // todo избавиться от $this->pricesUnformatted[$priceKey]
            $data->products[$i]['subtotal_tax_amount'] = $product->prod_subtotal_tax;
            $data->products[$i]['subtotal_discount'] = $product->prod_subtotal_discount;
            $data->products[$i]['subtotal_with_tax'] = $product->prod_subtotal_with_tax;

            // UPDATE CART / DELETE FROM CART
            $data->products[$i]['quantity'] = (float)$product->prod_quantity;
            $data->totalProduct += (float)$product->prod_quantity;

            $i++;
        }
        $data->billTotal = $currency->priceDisplay( $this->pricesUnformatted['billTotal'] );
        $data->dataValidated = $this->_dataValidated;
        return $data ;
    }


    public function getDataValid() {
        return $this->_dataValidated;
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
// Class initialization for some static variables
ShopCart::__init();