<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Controller class for the cart
 *
 * @package shop
 * @subpackage cart
 * @author Alex
 * @copyright http://portal30.ru
 * @todo Методы JS переименовать в  ajx
 */
class CartController{
    
    protected $useSSL = 0;
    protected $useXHTML = true;
    
    /**
     * Construct the cart
     * @access public
     */
    public function __construct() {
        global $cfg;
        if ($cfg["shop"]['use_as_catalog']) {
            cot_redirect(cot_url('index'));
        } else {

        }
        $this->useSSL = $cfg["shop"]['useSSL'];
        $this->useXHTML = true;
    }
    
    /**
     * Main (index) Action.
     * Show Cart
     */
    public function indexAction(){
        global $L, $cfg, $out, $usr, $R, $redirect, $sys, $cot_plugins, $cart, $cot_countries;
        
        $cart = ShopCart::getInstance(true);
        // Для отладки можно очистить корзину
//        $cart->removeFromSession();
//        die;

        $cart->prepareCartData();

        // Адреса доставки
        $shipToArr = array();
        if ($usr['id'] > 0){
            $shipToArr = UserInfo::find("user_id={$usr['id']}", 0, 0, 'ui_title');
            if (!$shipToArr) $shipToArr = array();
        }

    	$continue_url = $this->getContinueLink();
		$totalInPaymentCurrency = $this->getTotalInPaymentCurrency($cart);
        // первичная проверка данных корзины
        if ($cart && !$cfg["shop"]['use_as_catalog']) {
            $cart->checkoutData(false);
        }

        $crumbs = array(
            array($cfg["shop"]['mainPageUrl'], $cfg["shop"]['mainPageTitle']),
        );

        if ($cart->dataValid) {
            $crumbs[] = $L['shop']['order_confirm_mnu'];
            $out['subtitle'] = $L['shop']['order_confirm_mnu'];
            $out['canonical_uri'] = cot_url('shop', array('m'=>'cart'));
            $checkout_task = 'confirm';
        } else {
            $crumbs[] = $L['shop']['cart_overview'];
            $sys['sublocation'] = $L['shop']['cart_overview'];
            $out['subtitle'] = $L['shop']['cart_overview'];
            $out['canonical_uri'] = cot_url('shop', array('m'=>'cart'));

            $checkout_task = 'checkout';
        }
        $breadcrumbs = cot_breadcrumbs($crumbs, $cfg['homebreadcrumb'], true);

        $currency = CurrencyDisplay::getInstance();

		$useSSL = $cfg["shop"]['useSSL'];

        $cart->save();

        global $priceTpl, $couponTpl, $loginTpl;
        $priceTpl = cot_tplfile('shop.cart.prodlist');
        $couponTpl = cot_tplfile('shop.coupon');
        $loginTpl = cot_tplfile('shop.user.login_form');

        // Все глобальные переменные определяем до инициализации шаблона
        $t = new XTemplate(cot_tplfile('shop.cart'));

        // === Payment Methods ===
        if (!$this->checkPaymentMethodsConfigured()) {

        }else{
            $selectedPayment = (empty($cart->paym_id) ? 0 : $cart->paym_id);
            $paymentMethods = PaymentMethod::getListByUserId($usr['id'], $cart->vendor_id);

            /* === Hook === */
            // Все плагины оплаты просматривают массив $paymentMethods и исключают из него те методы, которые не
            // подходят для данного заказа
            // Также они заполняют цены за использование своего в соотвествии со своими правилами см
            //    vmpsplugin.php стр. 130
            // plgVmDisplayListFEPayment

            foreach ($paymentMethods as $key => $method){
                if (!$method || !cot_plugin_active($method->pl_code) || !cot_auth('plug', $method->pl_code, 'R')){
                    unset($paymentMethods[$key]);
                    continue;
                }
                $paymentValid = true;
                $paymentMethods[$key]->totalPrice = 0;
                if (!empty($cot_plugins['shop.payment.list'])){
                    foreach($cot_plugins['shop.payment.list'] as $k){
                        if ($k['pl_code'] == $method->pl_code){
                            include $cfg['plugins_dir'] . '/' . $k['pl_file'];
                        }
                    }
                }
                if (cot_error_found() || !$paymentValid) {
                    unset($paymentMethods[$key]);
                }
            }

            /* === /Hook === */
            reset($paymentMethods);
            $methods = array();
            $i = 0;
            $t->assign(array(
                'PAYMENT_SELECTED' => $selectedPayment
            ));
            foreach($paymentMethods as $key => $method){
                $method = $method->toArray();
                $method['salesPrice'] = $currency->priceDisplay($method['totalPrice']);

                $methods[$method['paym_id']] = $method['paym_title']." ({$method['paym_desc']})";
                if ($method['totalPrice'] > 0){
                    $methods[$method['paym_id']] .= "<strong>{$method['salesPrice']}</strong>";
                }
                // TODO учесть возможность вывода кастомного заголовка метода плагином оплаты
                $t->assign(array(
                    'METHOD_ROW_NUM' => $i,
                    'METHOD_ROW_ID' => $method['paym_id'],
                    'METHOD_ROW_TITLE' => htmlspecialchars($method['paym_title']),
                    'METHOD_ROW_DESC' => htmlspecialchars($method['paym_desc']),
                    'METHOD_ROW_SALES_PRICE' => $method['salesPrice'],
                    'METHOD_ROW' => $method,
                    'METHOD_ROW_ODDEVEN' => cot_build_oddeven($i),
                ));
                $t->parse('MAIN.SELECT_PAYMENT.ROW');
            }
            $t->assign(array(
                'PAYMENT_METHOD_RADIO' => cot_radiobox($selectedPayment, 'paymentmethod_id',
                    array_keys($methods), array_values($methods), '', '<br />'),
                'SCROLL_TOP' => cot_import('st', 'G', 'INT'),   // Параметр для прокрутки экрана
            ));
            $t->parse('MAIN.SELECT_PAYMENT');
        }
        // === /Payment оплаты ===

        // === Shipment Methods ===
        if (!$this->checkShipmentMethodsConfigured()) {

        }else{
            $selectedShipment = (empty($cart->shipm_id) ? 0 : $cart->shipm_id);
            $shipmentMethods = ShipmentMethod::getListByUserId($usr['id'], $cart->vendor_id);

            /* === Hook === */
            // Все плагины доставки просматривают массив $shipmentMethods и исключают из него те методы, которые не
            // подходят для данного заказа (устанавливают $shipmentValid в false)
            // Также они заполняют цены доставки в соотвествии со своими правилами см vmpsplugin.php стр. 130
            // plgVmDisplayListFEShipment
            foreach ($shipmentMethods as $key => $method){
                if (!$method || !cot_plugin_active($method->pl_code) || !cot_auth('plug', $method->pl_code, 'R')){
                    unset($shipmentMethods[$key]);
                }
                $shipmentValid = true;
                $shipmentMethods[$key]->totalPrice = 0;
                if (!empty($cot_plugins['shop.shipment.list'])){
                    foreach($cot_plugins['shop.shipment.list'] as $k){
                        if ($k['pl_code'] == $method->pl_code){
                            include $cfg['plugins_dir'] . '/' . $k['pl_file'];
                        }
                    }
                }
                if (cot_error_found() || !$shipmentValid) {
                    unset($shipmentMethods[$key]);
                }
            }
            /* === /Hook === */
            reset($shipmentMethods);

            $methods = array();
            $i = 0;
            $t->assign(array(
                'SHIPMENT_SELECTED' => $selectedShipment
            ));

            foreach($shipmentMethods as $key => $method){
                $method = $method->toArray();
                $method['salesPrice'] = $currency->priceDisplay($method['totalPrice']);

                $methods[$method['shipm_id']] = $method['shipm_title']." ({$method['shipm_desc']})";
                if ($method['totalPrice'] > 0){
                    $methods[$method['shipm_id']] .= "<strong>{$method['salesPrice']}</strong>";
                }
                $t->assign(array(
                    'METHOD_ROW_NUM' => $i,
                    'METHOD_ROW_ID' => $method['shipm_id'],
                    'METHOD_ROW_TITLE' => htmlspecialchars($method['shipm_title']),
                    'METHOD_ROW_DESC' => htmlspecialchars($method['shipm_desc']),
                    'METHOD_ROW' => $method,
                    'METHOD_ROW_SALES_PRICE' => $method['salesPrice'],
                    'METHOD_ROW_ODDEVEN' => cot_build_oddeven($i),
                ));
                $t->parse('MAIN.SELECT_SHIPMENT.ROW');
            }
            $t->assign(array(
                'SHIPMENT_METHOD_RADIO' => cot_radiobox($selectedShipment, 'shipmentmethod_id',
                    array_keys($methods), array_values($methods), '', '<br />'),
                'SCROLL_TOP' => cot_import('st', 'G', 'INT'),   // Параметр для прокрутки экрана
            ));
            $t->parse('MAIN.SELECT_SHIPMENT');
        }
        // === /Shipment Methods ===

        // === Ship to adresses select
        if ($usr['id'] > 0){
            $selectedShipTo = 0;
            if (isset($cart->shipTo->ui_id)) $selectedShipTo = $cart->shipTo->ui_id;
            $tmp = array();
            $i = 0;
            foreach($shipToArr as $addr){
                $tmp[$addr->ui_id] = $addr->ui_title;

                $t->assign(array(
                    'SHIPTO_ROW_NUM' => $i,
                    'SHIPTO_ROW_ID' => $addr->ui_id,
                    'SHIPTO_ROW_TITLE' => htmlspecialchars($addr->ui_title),
//                    'SHIPTO_ROW_DESC' => htmlspecialchars($method['shipm_desc']),
                    'SHIPTO_SELECTED' => $selectedShipTo,
                    'SHIPTO_ROW' => $addr,
                    'SHIPTO_ROW_ODDEVEN' => cot_build_oddeven($i),
                ));
                $t->parse('MAIN.SELECT_SHIPTO.ROW');

            }
            if(count($shipToArr) > 0){
                $t->assign(array(
                    'SHIPTO_RADIO' => cot_radiobox($selectedShipTo, 'shipto_id',
                        array_keys($tmp), array_values($tmp), '', '<br />'),
                    'SCROLL_TOP' => cot_import('st', 'G', 'INT'),   // Параметр для прокрутки экрана
                ));
            }else{
                $t->parse('MAIN.SELECT_SHIPTO.NONE');
            }
            $t->parse('MAIN.SELECT_SHIPTO');
        }
        // === Ship to adresses select

        $i=1;
        global $prow;   // Для XTemplate
		foreach( $cart->products as $pkey => $prow ) {

            $t->assign(OrderItem::generateTags($prow, 'ROW_PROD_'));
            $t->assign(array(
                'ROW_PROD_NUMBER' => $i,
                'ODDEVEN' => cot_build_oddeven($i),
                'ROW_PROD_BASE_PRICE' => $currency->createPriceDiv('basePrice','', $cart->pricesUnformatted[$pkey],false),
            ));
            $t->parse('MAIN.PRODUCTS.ROW');
            $i++;
        }
        // Скидочные купоны
        if ($cfg["shop"]['coupons_enable']){
            $t->assign(array(
                'COUPON_CODE' => $cart->cartData['couponCode'],
				'COUPON_DESCR' => $cart->cartData['couponDescr'],
                'COUPON_TAX' => $currency->createPriceDiv('couponTax','', $cart->pricesUnformatted['couponTax'],false),
                'SALES_PRICE_COUPON' => $currency->createPriceDiv('salesPriceCoupon','',
                    $cart->pricesUnformatted['salesPriceCoupon'],false),
                'COUPON_TEXT' => $cart->coupon_code ? $L['shop']['coupon_code_change'] : $L['shop']['coupon_code_enter'],
            ));
        }
        
        // Налоги
        $i = 1;
        if (is_array($cart->cartData['DBTaxRulesBill'])){
            foreach($cart->cartData['DBTaxRulesBill'] as $rule){
                /** @var Calc $rule  */
                $t->assign(array(
                    'ROW_TAX_TITLE' => htmlspecialchars($rule->calc_title),
                    'ROW_TAX_DESC' => $rule->calc_desc,
                    'ROW_TAX_AMOUNT' => $currency->createPriceDiv($rule->calc_id.'Diff','',
                        $cart->pricesUnformatted[$rule->calc_id.'Diff'],false),
                    'ODDEVEN' => cot_build_oddeven($i),
                    'ROW_TAX_KIND' => 'DBTaxRulesBill'
                ));
                $t->parse('MAIN.PRODUCTS.ROW_TAX_RULES_BILL');
                $i++;
            }
        }
        
        if (is_array($cart->cartData['taxRulesBill'])){
            foreach($cart->cartData['taxRulesBill'] as $rule){
                /** @var Calc $rule  */
                $t->assign(array(
                   'ROW_TAX_TITLE' => htmlspecialchars($rule->calc_title),
                   'ROW_TAX_DESC' => $rule->calc_desc,
                   'ROW_TAX_AMOUNT' => $currency->createPriceDiv($rule->calc_id.'Diff','',
                       $cart->pricesUnformatted[$rule->calc_id.'Diff'],false),
                   'ODDEVEN' => cot_build_oddeven($i),
                   'ROW_TAX_KIND' => 'taxRulesBill'
                ));
                $t->parse('MAIN.PRODUCTS.ROW_TAX_RULES_BILL');
                $i++;
            }
        }
        
        if (is_array($cart->cartData['DATaxRulesBill'])){
            foreach($cart->cartData['DATaxRulesBill'] as $rule){
                /** @var Calc $rule  */
                $t->assign(array(
                   'ROW_TAX_TITLE' => htmlspecialchars($rule->calc_title),
                   'ROW_TAX_DESC' => $rule->calc_desc,
                   'ROW_TAX_AMOUNT' => $currency->createPriceDiv($rule->calc_id.'Diff','',
                       $cart->pricesUnformatted[$rule->calc_id.'Diff'],false),
                   'ODDEVEN' => cot_build_oddeven($i),
                   'ROW_TAX_KIND' => 'DATaxRulesBill'
                ));
                $t->parse('MAIN.PRODUCTS.ROW_TAX_RULES_BILL');
                $i++;
            }
        }

        $t->assign(ShopCart::generateTags($cart, 'ORDER_', 'shopper', false));
        $t->assign(array(
            'ORDER_TOTAL_IN_PAYMENT_CURRENCY' => $totalInPaymentCurrency,
            'AUTO_SELECTED_SHIPMENT' => $cart->automaticSelectedShipment,
            'AUTO_SELECTED_PAYMENT'  => $cart->automaticSelectedPayment,
            'ORDER_SELECT_SHIPMENT_TEXT' => ($cart->shipm_id) ? $L['shop']['cart_change_shipping'] :
                    $L['shop']['cart_edit_shipping'],
            'ORDER_SELECT_PAYMENT_TEXT'  => ($cart->paym_id) ? $L['shop']['cart_change_payment'] :
                    $L['shop']['cart_edit_payment'],
        ));
        
        $t->parse('MAIN.PRODUCTS');

        $checkout_link_html = '';
        if (!$cfg["shop"]['use_as_catalog']) {
            $checkout_link_html = cot_rc('shop_btn_order_confirm');
        }
        

        // TODO CONTINUE_LINK из рессурсов
        $t->assign(array(
            'PAGE_TITLE'        => $L['shop']['cart_title'],
            'BREAD_CRUMBS'      => $breadcrumbs,
            'USERS_SUBTITLE'    => $L['use_subtitle'], 
            'CONTINUE_URL'      => $continue_url,
            // TODO учесть useSSL
            'CHECKOUT_FORM_ACTION' => cot_url('shop', array('m'=>'cart', 'a'=>$checkout_task)),
            'CHECKOUT_FORM_COMMENT' =>$cart->order_customer_note,
            'CHECKOUT_TOS' => $cart->vendor->vendor_terms_of_service,
            'CHECKOUT_FORM_TOS_ACCEPT' => cot_checkbox($cart->tosAccepted, 'tosAccepted', $L['shop']['cart_tos_read_and_accepted']),
            'CHECKOUT_FORM_SUBMIT' =>  $checkout_link_html,
            'CHECKOUT_TASK' => $checkout_task,

            'ST_SAME_AS_BT' => ($cart->shipTo != 0) ? cot_checkbox($cart->STsameAsBT, 'bt_as_st',
                $L['shop']['user_form_billto_as_shipto']) : '',
        ));
        
        // Авторизация для гостей
        if ($usr['id'] == 0){
            $t->assign(array(
                'USERS_AUTH_SEND' => cot_url('login', 'a=check' . (empty($redirect) ? '' : "&redirect=$redirect")),
                'USERS_AUTH_USER' => cot_inputbox('text', 'rusername', $rusername, array('size' => '16', 'maxlength' => '32')),
                'USERS_AUTH_PASSWORD' => cot_inputbox('password', 'rpassword', '', array('size' => '16', 'maxlength' => '32')),
                'USERS_AUTH_REGISTER' => cot_url('users', 'm=register'),
                'USERS_AUTH_REMEMBER' => $cfg['forcerememberme'] ? $R['form_guest_remember_forced'] : $R['form_guest_remember']
           ));
        }
        // Error and message handling
        cot_display_messages($t);
        
        $t->parse('MAIN');
        return $t->text('MAIN');
        
    }

