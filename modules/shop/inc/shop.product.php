<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Controller class for the Product
 * 
 * @package shop
 * @subpackage product
 */
class ProductController{
    
    /**
     * Action. Ajax. prices recalculation
     * @return string json encoded
     */
    public function recalculateAction(){
        global $cfg, $usr;

        $product_id = cot_import('shop_product_id', 'P', 'ARR');
        $product_id = (int)$product_id[0];
        $inVendorCurr = cot_import('vendor', 'P', 'BOL');

        $quantity = cot_import('quantity', 'P', 'ARR');
        $quantity = (float)$quantity[0];
        if ($quantity < 0 || !$quantity) $quantity = 1;
        $nowQuantity = $quantity;   // Кол-во которое выбрано непосредственно сейчас

        // Проверить кол-во товара в корзине
        // Если товар уже есть, то показываем цену, уоторая получится при добавлении заданного числа товаров к уже имеющимся
        $cart = ShopCart::getInstance(true, false);

        if (isset($cart->products[$product_id]) && $cart->products[$product_id]->prod_quantity > 0 ){
            $quantity += $cart->products[$product_id]->prod_quantity;
        }

        $product = Product::getById($product_id);

        $prices = $product->getPrices($quantity);

        $priceFormated = array();

        $vendorId = 0;
        $currId = 0;
        if($inVendorCurr){
            // продавца брать из товара ??
            $vendorId = Vendor::getLoggedVendorId();
            $currId = Vendor::getCurrencyId($vendorId);
        }
		$currency = CurrencyDisplay::getInstance($currId, $vendorId);
        $shopRootCat = shop_readShopCats();
        $shopRootCat = $shopRootCat[0];
		foreach ( $product->prices as $name => $product_price  ){
            $showBasePrice = cot_auth('page', $shopRootCat, 'A'); // todo add config settings
            if (!$showBasePrice && in_array($name, array('basePrice', 'basePriceVariant')) ){
                continue;
            }
            if (!in_array($name, array('costPrice', 'costPriceShopCurrency')) ){
			    $priceFormated[$name] = $currency->createPriceDiv($name,'', $prices, true);
            }
		}

        // Всего для данного кол-ва товаров
        $priceFormated['total'] = $prices['salesPrice'] * $nowQuantity;
        //var_dump($priceFormated['total']);
        $priceFormated['total'] = $currency->priceDisplay($priceFormated['total']);

        return json_encode ($priceFormated);
    }

    public function autocompleteAction(){
        global $cfg;

        $param = cot_import('param', 'G', 'ALP');
        if (!$param) $param = 'title';
        $q = cot_import('q', 'G', 'TXT');
        if (!$q) return '';

        $q = urldecode($q);

        $cats = shop_readShopCats();

        switch ($param){
            default:
                $cond = array(
                    array('page_title', "*{$q}*"),
                    array('page_'.$cfg['shop']['pextf_sku'], "*{$q}*", '=', 'OR'),
                    array('page_cat', $cats)
                );
                if ( is_numeric($q)){
                    $cond[] = array('page_id', (int)$q, '=', 'OR' );
                }
        }

        $products = Product::find($cond, false, 20);
        if (!$products) return '';

        $ret = array();

        foreach($products as $row){
            $str = "{$row->page_title}|{$row->prod_id}|";

            if (!empty($row->sku)){
                $str .= "{$row->sku}";
            }
            $ret[] = $str;
        }

        return implode("\n", $ret);
    }

    /**
     * Получить товар для Автокомплита
     */
    public function getProductACAction(){
        $id = cot_import('rprod_id', 'P', 'INT');

        if (!$id) return false;
        $prod = Product::getById($id);

        if (!$prod) return false;

//        $tmp = ShopProduct::generateTags($prod);
        $tmp = cot_generate_pagetags($prod->toArray(), 'PROD_', 10);

        // Удалим лишнюю информацию
        unset($tmp['PROD_TEXT']);
        unset($tmp['PROD_TEXT_CUT']);
        unset($tmp['PROD_TEXT_IS_CUT']);
        unset($tmp['PROD_DESC_OR_TEXT']);
        unset($tmp['PROD_DESC_OR_TEXT']);
        unset($tmp['PROD_PROD_ADD_TO_CART']);
        $ret = array('error' => '', 'message'=>'');
        $ret['product'] = $tmp;

        echo json_encode($ret);

    }

