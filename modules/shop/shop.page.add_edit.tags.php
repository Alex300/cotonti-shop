<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.edit.tags,page.add.tags
Tags=page.edit.tpl,page.add.tpl:{PAGE_FORM_PROD_PRICE},{PAGE_FORM_PROD_BASE_PRICE},{PAGE_FORM_PROD_SALES_PRICE},{PAGE_FORM_CURRENCY},{PAGE_PROD_CURRENCY_SYMBOL},{PAGE_VENDOR_CURRENCY_SYMBOL}, {PAGE_FORM_PROD_TAX_RATES}, {PAGE_PROD_TAX_RULES_ARR}, {PAGE_FORM_PROD_DISCOUNTS}, {PAGE_PROD_DBTAX_RULES_ARR}, {PAGE_PROD_DATAX_RULES_ARR}, {PAGE_FORM_PROD_PRICE_OVERRIDE_PRICE}, {PAGE_FORM_PROD_PRICE_OVERRIDE}
[END_COT_EXT]
==================== */
/**
 * module shop for Cotonti Siena
 * Product edit tags
 * @package shop
 * 
 */
defined('COT_CODE') or die('Wrong URL.');

require_once cot_langfile('shop', 'module');
require_once cot_incfile('shop', 'module');
require_once cot_incfile('shop', 'module', 'resources');

/**
 * @todo родительские и дочерние продукты
 *  Или нет - атрибуты !!!
 * @todo сопуствующие категории и товары
 * @todo габариты и вес
 * @todo в наличии, доступность (+ картинки), отложено, min и max количестко покупок 
 * @todo артикул (пока выводить его назначенным ему екстраполем)
 */
if(cot_get_caller() == 'page.add'){
    $pag = $rpage;
}
if (inShopCat($pag['page_cat'])){

    // Грузим доп. инфо по товару
    $pag = Product::loadInfoByPag($pag);
    
    global $calculator;
    $calculator = calculationHelper::getInstance();

    $vendor = Vendor::getById(1);
    if(!$vendor){
       $vendor = Vendor::getById(Vendor::getLoggedVendorId());
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
    if(!isset($pag['_price_tax_id'])){
        $pag['_price_tax_id'] = 0;
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

    if(!isset($pag['price']['discount_id'])){
        $pag['price']['discount_id'] = 0;
    }

    if(empty($pag['price']['curr_id'])){
        $pag['price']['curr_id'] = $vendor->curr_id;
    }

    $currencies     = Currency::getKeyValPairsList();
    $currency       = Currency::getById($pag['price']['curr_id']);
    $venCurrency    = Currency::getById($vendor->curr_id);
    
    // Load Manufacturers

   $uGroups = array();
   foreach($cot_groups as $k => $i){
        $uGroups[$k] = $cot_groups[$k]['title'];
   }
   // Дополнительные цены
   $pagAddP = $pag['add_prices'];

    // Ожидающие пользователи
    if(cot_get_caller() != 'page.add'){
        $waitingUsers = Product::getWaitingUserList($pag['page_id']);
    }
    if (empty($waitingUsers)) $waitingUsers = array();
    if (count($waitingUsers) > 0){
        $wui = 1;
        foreach($waitingUsers as $key => $wuVal){
            $wuUser = $L['Guest'];
            if($wuVal['user_id'] > 0){
                $wuUser = cot_build_user($wuVal['user_id'], $wuVal['user_name']);
            }
            $t->assign(array(
                'ODDEVEN' => cot_build_oddeven($wui),
                'PAGE_PROD_WU_NUM' => $wui,
                'PAGE_PROD_WU_NAME' => htmlspecialchars($wuVal['wu_notify_name']),
                'PAGE_PROD_WU_EMAIL' => htmlspecialchars($wuVal['wu_notify_email']),
                'PAGE_PROD_WU_PHONE' => htmlspecialchars($wuVal['wu_notify_phone']),
                'PAGE_PROD_WU_DATE' => cot_date('datetime_medium', strtotime($wuVal['wu_updated_on'])),
                'PAGE_PROD_WU_USER' => $wuUser,
            ));
            $t->parse('MAIN.WAITING_USERS.ROW');
            $wui++;
        }
        $t->assign(array(
            'PAGE_PROD_WU_ARR' => $waitingUsers,
            'PAGE_PROD_WU_COUNT' => count($waitingUsers),
            'PAGE_PROD_WU_NOTIFY' => cot_checkbox(true, 'rprod_wu_notify', $L['shop']['waiting_users_notify']),
        ));
        $t->parse('MAIN.WAITING_USERS');
    }


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
           'PAGE_VENDOR_CURRENCY_SYMBOL' => $venCurrency->curr_symbol,
       ));
       $t->parse('MAIN.ADD_PRICES_ROW');
   }

    $OverrideOpts = array(
        0 => $L['Disabled'],
        1 => $L['shop']['product_form_override_final'],
       -1 => $L['shop']['product_form_override_to_tax']
    );

    $t->assign(array(
       'PAGE_FORM_PROD_PRICE' => cot_inputbox('text', 'rprod_price', $pag['price']['price'],
               array('size' => '24', 'maxlength' => '255')),
       'PAGE_FORM_CURRENCY' => cot_selectbox($pag['price']['curr_id'], 'rprod_price_currency',
               array_keys($currencies), array_values($currencies), false),
       'PAGE_FORM_PROD_BASE_PRICE' => cot_inputbox('text', 'rprod_basePrice', $pag["prices"]["basePrice"],
               array('size' => '24', 'readonly'=>'readonly')),
       'PAGE_FORM_PROD_SALES_PRICE' => cot_inputbox('text', 'rprod_salesPrice', $pag["prices"]["salesPriceTemp"],
               array('size' => '24')),
       'PAGE_FORM_PROD_SALES' => cot_checkbox(false, 'rprod_use_desired_price',
                $L['shop']['product_form_calc_base_price']),
       'PAGE_PROD_CURRENCY_SYMBOL' => $currency->curr_symbol,
       'PAGE_VENDOR_CURRENCY_SYMBOL' => $venCurrency->curr_symbol,
       'PAGE_FORM_PROD_TAX_RATES' => cot_selectbox($pag['price']['tax_id'], 'rprod_price_tax_id',
               array_keys($taxRates), array_values($taxRates), false, array() ),
       'PAGE_PROD_TAX_RULES_ARR' => $taxRules,
       'PAGE_FORM_PROD_DISCOUNTS' => cot_selectbox($pag['price']['discount_id'], 'rprod_price_discount_id',
               array_keys($discountrates), array_values($discountrates), false, array() ),
       'PAGE_PROD_DBTAX_RULES_ARR' => $DBTaxRules,
       'PAGE_PROD_DATAX_RULES_ARR' => $DATaxRules,
       'PAGE_FORM_PROD_PRICE_OVERRIDE_PRICE' => cot_inputbox('text', 'rprice_override_price', 
               $pag["_price_override_price"], array('size' => '24')),
        'PAGE_FORM_PROD_PRICE_OVERRIDE' => cot_radiobox($pag['_price_override'], 'rprice_override', array_keys($OverrideOpts),
            array_values($OverrideOpts)),
    ));

}