    /**
     * Оформление заказа
     * Validate for the data that is needed to process the order
     */
    public function checkoutAction() {
        //Tests step for step for the necessary data, redirects to it, when something is lacking
        global $cfg, $L;

        $cart = ShopCart::getInstance();
        if ($cart && !$cfg["shop"]['use_as_catalog']) {
            if ($cart->checkoutData(true)) {
                //This is dangerous, we may add it as option, direclty calling the confirm is in most countries illegal and
                // can lead to confusion.
                cot_message($L['shop']['cart_checkout_done_confirm_order']);
                cot_redirect(cot_url('shop', array('m'=>'cart'), '', true));
            }
        }
    }
    
    /**
     * Подтверждение заказа 
     */
    public function confirmAction() {
        global $L, $cfg, $usr;
        
        $cart = ShopCart::getInstance();
        if (!$cart){
            cot_error($L['shop']['cart_data_not_valid']);
            cot_redirect(cot_url('shop', array('m'=>'cart'), '', true));
        }
        $orderId = $cart->createOrder();

        if ($orderId > 0){
            // Получить заказ, отправить уведомления
            $this->sendNewOrderNotify($orderId, 'vendor');

            $order = Order::getById($orderId);
            $vendorId = ($order->vendor_id > 0) ? $order->vendor_id : 1;
            $vendor = Vendor::getById($vendorId);
            $vendorMail = $vendor->vendor_email;
            $shopperMail = $order->billTo->oui_email;

            $notifyShopper = true;
            if ($vendorMail == $shopperMail) $notifyShopper = false;
            if ($usr['id'] == $vendor->vendor_ownerid && $cfg['shop']['notify_if_user_admin'] == 0) $notifyShopper = false;
            if (cot_auth('shop', 'any', 'A') && $cfg['shop']['notify_if_user_admin'] == 0) $notifyShopper = false;
            if ($notifyShopper){
                $this->sendNewOrderNotify($orderId, 'shopper');
            }

            // Потом редирект на страницу Thank You
            cot_message($L['shop']['cart_thankyou']);
            if ($usr['id'] > 0){
                cot_redirect(cot_url('shop', array('m'=>'order', 'id'=>$orderId), '', true));
            }else{
                cot_redirect(cot_url('shop', array('m'=>'order', 'order_number'=>$order->order_number,
                                                     'order_pass'=>$order->order_pass), '', true));
            }
        }
        
    }
    