    /**
     * Add Waiting User
     */
    public function ajx_notify_meAction(){
        global $usr, $L;

        $ret = array('error' => '', message => '');

        $item = array();
        $item['product_id'] = cot_import('rprod_id', 'P', 'INT');
        $item['wu_notify_name'] = cot_import('rname', 'P', 'TXT');
        $item['wu_notify_email'] = cot_import('remail', 'P', 'TXT');
        $item['wu_notify_phone'] = cot_import('rphone', 'P', 'TXT');
        $item['user_id'] = $usr['id'];
        $item['wu_notified'] = 0;
        $item['wu_notify_date'] = '1970-01-01';
        if(!cot_check_email($item['wu_notify_email'])){
            $ret['error'] = $L['aut_emailtooshort'];
            return json_encode($ret);
        }

        //$this->_productAdapter->saveWaitingUser($item);
        $product = ShopProduct::getById($item['product_id']);
        if (!$product){
            $ret['error'] = $L['shop']['product_not_found'];
            return json_encode($ret);
        }
        ShopProduct::saveWaitingUser($item);

        $ret['message'] = $L['shop']['request_accepted'];
        return json_encode($ret);
    }

    /**
     * Action. Ajax редактирование цен
     * @todo cache
     * @todo проверка прав на сохранение
     */
    public function edit_priceAction(){
        global $usr, $L, $cot_groups, $cfg, $b;
        
        define('COT_ADMIN', TRUE);
        define('COT_CORE', TRUE);
        //cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop.admin.product.js');
        
        $prodId = cot_import('prod_id', 'G', 'INT');
        
        if (!$prodId || $prodId < 0) cot_die_message(404);
        list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = cot_auth('page', 'any');
        cot_block($usr['auth_read']);

        $calculator = calculationHelper::getInstance();

        if($b == 'save'){
            $rpage = array();
            $rpage['page_id'] = $prodId;
            // todo проверка прав на сохранение
            // Обработка цены
            $rpage['price']['price'] = cot_import('rprod_price', 'P', 'TXT');
            $rpage['price']['curr_id'] = cot_import('rprod_price_currency', 'P', 'INT');
            $rpage['price']['tax_id'] = cot_import('rprod_price_tax_id', 'P', 'INT');
            $rpage['price']['discount_id'] = cot_import('rprod_price_discount_id', 'P', 'INT');
            $rpage['price']['override'] = cot_import('rprice_override', 'P', 'INT', 2);
            $rpage['price']['override_price'] = cot_import('rprice_override_price', 'P', 'TXT');
            // Дополнительные цены
            $rpage['_addprice'] = cot_import('rprod_addprice', 'P', 'ARR');
            $rpage['_addprice_groups'] = cot_import('rprod_addprice_groups', 'P', 'ARR');
            $rpage['_addprice_min_quantity'] = cot_import('rprod_addprice_min_quantity', 'P', 'ARR');
            $rpage['_addprice_max_quantity'] = cot_import('rprod_addprice_max_quantity', 'P', 'ARR');

            $rpage['_price_sales'] = cot_import('rprod_salesPrice', 'P', 'TXT');
            $useDesPrice = cot_import('rprod_use_desired_price', 'P', 'BOL');

            // Use desired price
            if($useDesPrice){
                $rpage['price']['price'] = $calculator->calculateCostprice($rpage);
            }

            Product::saveInfoByPag($rpage);
            
            cot_message($L['shop']['saved']);
            cot_redirect("index.php?e=shop&m=product&a=edit_price&prod_id={$prodId}&_ajax'=>1");
        }
        
        $product = Product::getById($prodId);
        if (!$product) cot_die_message(404);
        
        list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = cot_auth('page', $product->page_cat);
        cot_block($usr['isadmin'] || $usr['auth_write'] && $usr['id'] == $product->page_ownerid);
        
        $vendor = Vendor::getById($product->vendor_id);
        if(!$vendor){
            $vendor = Vendor::getById(Vendor::getLoggedVendorId());
        }

        if(empty($product->price['curr_id'])){
            $product->price['curr_id'] = $vendor->curr_id;
        }

        $currencies = Currency::getKeyValPairsList();
        $currency = Currency::getById($product->price['curr_id']);
        $venCurrency = Currency::getById($vendor->curr_id);
        
        // Все группы пользователей на сайте:
        $uGroups = array();
        foreach($cot_groups as $k => $i){
                $uGroups[$k] = $cot_groups[$k]['title'];
        }

        $taxRules = array();
        foreach($calculator->rules['Tax'] as $rule){
            $taxRules[] = $rule->calc_title;
        }
        foreach($calculator->rules['VatTax'] as $rule){
            $taxRules[] = $rule->calc_title;
        }

        $DBTaxRules = array();
        foreach($calculator->rules['DBTax'] as $rule){
            $DBTaxRules[] = $rule->calc_title;
        }

        $DATaxRules = array();
        foreach($calculator->rules['DATax'] as $rule){
            $DATaxRules[] = $rule->calc_title;
        }

        // Список налогов
        $taxes = Calc::getTaxes();
        $taxRates = array(
            '-1' => $L['shop']['product_tax_none'],
            '0'  => $L['shop']['product_tax_no_special']
        );
        foreach ($taxes as $tax) {
            $taxRates[$tax->calc_id] = $tax->calc_title;
        }

        if(!isset($product->price['tax_id'])){
            $product->price['tax_id'] = 0;
        }

        // Список скидок
        $discounts = Calc::getDiscounts();
        $discountrates = array(
            '-1' => $L['shop']['product_tax_none'],
            '0'  => $L['shop']['product_tax_no_special']
        );
        foreach ($discounts as $discount) {
            $discountrates[$discount->calc_id] = $discount->calc_title;
        }

        if(!isset($product->price['discount_id'])){
            $product->price['discount_id'] = 0;
        }

        $OverrideOpts = array(
            0 => $L['Disabled'],
            1 => $L['shop']['product_form_override_final'],
            -1 => $L['shop']['product_form_override_to_tax']
        );

        $t = new XTemplate(cot_tplfile('shop.admin.product'));

        $t->assign(cot_generate_pagetags($product->toArray(), 'PAGE_FORM_'));
        $t->assign(array(
            'PAGE_FORM_PROD_PRICE' => cot_inputbox('text', 'rprod_price', $product->price['price'],
                array('size' => '24', 'maxlength' => '255')),
            'PAGE_FORM_CURRENCY' => cot_selectbox($product->price['curr_id'], 'rprod_price_currency',
                array_keys($currencies), array_values($currencies), false),
            'PAGE_FORM_PROD_BASE_PRICE' => cot_inputbox('text', 'rprod_basePrice', $product->prices["basePrice"],
               array('size' => '24', 'readonly'=>'readonly')),
            'PAGE_FORM_PROD_SALES_PRICE' => cot_inputbox('text', 'rprod_salesPrice', $product->prices["salesPriceTemp"],
               array('size' => '24')),
            'PAGE_FORM_PROD_SALES' => cot_checkbox(false, 'rprod_use_desired_price',
                $L['shop']['product_form_calc_base_price']),
            'PAGE_PROD_CURRENCY_SYMBOL' => $currency->curr_symbol,
            'PAGE_VENDOR_CURRENCY_SYMBOL' => $venCurrency->curr_symbol,
            'PAGE_FORM_PROD_TAX_RATES' => cot_selectbox($product->price['tax_id'], 'rprod_price_tax_id',
                    array_keys($taxRates), array_values($taxRates), false, array() ),
            'PAGE_PROD_TAX_RULES_ARR' => $taxRules,
            'PAGE_FORM_PROD_DISCOUNTS' => cot_selectbox($product->price['discount_id'], 'rprod_price_discount_id',
                    array_keys($discountrates), array_values($discountrates), false, array() ),
            'PAGE_PROD_DBTAX_RULES_ARR' => $DBTaxRules,
            'PAGE_PROD_DATAX_RULES_ARR' => $DATaxRules,
            'PAGE_FORM_PROD_PRICE_OVERRIDE_PRICE' => cot_inputbox('text', 'rprice_override_price',
                $product->price['override_price'], array('size' => '24')),
            'PAGE_FORM_PROD_PRICE_OVERRIDE' => cot_radiobox($product->price['override'], 'rprice_override', array_keys($OverrideOpts),
                array_values($OverrideOpts), '', '<br />'),
        ));
        // Дополнительные цены
        $pagAddP = $product->add_prices;
        // Нулевой пустой элемент будем клонировать
        $pagAddP[0] = array('price_id' => 0);
        foreach($pagAddP as $pkey => $pval){
            $pid = ($pval['price_id'] > 0) ? 'id'.$pval['price_id'] : '';
            $t->assign(array(
                'PAGE_FORM_PROD_ADDP_ROW_ID' => $pval['price_id'],
                'PAGE_FORM_PROD_ADDP_ROW' => cot_inputbox('text', "rprod_addprice[$pid]", $pval['price_price'],
                    array('size' => '10', 'maxlength' => '255')),
                'PAGE_FORM_PROD_ADDP_ROW_MIN' => cot_inputbox('text', "rprod_addprice_min_quantity[$pid]", 
                        $pval['price_quantity_start'], array('size' => '10', 'maxlength' => '255')),
                'PAGE_FORM_PROD_ADDP_ROW_MAX' => cot_inputbox('text', "rprod_addprice_max_quantity[$pid]", 
                        $pval['price_quantity_end'], array('size' => '10', 'maxlength' => '255')),
                'PAGE_FORM_PROD_ADDP_ROW_GROUPS' => cot_selectbox($pval['price_groups'], 
                        "rprod_addprice_groups[$pid][]", array_keys($uGroups), array_values($uGroups), 
                        true, array('multiple'=>'multiple', 'style'=>'vertical-align:middle')),
            ));
            $t->parse('EDIT_PRICE.ADD_PRICES_ROW');
        }
        
        // Error and message handling
        cot_display_messages($t, 'EDIT_PRICE');
        
        $t->parse('EDIT_PRICE');
        return $t->text('EDIT_PRICE');

    }
    
}