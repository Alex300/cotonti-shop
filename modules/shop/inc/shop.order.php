<?php
defined('COT_CODE') or die('Wrong URL.');
//require_once cot_incfile('forms');

/**
 * Controller class for the order
 * 
 * @package shop
 * @subpackage order
 */
class OrderController{
    

    /**
     *
     */
    public function indexAction() {
        global $L, $cfg, $out, $usr, $R, $sys;
        
        $id = cot_import('id', 'G', 'INT');
        $order_number = cot_import('order_number', 'G', 'TXT');

        $payresult = cot_import('payresult', 'G', 'INT');

        // If the user is not logged in, we will check the order number and order pass
        if($usr['id'] == 0){
            $order_pass = cot_import('order_pass', 'G', 'TXT');
            if(!$order_number && !$order_pass){
                // Никаких данных не передано. Незарегу нечего тут делать
                $tmp = shop_readShopCats();
                cot_redirect(cot_url('page', array('c' => $tmp[0]), '', true)); 
            }
            $id = Order::getIdByOrderNumberPass($order_number, $order_pass);
            if (!$id){
                $tmp = shop_readShopCats();
                cot_error($L['shop']['order_notfound']);
                cot_redirect(cot_url('page', array('c' => $tmp[0]), '', true)); // вероятно на главную магазина
            }
        }
        // не передано параметров для просмотра заказа. Выводим список
        if (!$id && !$order_number){
            return $this->orderList();
        }
        if(!$id) {
            $id = Order::getIdByNumber($order_number);
            if (!$id){
                cot_error($L['shop']['order_notfound']);
                cot_redirect(cot_url('shop', 'm=order', '', true));
            }
        }

        global $order; // для xTemlate
        $order = Order::getById($id);
        if (!$order){
            cot_error($L['shop']['order_notfound']);
            cot_redirect(cot_url('shop', 'm=order', '', true));
        }
        // Убедимся, что есть права на просмотр заказа (гоcтя проверили выше)
        if ($usr['id'] > 0){
            if (!cot_auth('shop', 'any', 'A') && $usr['id'] != $order->user_id){
                // Нет прав на просмотр
                cot_error($L['shop']['order_notfound']);
                cot_redirect(cot_url('shop', 'm=order', '', true));
            }
        }

        $urlParams = array('m'=>'order', 'order_number'=>$order->order_number);
        if($usr['id'] == 0) $urlParams['order_pass'] = $order->order_pass;

        if(!empty($payresult)){
            if($payresult == 1){
                cot_message('Спасибо. Ваш заказ оплачен!');
            }else{
                cot_error('При оплате заказа произошла ошибка.');
            }
            cot_redirect(cot_url('shop', $urlParams, '', true));

        }
        // TODO Vendor

        $sys['sublocation'] = $L['shop']['order_info'];
        $out['subtitle'] = $L['shop']['order_info'];
        $out['canonical_uri'] = cot_url('shop', array('m'=>'order', 'order_number'=>$order->order_number));
        
        if ($usr['id'] > 0){
            $crumbs = array(
                array(cot_url('users', array('m'=>'profile')), $L['pro_title']),
                array(cot_url('shop', array('m'=>'order')), $L['shop']['my_orders']),
            );
        }else{
            $crumbs = array(
                array($cfg["shop"]['mainPageUrl'], $cfg["shop"]['mainPageTitle']),
            );
        }
        $crumbs[] = $L['shop']['order_info']." [{$order->order_number}]";
        $breadcrumbs = cot_breadcrumbs($crumbs, $cfg['homebreadcrumb'], true);
        
        global $priceTpl; // Для XTemplate
        $priceTpl = cot_tplfile('shop.order.prodlist.tpl');
        // до фикса https://github.com/Cotonti/Cotonti/issues/822
        $priceTpl = str_replace('.tpl', '', $priceTpl);
        
        $curr = CurrencyDisplay::getInstance('', $order->vendor_id);
        
        $t = new XTemplate(cot_tplfile('shop.order.details'));
        $t->assign(Order::generateTags($order, 'ORDER_'));
        
        $i=1;
        global $prow;   // Для XTemplate

        foreach( $order->products as $pkey =>$prow ) {

            $t->assign(OrderItem::generateTags($prow, 'ROW_PROD_'));
            $t->assign(array(
                'ROW_PROD_NUMBER' => $i,
                'ODDEVEN' => cot_build_oddeven($i),
            ));
            $t->parse('MAIN.PRODUCTS.ROW');
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
                $t->parse('MAIN.PRODUCTS.ROW_TAX_RULES_BILL');
                $i++;
            }
        }

        $t->parse('MAIN.PRODUCTS');

        $paymentText = $shipmentText = '';