    /**
     * Edit shipment Action
     */
    public function edit_shipmentAction(){
        global $L, $cfg, $out, $sys, $usr, $cot_plugins, $b;

        require_once(cot_incfile('forms'));
        
        $crumbs = array(
            array($cfg["shop"]['mainPageUrl'], $cfg["shop"]['mainPageTitle']),
            array(cot_url('shop', array('m'=>'cart')), $L['shop']['cart_overview']),
            $L['shop']['cart_selectshipment']
        );
        $breadcrumbs = cot_breadcrumbs($crumbs, $cfg['homebreadcrumb'], true);
        $sys['sublocation'] = $L['shop']['cart_selectshipment'];

        $out['subtitle'] = $L['shop']['cart_selectshipment'];
        $out['canonical_uri'] = cot_url('shop', array('m'=>'cart', 'a'=>'edit_shipment'));
        
        $cart = ShopCart::getInstance(false);
        $found_shipment_method = false;
        //$shipments_shipment_rates = array();
        
        //global $shipmentMethods; // для XTemplate;
        $t = new XTemplate(cot_tplfile('shop.select_shipment'));
        
		if (!$this->checkShipmentMethodsConfigured()) {

		}else{
            $selectedShipment = (empty($cart->shipm_id) ? 0 : $cart->shipm_id);
            $shipmentMethods = ShipmentMethod::getListByUserId($usr['id'], $cart->vendor_id);

            $currency = CurrencyDisplay::getInstance();

            /* === Hook === */
            // Все плагины доставки просматривают массив $shipmentMethods и исключают из него те методы, которые не 
            // подходят для данного заказа (устанавливают $shipmentValid в false)
            foreach ($shipmentMethods as $key => $method){
                if (!$method || !cot_plugin_active($method->pl_code) || !cot_auth('plug', $method->pl_code, 'R')){
                    unset($shipmentMethods[$key]);
                }
                $shipmentValid = true;
                $shipmentMethods[$key]->totalPrice = 0;
                if (!empty($cot_plugins['shop.shipment.list'])){
                    foreach($cot_plugins['shop.shipment.list'] as $k){
                        if ($k['pl_code'] == $method->pl_code){
                            include $cfg['plugins_dir'] . '/' . $k['pl_file'];
                        }
                    }
                }
                if (cot_error_found() || !$shipmentValid) {
                    unset($shipmentMethods[$key]);
                }
            }
            /* === /Hook === */
            reset($shipmentMethods);

            $methods = array();
            $i = 0;
            $t->assign(array(
                'SHIPMENT_SELECTED' => $selectedShipment
            ));

            foreach($shipmentMethods as $key => $method){
                $method = $method->toArray();
                $method['salesPrice'] = $currency->priceDisplay($method['totalPrice']);

                $methods[$method['shipm_id']] = $method['shipm_title']." ({$method['shipm_desc']})";
                if ($method['totalPrice'] > 0){
                    $methods[$method['shipm_id']] .= "<strong>{$method['salesPrice']}</strong>";
                }

//                $costDisplay = '<span class="' . $this->_type . '_cost"> (' . JText::_('COM_VIRTUEMART_PLUGIN_COST_DISPLAY') . $costDisplay . ")</span>";

                $t->assign(array(
                    'METHOD_ROW_NUM' => $i,
                    'METHOD_ROW_ID' => $method['shipm_id'],
                    'METHOD_ROW_TITLE' => htmlspecialchars($method['shipm_title']),
                    'METHOD_ROW_DESC' => htmlspecialchars($method['shipm_desc']),
                    'METHOD_ROW' => $method,
                    'METHOD_ROW_SALES_PRICE' => $method['salesPrice'],
                    'METHOD_ROW_ODDEVEN' => cot_build_oddeven($i),
                ));
                $t->parse('MAIN.SELECT_SHIPMENT.ROW');
            }
            $t->assign(array(
                'SHIPMENT_METHOD_RADIO' => cot_radiobox($selectedShipment, 'shipmentmethod_id', 
                        array_keys($methods), array_values($methods), '', '<br />')
            ));
            $t->parse('MAIN.SELECT_SHIPMENT');
            
            // if no shipment rate defined
            $found_shipment_method = false;
            if( count($shipmentMethods) > 0) {
                $found_shipment_method = true;
            }
        }

        // Error and message handling
        cot_display_messages($t);
        $t->assign(array(
            'FOUND_SHIPMENT_METHOD' => $found_shipment_method,
            'PAGE_TITLE'        => $L['shop']['cart_selectshipment'],
            'BREAD_CRUMBS'      => $breadcrumbs,
        ));
        
        $t->parse('MAIN');

        return $t->text('MAIN');
    }
    
    
    /**
     * Edit payment Action
     */
    public function edit_paymentAction(){
        global $L, $cfg, $out, $sys, $cot_plugins, $usr;
        
        require_once(cot_incfile('forms'));
        
        $crumbs = array(
            array($cfg["shop"]['mainPageUrl'], $cfg["shop"]['mainPageTitle']),
            array(cot_url('shop', array('m'=>'cart')), $L['shop']['cart_overview']),
            $L['shop']['cart_selectpayment']
        );
        $breadcrumbs = cot_breadcrumbs($crumbs, $cfg['homebreadcrumb'], true);
        $sys['sublocation'] = $L['shop']['cart_selectpayment'];

        $out['subtitle'] = $L['shop']['cart_selectpayment'];
        $out['canonical_uri'] = cot_url('shop', array('m'=>'cart', 'a'=>'edit_payment'));
        
        $cart = ShopCart::getInstance(false);
        $found_payment_method = false;
        //global $shipmentMethods; // для XTemplate;
        $t = new XTemplate(cot_tplfile('shop.select_payment'));
        
		if (!$this->checkPaymentMethodsConfigured()) {

		}else{
            $selected = (empty($cart->paym_id) ? 0 : $cart->paym_id);
            $paymentMethods = PaymentMethod::getListByUserId($usr['id'], $cart->vendor_id);
            
            $currency = CurrencyDisplay::getInstance();
            
            /* === Hook === */
            // Все плагины оплаты просматривают массив $paymentMethods и исключают из него те методы, которые не 
            // подходят для данного заказа
            // Также они заполняют цены за использование своего в соотвествии со своими правилами см 
            //    vmpsplugin.php стр. 130
            // plgVmDisplayListFEPayment
            foreach ($paymentMethods as $key => $method){
                if (!$method || !cot_plugin_active($method->pl_code) || !cot_auth('plug', $method->pl_code, 'R')){
                    unset($paymentMethods[$key]);
                }
                $paymentValid = true;
                $paymentMethods[$key]->totalPrice = 0;
                if (!empty($cot_plugins['shop.payment.list'])){
                    foreach($cot_plugins['shop.payment.list'] as $k){
                        if ($k['pl_code'] == $method->pl_code){
                            include $cfg['plugins_dir'] . '/' . $k['pl_file'];
                        }
                    }
                }
                if (cot_error_found() || !$paymentValid) {
                    unset($paymentMethods[$key]);
                }
            }
            /* === /Hook === */
            reset($paymentMethods);
            $methods = array();
            $i = 0;
            $t->assign(array(
                'PAYMENT_SELECTED' => $selected
            ));
            foreach($paymentMethods as $key => $method){
                $method = $method->toArray();
                $method['salesPrice'] = $currency->priceDisplay($method['totalPrice']);
                
                $methods[$method['paym_id']] = $method['paym_title']." ({$method['paym_desc']})";
                if ($method['totalPrice'] > 0){
                    $methods[$method['paym_id']] .= "<strong>{$method['salesPrice']}</strong>";
                }
                // TODO учесть возможность вывода кастомного заголовка метода плагином оплаты
                $t->assign(array(
                    'METHOD_ROW_NUM' => $i,
                    'METHOD_ROW_ID' => $method['paym_id'],
                    'METHOD_ROW_TITLE' => htmlspecialchars($method['paym_title']),
                    'METHOD_ROW_DESC' => htmlspecialchars($method['paym_desc']),
                    'METHOD_ROW_SALES_PRICE' => $method['salesPrice'],
                    'METHOD_ROW' => $method,
                    'METHOD_ROW_ODDEVEN' => cot_build_oddeven($i),
                ));
                $t->parse('MAIN.SELECT_PAYMENT.ROW');
            }
            $t->assign(array(
                'PAYMENT_METHOD_RADIO' => cot_radiobox($selected, 'paymentmethod_id',
                        array_keys($methods), array_values($methods), '', '<br />')
            ));
            $t->parse('MAIN.SELECT_PAYMENT');
            
            // if no payment rate defined
            $found_payment_method = false;
            if( count($paymentMethods) > 0) {
                $found_payment_method = true;
            }
        }

        // Error and message handling
        cot_display_messages($t);
        $t->assign(array(
            'FOUND_PAYMENT_METHOD' => $found_payment_method,
            'PAGE_TITLE'        => $L['shop']['cart_selectpayment'],
            'BREAD_CRUMBS'      => $breadcrumbs,
        ));
        $t->parse('MAIN');

        return $t->text('MAIN');
    }
    
    /**
     * Action:
     * Sets a selected shipment to the cart
     */
    public function setshipmentAction() {
        global $L;
        
        $shipmentmethod_id = cot_import('shipmentmethod_id', 'P', 'INT');

        if (!$shipmentmethod_id){
            cot_message($L['shop']['cart_no_shipment_selected'], 'warning');
            cot_redirect(cot_url('shop', array('m'=>'cart', 'a'=>'edit_shipment'), '', true));
        }

        //Now set the shipment ID into the cart
	    $cart = ShopCart::getInstance();
	    if ($cart) {
            $cart->shipm_id = $shipmentmethod_id;
            //Add a hook here for other payment/shipment plugins, checking the data of the choosed method
            /* === Hook === */
            foreach (cot_getextplugins('shop.shipment.set') as $pl){
                include $pl;
            }
            if (!cot_error_found()){
                // It's OK; nothing else to do
                $cart->save();
            }else{
                cot_redirect(cot_url('shop', array('m'=>'cart', 'a'=>'edit_shipment'), '', true));
            }
            if ($cart->getInCheckOut()) {
                cot_redirect(cot_url('shop', array('m'=>'cart', 'a'=>'checkout'), '', true));
            }else{
                $urlArr = array('m'=>'cart');
                $st = cot_import('scroll', 'P', 'INT');
                if ($st > 0) $urlArr['st'] = $st;
                cot_redirect(cot_url('shop', $urlArr, '', true));
            }
        }
        
    }
    