        $urlParams['order_pass'] = $order->order_pass;
        $paySuccessUrl = cot_url('shop', array_merge($urlParams, array('payresult' => 1)));
        $payFailUrl = cot_url('shop', array_merge($urlParams, array('payresult' => 2)));
        if (!cot_url_check($paySuccessUrl)) $paySuccessUrl = $cfg['mainurl'].'/'.$paySuccessUrl;
        if (!cot_url_check($payFailUrl)) $payFailUrl = $cfg['mainurl'].'/'.$payFailUrl;

        /* === Hook === */
        // Тут плагины в теги PAYMENT_TEXT и SHIPMENT_TEXT могут вывести свою инфу
        foreach (cot_getextplugins('shop.order.details.tags') as $pl){
            include $pl;
        }

        $t->assign(array(
            'PAGE_TITLE'        => $L['shop']['order_info'],
            'BREAD_CRUMBS'      => $breadcrumbs,
            'PAYMENT_TEXT'      => $paymentText,
            'SHIPMENT_TEXT'     => $shipmentText,
        ));

        // Error and message handling
        cot_display_messages($t);

        $t->parse('MAIN');
        return $t->text('MAIN');
    }

    /**
     * Мои заказы
     */
    protected function orderList(){
        global $usr, $L, $cfg, $out;

        cot_blockguests();

        $sys['sublocation'] = $L['shop']['my_orders'];
        $out['subtitle'] = $L['shop']['my_orders'];
        $out['canonical_uri'] = cot_url('shop', array('m'=>'order'));

        $crumbs = array(
                array(cot_url('users', array('m'=>'profile')), $L['pro_title']),
                array('', $L['shop']['my_orders']),
        );
        $breadcrumbs = cot_breadcrumbs($crumbs, $cfg['homebreadcrumb'], true);

        $usr['isadmin'] = cot_auth('shop', 'any', 'A');

        $so = cot_import('so', 'G', 'ALP'); // order field name without 'page_'
        $w = cot_import('w', 'G', 'ALP', 4); // order way (asc, desc)
        //$fil = cot_import('fil', 'G', 'ARR');  // filters

        $maxrowsperpage = $cfg['maxrowsperpage'];
        //$maxrowsperpage = 1;
        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list

        $so = empty($s) ? 'order_updated_on' : $so;
        $w = empty($w) ? 'DESC' : $w;

        $list_url_path = array('m' => 'order', 'so' => $so, 'w' => $w);
        $orderList = Order::getList($usr['id'], 0, $maxrowsperpage, $d, $so.' '.$w);
        $totallines = Order::count("user_id={$usr['id']}");

        $infoList = UserInfo::find("user_id={$usr['id']}", 0, 0, 'ui_title');

        $t = new XTemplate(cot_tplfile('shop.order.list'));

        if(is_array($infoList)){
            $i = 1;
            foreach($infoList as $info){
                $t->assign(UserInfo::generateTags($info, 'USER_INFO_ROW_'));
                $t->assign(array(
                    'USER_INFO_ROW_NUM' => $i,
                    'USER_INFO_ROW_ODDEVEN' => cot_build_oddeven($i),
                ));
                $t->parse('MAIN.USER_INFO_ROW');
                $i++;
            }
        }

        if(is_array($orderList)){
            $i = $d+1;
            foreach ($orderList as $order){
                $t->assign(Order::generateTags($order, 'LIST_ROW_', 'shopper'));
                $t->assign(array(
                    'LIST_ROW_NUM' => $i,
                    'LIST_ROW_ODDEVEN' => cot_build_oddeven($i),
                ));
                $i++;
                $t->parse('MAIN.LIST_ROW');
            }
        }
        $pagenav = cot_pagenav('shop', $list_url_path, $d, $totallines, $maxrowsperpage);

        $t->assign(array(
            'LIST_PAGINATION' => $pagenav['main'],
            'LIST_PAGEPREV' => $pagenav['prev'],
            'LIST_PAGENEXT' => $pagenav['next'],
            'LIST_CURRENTPAGE' => $pagenav['current'],
            'LIST_TOTALLINES' => $totallines,
            'LIST_MAXPERPAGE' => $maxrowsperpage,
            'LIST_TOTALPAGES' => $pagenav['total'],
            //'LIST_ITEMS_ON_PAGE' => $jj
            'LIST_ITEMS_ON_PAGE' => $pagenav['onpage'],
            'LIST_URL' =>  cot_url('admin', $list_url_path, '', true),
            'PAGE_TITLE'        => $L['shop']['my_orders'],
            'BREAD_CRUMBS'      => $breadcrumbs,
        ));

        /* === Hook === */
        foreach (cot_getextplugins('shop.order.list.tags') as $pl){
            include $pl;
        }

        // Error and message handling
        cot_display_messages($t);

        $t->parse('MAIN');
        return $t->text('MAIN');
    }
}