    /**
     * Action:
     * Sets a selected shipment to the cart
     */
    public function setpaymentAction() {
        global $L;

        $paymentmethod_id = cot_import('paymentmethod_id', 'P', 'INT');
        if (!$paymentmethod_id){
            cot_message($L['shop']['cart_no_payment_selected'], 'warning');
            cot_redirect(cot_url('shop', array('m'=>'cart', 'a'=>'edit_payment'), '', true));
        }
        //Now set the shipment ID into the cart
	    $cart = ShopCart::getInstance();
	    if ($cart) {
            $cart->paym_id = $paymentmethod_id;
            //Add a hook here for other payment/shipment plugins, checking the data of the choosed method
            /* === Hook === */
            foreach (cot_getextplugins('shop.payment.set') as $pl){
                include $pl;
            }
            if (!cot_error_found()){
                // It's OK; nothing else to do
                $cart->save();
            }else{
                cot_redirect(cot_url('shop', array('m'=>'cart', 'a'=>'edit_payment'), '', true));
            }
            if ($cart->getInCheckOut()) {
                cot_redirect(cot_url('shop', array('m'=>'cart', 'a'=>'checkout'), '', true));
            }else{
                $urlArr = array('m'=>'cart');
                $st = cot_import('scroll', 'P', 'INT');
                if ($st > 0) $urlArr['st'] = $st;
                cot_redirect(cot_url('shop', $urlArr, '', true));
            }
        }
        
    }

    /**
     * Сохранить выбранный адрес доставки в корзине
     */
    public function setshiptoAction(){
        global $usr;

        $ui_id = cot_import('shipto_id', 'P', 'INT');
        if (!$ui_id) return false;

        $uData = UserInfo::find(array("ui_id={$ui_id}", "user_id={$usr['id']}"));
        if (!$uData || count($uData) == 0) return false;

        // если мы в корзине - сохранить в корзину
        $cart = ShopCart::getInstance();
        $cart->saveAddress($uData[0], 'ST');

        $urlArr = array('m'=>'cart');
        $st = cot_import('scroll', 'P', 'INT');
        if ($st > 0) $urlArr['st'] = $st;
        cot_redirect(cot_url('shop', $urlArr, '', true));
    }

    /**
     * Action: Добавление товара Ajax 
     * @todo права пользователя на добавления товара в корзину/просмотр цен. Например 2 и 3
     * @todo вывод сообщений через рессурсы
     */
    public function ajxAddAction(){
        global $L, $cfg;

        $json = null;
        $cart = ShopCart::getInstance(false);
        // Для отладки можно очистить корзину
//        $cart->removeCartFromSession();
        $shop_categoryArr = shop_readShopCats();
        if ($cart) {
            // Get a continue link
            //$continue_link = $this->getContinueLink();
            $continue_link = '';    // Просто закрыть корзину

            $cart_link = cot_url('shop', 'm=cart', '', true);

            $product_ids = cot_import('shop_product_id', 'P', 'ARR');
            $quantities = cot_import('quantity', 'P', 'ARR');


            $errorMsg = array( 0 => $L['shop']['cart_product_added']);
            if (empty($product_ids)) {
                $errorMsg = $L['shop']['cart_error_no_product_ids'];
                $json->msg = '<div>'.$errorMsg.'</div>';
                $json->msg .= '<a class="confirmButton jqmClose" href="' . $continue_link . '" >' . $L['shop']['continue_shopping'] . '</a>';

                $json->stat = '2';
                echo json_encode($json);
                exit();
            }
            $success = false;
            foreach ($product_ids as $p_key => $product_id) {
                $product_id = (int)$product_id;
                if(empty($quantities[$p_key])) $quantities[$p_key] = 1;

                if($cart->add($product_id, $quantities[$p_key], $errorMsg[$p_key] )) {
                    $success = true;
                }
            }
            if($success){
                $json->msg = '';
                if ($errorMsg) $json->msg .= '<div>'.implode('<br />', $errorMsg).'</div>';
                $json->msg .= '<a class="confirmButton jqmClose" href="' . $continue_link . '" >' . $L['shop']['continue_shopping'] . '</a>';
                $json->msg .= '&nbsp;&nbsp;&nbsp;';
                $json->msg .= '<a class="confirmButton" href="' . $cart_link . '">' . $L['shop']['cart_show'] . '</a>';
                $json->stat = '1';
                $json->act = $cfg["shop"]['addtocart_act'];
                $json->cartlink = $cart_link;
            }else{
                $json->msg = '<div>'.implode('<br />', $errorMsg).'</div>';
                $json->msg .= '<a class="confirmButton jqmClose" href="' . $continue_link . '" >' . $L['shop']['continue_shopping'] . '</a>';

                $json->stat = '2';
            }

        } else {
            $json->msg  = '<div>'.$L['shop']['minicart_error_js'].'</div>';
            $json->msg .= '<a href="'.cot_url('page', 'c='.$shop_categoryArr[0]).'" >' . $L['shop']['continue_shopping'] . '</a>';
            $json->stat = '0';
        }
        echo json_encode($json);
        exit();
    }
    
    
    
    
    /**
     * Обновление корзины Ajax 
     */
    public function viewJSAction(){
        global $L;
        $cart = ShopCart::getInstance(true);
        
        $data = $cart->prepareAjaxData();

        if ($data->totalProduct > 1){
            $data->totalProductTxt = sprintf($L['shop']['cart_x_products'], $data->totalProduct);
        }else if ($data->totalProduct == 1){
            $data->totalProductTxt = $L['shop']['cart_one_product'];
        }else{
            $data->totalProductTxt = $L['shop']['cart_empty_cart'];
        }

        // Даже если данные проверены, все равно с миникорзины уходим в большую
        $taskRoute = '';
        $linkName = $L['shop']['cart_show'];

        $data->cart_show = cot_rc('shop_minicart_showcart', array(
                'url' => cot_url('shop', 'm=cart'.$taskRoute),
                'text' => $linkName
            ));
        $data->cart_showUrl = cot_url('shop', 'm=cart'.$taskRoute);
        $data->cart_showTitle = $linkName;
        $data->billTotal = $L['shop']['cart_total'].': <strong>' . $data->billTotal . '</strong>';

        echo json_encode($data);
        exit();
    }
    
    /**
     * Action. Update product quantity in the cart
     * @access public
     */
    public function updateAction() {
        global $L, $cfg;
        $cart = ShopCart::getInstance(true, false);

        $product_id = cot_import('product_id', 'P', 'INT');

        if(!isset($cart->products[$product_id])) return false;


        $product = Product::getById($product_id);
        if(!$product) return false;

        $allowDec = $product->allow_decimal_quantity;

        if($allowDec){
            $quantity = cot_import('quantity', 'P', 'TXT');
            $quantity = trim(str_replace(',','.', $quantity));
            $quantity = (float)$quantity;
        }else{
            $quantity = cot_import('quantity', 'P', 'INT');
        }

        if ($product_id > 0 && $quantity >=0 && $cart->updateProduct($product_id, $quantity)){
            $msg = $cart->getError();
            if (empty($msg)) $msg = $L['shop']['product_quantity_success'];
            cot_message($msg);
        }else{
            cot_error($L['shop']['product_quantity_error'].'. '.$cart->getError());
        }
        cot_redirect(cot_url('shop', 'm=cart', '', true));
    }
    
    /**
     * Action. Delete product from cart
     * @access public
     */
    public function deleteAction() {
        global $L;
        $cart = ShopCart::getInstance(true, false);
		
        $product_id = cot_import('product_id', 'G', 'INT');
        
        if ($product_id > 0 && $cart->deleteProduct($product_id)){
            cot_message($L['shop']['product_removed']);
        }else{
            cot_error($L['shop']['product_remove_error']);
        }
        cot_redirect(cot_url('shop', 'm=cart', '', true));
    }

    /**
     * Сохранить код купона
     * @return bool
     */
    public function setcouponAction(){
        global $L, $cfg;

        $cart = ShopCart::getInstance();
        $code = cot_import('coupon_code', 'P', 'TXT');
        if (!$code) return false;
        $coupon = Coupon::getByCode($code);
        if (!$coupon){
            cot_error($L['shop']['coupon_notfound']);
            cot_redirect(cot_url('shop', 'm=cart', '', true));
        }else{
            $prices = $cart->getCartPrices();
            if ($coupon->isValid($prices['salesPrice'])){
                $cart->coupon_code = $code;
                $cart->save();
                if($cart->getInCheckOut()){
                    cot_redirect(cot_url('shop', 'm=cart&a=checkout', '', true));
                }
            }
        }
        cot_redirect(cot_url('shop', 'm=cart', '', true));
    }

    // === Cлужебные методы ===

    /**
     * Send New Order Notification
     * @param Order|int $order
     * @param string $recipient 'admin', 'vendor', 'shopper'
     * @internal param array $calcRules правила расчета
     * @return boolean
     */
    protected function sendNewOrderNotify($order, $recipient = 'shopper'){
        global $L, $cfg;

        if(!($order instanceof Order)){
            $order = (int)$order;
            if (!$order) return false;
            $order = Order::getById($order);
        }
        if (!$order) return false;

        global $vendor;  // для  XTemplate
        $vendorId = ($order->vendor_id > 0) ? $order->vendor_id : 1;
        $vendor = Vendor::getById($vendorId);

        if ($recipient == 'shopper'){
            $curr = CurrencyDisplay::getInstance(0, $order->vendor_id);
        }else{
            // $order->order_currency и есть валюта продавца
            $curr = CurrencyDisplay::getInstance($vendor->curr_id, $order->vendor_id);
        }

        global $priceTpl;
        $priceTpl = cot_tplfile('shop.mail.prodlist');

        $tpl = cot_tplfile('shop.mail.new_order.'.$recipient);
        if (!$tpl) $tpl = cot_tplfile('shop.mail.new_order.shopper');
        $t = new XTemplate($tpl);
        $i=1;

        global $prow;   // Для XTemplate
        foreach( $order->products as $pkey => $prow ) {

            $t->assign(OrderItem::generateTags($prow, 'ROW_PROD_', $recipient));
            $t->assign(array(
                'ROW_PROD_NUMBER' => $i,
                'ODDEVEN' => cot_build_oddeven($i),
            ));
            $t->parse('MAIN.ROW');
            $i++;
        }
        if(is_array($order->calc_rules)){
            foreach($order->calc_rules as $rule){
                $t->assign(array(
                     'ODDEVEN' => cot_build_oddeven($i),
                     'ROW_TAX_TITLE'  => htmlspecialchars($rule['calc_title']),
                     'ROW_TAX_AMOUNT' => $curr->priceDisplay($rule['calc_value']),
                     'ROW_TAX_KIND'   => $rule['calc_kind'],
                ));
                $t->parse('MAIN.ROW_TAX_RULES_BILL');
                $i++;
            }
        }

        $orderShopperLink = cot_url('shop', array('m'=>'order', 'order_number'=>$order->order_number,
            'order_pass'=>$order->order_pass));
        if (!cot_url_check($orderShopperLink)) $orderShopperLink = $cfg['mainurl'].'/'.$orderShopperLink;
        $orderVendorLink = cot_url('admin', array('m'=>'shop', 'n'=>'order', 'a'=>'edit', 'id'=>$order->order_id));
        if (!cot_url_check($orderVendorLink)) $orderVendorLink = $cfg['mainurl'].'/'.$orderVendorLink;

        $orderTotal = $curr->priceDisplay($order->order_total);
        $t->assign(Order::generateTags($order, 'ORDER_', $recipient));
        $t->assign(array(
            'ORDER_TOTAL' => $orderTotal,
            'ORDER_SHOPPER_LINK' => $orderShopperLink,
            'ORDER_VENDOR_LINK' => $orderVendorLink,
        ));
        $t->parse('MAIN');
        $msgBody = $t->text('MAIN');

        $subject = '';
        if ($recipient == 'shopper'){
            $subject = sprintf($L['shop']['mail_shopper_new_order_confirmed'], $vendor->vendor_title, $orderTotal,
                    $order->order_number);
            $to = $order->billTo->oui_email;
        }else{
            $shopperName = "{$order->billTo->oui_lastname} {$order->billTo->oui_firstname} {$order->billTo->oui_middlename}";
            $subject = sprintf($L['shop']['mail_vendor_new_order_confirmed'], $shopperName, $orderTotal,
                    $order->order_number);
            $to = $vendor->vendor_email;

        }

        cot_mail($to, $subject, $msgBody, '', false, null, true);
    }
    
    /**
     * Генерирует ссылку "Продолжить покупки"
     * prepareContinueLink
     * @todo если на странице товара и Ajax, то линк на эту страницу, а не на категорию
     */
    protected function getContinueLink() {

		// Get a continue link */
		$cat = shop_getLastVisitedCategory();
		if (!$cat) {
			$cat = shop_readShopCats();
            $cat = $cat[0];
		}
		$continue_link = cot_url('page', array('c'=>$cat));

        return $continue_link;
	}
    
    /**
     * Сконфигурированы методы отплаты?
     * @todo привязка методов оплаты к разным продавцам
     * @todo перенести в модель оплаты 
     * @return boolean 
     */
    protected function checkPaymentMethodsConfigured() {
        global $L, $db, $db_shop_paymethods;
        
        // Проверяем наличие плагинов оплаты
        // Годится любой хук впринципе. Нам нужно проверить установленные
        // плагины оплаты
        $payments = cot_getextplugins('shop.payment.calc_price');
        if (empty($payments)){
            $text = '';
			if (cot_auth('shop', 'any', 'A')) {
                cot_message ($L['shop']['no_payment_plugins_installed'], 'warning');
			}else{
                cot_message($L['shop']['no_payment_methods_configured'], 'warning');
            }
            return false;
        }
        
        // Проверяем наличие настроенных способов доставки
        $query = "SELECT COUNT(*) FROM $db_shop_paymethods WHERE paym_published=1";
        $totalitems = $db->query($query)->fetchColumn();
		if ($totalitems == 0) {
			$text = '';
			if (cot_auth('shop', 'any', 'A')) {
				$link = cot_url('admin','m=shop&n=paymentmethod');
				$text = sprintf($L['shop']['no_payment_methods_configured_link'], '<a href="' . $link . '">' . $link . '</a>');
			}
            cot_message(sprintf($L['shop']['no_payment_methods_configured'], $text), 'warning');

			return false;
		}
		return true;
	}
    
    /**
     * Сконфигурированы ли методы доставки
     * @todo привязка методов доставки к разным продавцам
     * @todo перенести в модель доставки 
     * @return boolean 
     */
	protected function checkShipmentMethodsConfigured() {
        global $L, $db, $db_shop_shipmethods;
        
        // Проверяем наличие плагинов доставки
        // Годится любой хук впринципе. Нам нужно проверить установленные
        // плагины доставки
        $shipments = cot_getextplugins('shop.shipment.calc_price');
        
        if (empty($shipments)){
            $text = '';
			if (cot_auth('shop', 'any', 'A')) {
                cot_message ($L['shop']['no_shipment_plugins_installed'], 'warning');
			}else{
                cot_message($L['shop']['no_shipping_methods_configured'], 'warning');
            }
            return false;
        }
       
        // Проверяем наличие настроенных способов доставки
        $query = "SELECT COUNT(*) FROM $db_shop_shipmethods WHERE shipm_published=1";
        $totalitems = $db->query($query)->fetchColumn();
		if ($totalitems == 0) {
			$text = '';
			if (cot_auth('shop', 'any', 'A')) {
				$link = cot_url('admin','m=shop&n=shipmentmethod');
				$text = sprintf($L['shop']['no_shipping_methods_configured_link'], '<a href="' . $link . '">' . $link . '</a>');

			}
            cot_message(sprintf($L['shop']['no_shipping_methods_configured'], $text), 'warning');

			return false;
		}
		return true;
	}

    /**
     * Итого в валюте покупателя
     * @param ShopCart $cart
     * @return float|null
     */
    protected function getTotalInPaymentCurrency(&$cart) {

		if (empty($cart->paymentmethod_id)) {
			return null;
		}
		if (!$cart->paymentCurrency or ($cart->paymentCurrency == $cart->pricesCurrency)) {
			return null;
		}
		$paymentCurrency = CurrencyDisplay::getInstance($cart->paymentCurrency);
		$totalInPaymentCurrency = $paymentCurrency->priceDisplay( $cart->pricesUnformatted['billTotal'], $cart->paymentCurrency) ;

		return $totalInPaymentCurrency;
	}
    
}