<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

/**
 * Admin Controller class for the orders
 *
 * @package shop
 * @subpackage admin
 * @copyright http://portal30.ru
 * @method static Order getById(int $pk)
 */
class OrderController{
    

    /**
     * Main (index) Action.
     * Payment Methods List
     */
    public function indexAction(){
        global $adminpath, $cfg,  $L, $sys, $a;

        $sortFields = array(
            array('order_id', 'ID'),
            array('user_id', $L['shop']['customer'].' ID' ),
//            'vendor_id'
            array('order_total', $L['shop']['order_total']),
            array('pm.paym_title', $L['shop']['payment_method']),
            array('sm.shipm_title', $L['shop']['shipment_method']),
//            'order_salesPrice',
            array('order_updated_on', $L['shop']['last_modified']),
            array('order_created_on', $L['shop']['order_create_date']),

        );

        $adminpath[] = '&nbsp;'.$L['shop']['orders'];
        //$adminhelp = $L['adm_help_page'];
        
        $so = cot_import('so', 'G', 'TXT'); // order field name without 'page_'
        $w = cot_import('w', 'G', 'ALP', 4); // order way (asc, desc)
        $fil = cot_import('fil', 'G', 'ARR');  // filters
        $fil['created_from'] = cot_import_date('fil_cf', true, false, 'G');
        $fil['created_to'] = cot_import_date('fil_ct', true, false, 'G');
        $fil['updated_from'] = cot_import_date('fil_uf', true, false, 'G');
        $fil['updated_to'] = cot_import_date('fil_ut', true, false, 'G');

        $maxrowsperpage = $cfg['maxrowsperpage'];
        //$maxrowsperpage = 1;
        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list

        $list_url_path = array('m' => 'shop', 'n' => 'order');
        if(empty($so)){
            $so = 'order_updated_on';
        }else{
            $list_url_path['so'] = $so;
        }
        if(empty($w)){
            $w = 'DESC';
        }else{
            $list_url_path['w'] = $w;
        }
        $vendorId = Vendor::getLoggedVendorId();
        $conditions = array();
        $conditions[] = "vendor_id = {$vendorId}";

        if (!empty($fil)){
            foreach($fil as $key => $val){
                $val = trim(cot_import($val, 'D', 'TXT'));
                if(empty($val)) continue;
                if($key == 'customer'){
                    $val = Order::quote('%'.$val.'%');
                    $conditions[] = array('RAW', "(ubt.lastname LIKE $val OR ubt.firstname LIKE $val OR ubt.middlename LIKE $val)");
                    $list_url_path["fil[customer]"] = $fil[$key];
                }elseif($key == 'created_from'){
                    $conditions[] = array('order_created_on', cot_date('Y-m-d', $fil[$key]), '>=');
                    $list_url_path["fil_cf[year]"] = cot_date('Y', $fil[$key]);
                    $list_url_path["fil_cf[month]"] = cot_date('m', $fil[$key]);
                    $list_url_path["fil_cf[day]"] = cot_date('d', $fil[$key]);
                }elseif($key == 'created_to'){
                    $conditions[] = array('order_created_on', cot_date('Y-m-d', $fil[$key]), '<=');
                    $list_url_path["fil_ct[year]"] = cot_date('Y', $fil[$key]);
                    $list_url_path["fil_ct[month]"] = cot_date('m', $fil[$key]);
                    $list_url_path["fil_ct[day]"] = cot_date('d', $fil[$key]);
                }elseif($key == 'updated_from'){
                    $conditions[] = array('order_updated_on', cot_date('Y-m-d', $fil[$key]), '>=');
                    $list_url_path["fil_uf[year]"] = cot_date('Y', $fil[$key]);
                    $list_url_path["fil_uf[month]"] = cot_date('m', $fil[$key]);
                    $list_url_path["fil_uf[day]"] = cot_date('d', $fil[$key]);
                }elseif($key == 'updated_to'){
                    $conditions[] = array('order_updated_on', cot_date('Y-m-d', $fil[$key]), '<=');
                    $list_url_path["fil_ut[year]"] = cot_date('Y', $fil[$key]);
                    $list_url_path["fil_ut[month]"] = cot_date('m', $fil[$key]);
                    $list_url_path["fil_ut[day]"] = cot_date('d', $fil[$key]);
                }elseif(in_array($key, array('order_number') )){
                    $conditions[] = array($key, "*{$val}*");
                    $list_url_path["fil[{$key}]"] = $val;
                }else{
                    $conditions[] = array($key, $val);
                    $list_url_path["fil[{$key}]"] = $val;
                }
            }
        }
        $orderList = Order::find($conditions, $maxrowsperpage, $d, $so.' '.$w);
        $totallines = Order::count($conditions);

        $pagenav = cot_pagenav('admin', $list_url_path, $d, $totallines, $maxrowsperpage);
        if($pagenav["onpage"] <= 0 && $d > 0){
            // редитрект на первую страницу
            cot_redirect(cot_url('admin', $list_url_path, '', true));
        }

        $act = cot_import('act', 'G', 'TXT');
        if($act == 'delete'){
            $urlArr = $list_url_path;
            if($pagenav['current'] > 0) $urlArr['d'] = $pagenav['current'];
            $id = cot_import('id', 'G', 'INT');
            cot_check_xg();
            $order = Order::getById($id);
            if (!$order){
                cot_error($L['shop']['order_notfound']. "id# ".$id);
                cot_redirect(cot_url('admin', $urlArr, '', true));
            }
            $order->delete();
            cot_message(sprintf($L['shop']['order_deleted'], $id));
            cot_redirect(cot_url('admin', $urlArr, '', true));
        }

        $tmp = array(
            array('RAW', "vendor_id=0 OR vendor_id={$vendorId} OR paym_shared=1")
        );
        $payMethods = PaymentMethod::find($tmp);
        $paymPairs = array();
        if($payMethods){
            foreach($payMethods as $key => $val){
                $paymPairs[$val->paym_id] = $val->paym_title;
            }
        }

        $tmp = array(
            array('RAW', "vendor_id=0 OR vendor_id={$vendorId} OR shipm_shared=1")
        );
        $shipmMethods = ShipmentMethod::find($tmp);
        $shipmPairs = array();
        if($shipmMethods){
            foreach($shipmMethods as $key => $val){
                $shipmPairs[$val->shipm_id] = $val->shipm_title;
            }
        }

        $orderStPairs = OrderStatus::getKeyValPairsList($vendorId);

        $tpl = new XTemplate(cot_tplfile('shop.admin.order'));
        $i = $d+1;
        if ($orderList){
            foreach ($orderList as $order){
                $tpl->assign(Order::generateTags($order, 'LIST_ROW_', 'admin'));
                $delUrlArr = $list_url_path;
                $delUrlArr['act'] = 'delete';
                $delUrlArr['id'] = $order->order_id;
                if($pagenav['current'] > 0) $delUrlArr['d'] = $pagenav['current'];
                $delUrlArr['x'] = $sys['xk'];
                $tpl->assign(array(
                    'LIST_ROW_NUM' => $i,
                    'LIST_ROW_DELETE_URL' => cot_confirm_url(cot_url('admin', $delUrlArr), 'admin'),
                ));
                $i++;
                $tpl->parse('MAIN.LIST_ROW');
            }
        }

        // Сортировка
        $sort = array();
        foreach ($sortFields as $fld){
            if (is_array($fld)){
                $sort[$fld[0]] = $fld[1];
            }else{
                $sort[$fld] = $fld;
            }
        }

        $tpl->assign(array(
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
            'PAGE_TITLE' => $L['shop']['orders'],
            'SORT_BY' => cot_selectbox($so, 'so', array_keys($sort), array_values($sort), false),
            'SORT_WAY' => cot_selectbox($w, 'w', array('ASC', 'DESC'), array($L['Ascending'], $L['Descending']), false),
            'FILTER_STATUS' => cot_selectbox($fil['order_status'], 'fil[order_status]',  array_keys($orderStPairs),
                array_values($orderStPairs)),
            'FILTER_CREATED_FROM' => cot_selectbox_date($fil['created_from'], 'short', 'fil_cf'),
            'FILTER_CREATED_TO' => cot_selectbox_date($fil['created_to'], 'short', 'fil_ct'),
            'FILTER_UPDATED_FROM' => cot_selectbox_date($fil['updated_from'], 'short', 'fil_uf'),
            'FILTER_UPDATED_TO' => cot_selectbox_date($fil['updated_to'], 'short', 'fil_ut'),
            'FILTER_VALUES' => $fil

        ));
        if (count($paymPairs) > 0){
            $tpl->assign(array(
                'FILTER_PAYMENT_METHOD' => cot_selectbox($fil['paym_id'], 'fil[paym_id]',  array_keys($paymPairs), array_values($paymPairs)),
            ));
        }
        if (count($shipmPairs) > 0){
            $tpl->assign(array(
                'FILTER_SHIPMENT_METHOD' => cot_selectbox($fil['shipm_id'], 'fil[shipm_id]',  array_keys($shipmPairs), array_values($shipmPairs)),
            ));
        }
        $tpl->parse('MAIN');
        return $tpl->text();
        //$productlist = $this->_productAdapter->getProductListing(false,false,false,false,true);
	}
    
    /**
     * Создание редактирование заказа
     * @return string
     * @todo Мультипродавец
     */
    public function editAction(){
        global $adminpath, $cfg,  $L, $cot_plugins, $usr, $sys;
        
        $adminpath[] = array(cot_url('admin', array('m' => 'shop', 'n' => 'order')),
            $L['shop']['orders']);

        cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop.admin.order.js');
        cot_rc_link_file($cfg['modules_dir'].'/shop/tpl/shop.css', 'css');
//        cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop_prices.js');

        $id = cot_import('id', 'G', 'INT');

        $act = cot_import('act', 'P', 'ALP');

        $order = null;
        if(!$id){
            $id = 0;
            $adminpath[] = '&nbsp;'.$L['Add'];
        }else{
            if ($act != 'save'){
                $order = Order::getById($id);
            }
            $adminpath[] = $order->order_number." - id#:{$order->order_id} [".$L['Edit']."]";
        }

        $items = array();

        if ($act == 'update_o_status'){
            $items['order_status']  = cot_import('rstatus', 'P', 'ALP', 1);
            $comment                = cot_import('rcomment', 'P', 'HTM');
            $incComment             = cot_import('rinclude_comment', 'P', 'BOL');
            $customerNotify         = cot_import('rcustomer_notify', 'P', 'BOL');
            if ($order->setStatus($items['order_status'], $comment, $customerNotify, $incComment)){
                cot_message($L['shop']['order_updated_success']);
            }
            cot_redirect(cot_url('admin', array('m'=>'shop', 'n'=>'order','a'=>'edit', 'id'=>$id),
                '', true));

        // Обновление позиций
        }elseif($act == 'update_items'){
            $items = cot_import('item', 'P', 'ARR');
            foreach ($items as $prod_id => $item){
                $prod_id = cot_import($prod_id, 'D', 'INT');
                if (!$prod_id) continue;
                $data = array(
                    'prod_id' => $prod_id,
                    'order_status' => cot_import($item['order_status'], 'D', 'ALP', 1),
                    'prod_sales_price' => cot_import($item['finalprice_override'], 'D', 'TXT' ),
                    'prod_quantity' => cot_import($item['quantity_override'], 'D', 'TXT'),
                );
                $data['prod_quantity'] = (float)str_replace(',', '.', $data['prod_quantity']);
                $data['prod_sales_price'] = (float)str_replace(',', '.', $data['prod_sales_price']);
                if ($data['prod_sales_price'] == 0) $data['prod_sales_price'] = $order->products[$prod_id]->prod_sales_price_origin;
                if ($data['prod_quantity'] == 0){
                    unset($data['prod_quantity']);
                }else{
//                    $this->checkForItemQuantities($order->items[$iid]['product_id'], $data['product_quantity'], $order);
                }
                if (!cot_error_found()) $order->updateProduct($data);
            }
            cot_message($L['shop']['order_updated_success']);
            cot_redirect(cot_url('admin', array('m'=>'shop', 'n'=>'order','a'=>'edit', 'id'=>$id),
                '', true));
        }

        global $updStatusTpl;
        $updStatusTpl = cot_tplfile('shop.admin.order.edit_status');

        $orderStateArr = OrderStatus::getKeyValPairsList();

        $currency = CurrencyDisplay::getInstance('', $order->vendor_id);

        $tpl = new XTemplate(cot_tplfile('shop.admin.order'));

        foreach($order->history as $row){
            $tpl->assign(array(
                'ORDER_HISTORY_ROW_DATE' => cot_date('datetime_full', strtotime($row['oh_created_on'])),
                'ORDER_HISTORY_ROW_NOTIFY' => ($row['oh_created_on']) ? $L['Yes'] : $L['No'],
                'ORDER_HISTORY_ROW_STATUS' => htmlspecialchars($orderStateArr[$row['order_status']]),
                'ORDER_HISTORY_ROW_COMMENT' => htmlspecialchars($row['oh_comment']),
            ));
            $tpl->parse('EDIT.FORM.HISTORY_ROW');
        }

        foreach($order->calc_rules as $row){
            $tpl->assign(array(
                'ROW_TAX_TITLE' => htmlspecialchars($row['calc_title']),
                'ROW_TAX_KIND' => $row['calc_kind'],
                'ROW_TAX_AMOUNT' => $currency->priceDisplay($row['calc_value']),
            ));
            $tpl->parse('EDIT.FORM.CALC_RULE_ROW');
        }

        $tpl->assign(Order::generateTags($order, 'ORDER_'));
        $tpl->assign(array(
            'UPDST_NOTIFY' => cot_checkbox('1', 'rcustomer_notify', '', array('id'=>'customer_notified')),
            'UPDST_INC_COMMENT' => cot_checkbox('1', 'rinclude_comment', '', array('id'=>'include_comment')),
            'UPDST_LINE_STATUS' => cot_checkbox('1', 'update_lines', '', array('id'=>'update_lines')),
            'UPDST_ORDER_STATUS' => cot_selectbox($order->order_status,
                "rstatus", array_keys($orderStateArr), array_values($orderStateArr), false),
        ));

        // Позиции заказа
        if ($order){
            $i = 1;
            foreach ($order->products as $item){

                $tpl->assign(OrderItem::generateTags($item, 'ROW_PROD_', 'vendor'));
                $tpl->assign(array(
                    'ROW_PROD_NUMBER' => $i,
                    'ODDEVEN' => cot_build_oddeven($i),
                ));

                // Опции для редактирования позиций
                // todo оплаченные или отгруженные заказы не редактируем
                //    не хорошо, да и путаница будет с остатками на складе
                $priceOverride = '';
                if ($item->prod_sales_price_origin != $item->prod_sales_price) $priceOverride = $item->prod_sales_price;
                $quantityOverride = '';
                $delUrlArr = array('m' => 'shop', 'n'=>'order', 'id'=>$order->order_id, 'prod_id' => $item->prod_id,
                    'a'=>'delete', 'x'=>$sys['xk']);
                $tpl->assign(array(
                    'ROW_EDIT_PROD_ORDER_STATUS' => cot_selectbox($item->order_status,
                        "item[{$item->prod_id}][order_status]", array_keys($orderStateArr),
                        array_values($orderStateArr), false),
                    'ROW_EDIT_PROD_SALES_PRICE_OVERRIDE' => cot_inputbox('text',
                        "item[{$item->prod_id}][finalprice_override]", $priceOverride,
                        array('size' => '8')),
                    'ROW_EDIT_PROD_QUANTITY_OVERRIDE' => cot_inputbox('text',
                        "item[{$item->prod_id}][quantity_override]", $quantityOverride,
                        array('size' => '5')),
                    'ROW_DELETE_ITEM_URL' => cot_confirm_url(cot_url('admin', $delUrlArr), 'admin'),
                ));
                $tpl->parse('EDIT.FORM.ROW');
                $i++;
            }
        }

        $paymentMethods = PaymentMethod::getListByUserId($usr['id'], $usr['id']);
        $paymentSelect = '';
        if($paymentMethods){
            $choosen = $order->paym_id;
            $methods = array();
            foreach($paymentMethods as $method){
                if($method instanceof PaymentMethod) $method = $method->toArray();
                $methods[$method['paym_id']] = $method['paym_title'];
            }
            $paymentSelect = cot_selectbox($choosen, 'paym_id', array_keys($methods), array_values($methods), false,
             array('id'=>'select_paym_id', 'state'=>$choosen));
        }

		// additional plugins - если происходит обновление статуса заказа
        $paymentText = $shipmentText = '';

        /* === Hook === */
        // Тут плагины в теги PAYMENT_TEXT и SHIPMENT_TEXT могут вывести свою инфу
        foreach (cot_getextplugins('shop.admin.order.edit.tags') as $pl){
            include $pl;
        }

        $tpl->assign(array(
            'PAYMENT_SELECT'    => $paymentSelect,
            'PAYMENT_TEXT'      => $paymentText,
            'SHIPMENT_TEXT'     => $shipmentText,
        ));

        $tpl->parse('EDIT.FORM');


        $tpl->assign(array(
            'PAGE_TITLE' => isset($order->order_number) ? $L['shop']['order'].': '.$order->order_number.
                " - id#:{$order->order_id}" : '',
        ));

        /* === Hook === */
        foreach (cot_getextplugins('shop.admin.order.details.tags') as $pl){
            include $pl;
        }

        $tpl->parse('EDIT');
        return $tpl->text('EDIT');
    }

    /**
     * @todo проверка на то что товар уже есть в заказе
     * @return string
     */
    public function ajxAddItemAction(){
        global $usr, $L;

        $ret = array('error' => '', 'message' => '');

        $orderId = cot_import('order_id', 'P', 'INT');
        $prod_id = cot_import('prod_id', 'P', 'INT');
        $quantity = cot_import('quantity', 'P', 'TXT');
        $quantity = (float)(str_replace(',', '.', $quantity));
        $priceOverride = cot_import('priceOverride', 'P', 'TXT');
        $priceOverride = (float)(str_replace(',', '.', $priceOverride));


        if (!$orderId) {
            $ret['error'] = $L['shop']['order_notfound'];
            return json_encode($ret);
        }
        if (!$prod_id) {
            $ret['error'] = $L['shop']['product_not_found'];
            return json_encode($ret);
        }
        if(!$quantity){
            $ret['error'] = $L['shop']['cart_data_not_valid'].": ".$L['shop']['cart_quantity'];
            return json_encode($ret);
        }
        $prod = Product::getById($prod_id);
        if (!$prod){
            $ret['error'] = $L['shop']['product_not_found'];
            return json_encode($ret);
        }
        $order = Order::getById($orderId);
        if (!$order){
            $ret['error'] = $L['shop']['order_notfound'];
            return json_encode($ret);
        }

        if(!empty($order->products[$prod_id])){
            $ret['error'] = sprintf($L['shop']['order_alredy_present'], $prod->page_title);
            return json_encode($ret);
        }
        $order->add($prod, $quantity, $priceOverride);

        return json_encode($ret);
    }

    /**
     * Action. Delete product from the order
     * @access public
     */
    public function deleteAction(){
        global $L;

        $orderId = cot_import('id', 'G', 'INT');
        $prod_id = cot_import('prod_id', 'G', 'INT');

        cot_check_xg();

        if (!$orderId) {
            cot_error($L['shop']['order_notfound']);
            $rUrl = cot_url('admin', 'm=shop&n=order', '', true);
            cot_redirect($rUrl);
        }

        $order = Order::getById($orderId);
        if (!$order){
            cot_error($L['shop']['order_notfound']);
            $rUrl = cot_url('admin', 'm=shop&n=order', '', true);
            cot_redirect($rUrl);
        }

        if (!$prod_id || empty($order->products[$prod_id])) {
            cot_error($L['shop']['product_not_found']);
            $rUrl = cot_url('admin', 'm=shop&n=order&a=edit&id='.$orderId, '', true);
            cot_redirect($rUrl);
        }
        $rUrl = cot_url('admin', 'm=shop&n=order&a=edit&id='.$orderId, '', true);
        if($order->deleteProduct($prod_id)){
//            $order->save();
            cot_message($L['shop']['product_removed']);
        }else{
            cot_error($L['shop']['product_remove_error']);
        }

        cot_redirect($rUrl);
    }

    public function recalculateItemAction(){
        $product_id = cot_import('shop_product_id', 'P', 'INT');
        $product_id = (int)$product_id;
        $inVendorCurr = true;

        $quantity = cot_import('quantity', 'P', 'TXT');
        $quantity = (float)(str_replace(',', '.', $quantity));

        $customPrices = array();

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
        foreach ( $prices as $name => $product_price  ){
            if (!in_array($name, array('costPrice', 'costPriceShopCurrency')) ){
                $priceFormated[$name] = $currency->createPriceDiv($name,'', $prices, true);
            }
        }

        // Всего для данного кол-ва товаров
        $priceFormated['total'] = $prices['salesPrice'] * $quantity;
        $priceFormated['total'] = $currency->priceDisplay($priceFormated['total']);

        return json_encode ($priceFormated);
    }

    /**
     * Checks if the quantity is correct
     * @deprecated
     */
    protected function checkForItemQuantities($product, &$quantity = 0, $order = false, &$errorMsg ='') {
        global $cfg;

        if (!is_array($product)) $product = ShopProduct::getById((int)$product);

        // Не учитываем для проверки кол-ва резерв для этого заказа
        $orderQuantity = 0;
        if ($order){
            foreach($order->items as $item){
                if ($item['product_id'] == $product['page_id']){
                    $orderQuantity = $item['product_quantity'];
                }
            }
        }
        $product['page_'.$cfg['shop']['pextf_ordered']] = $product['page_'.$cfg['shop']['pextf_ordered']] - $orderQuantity;
        if ($product['page_'.$cfg['shop']['pextf_ordered']] <= 0) $product['page_'.$cfg['shop']['pextf_ordered']] = 0.0;

        $cart = ShopCart::getCart();
        if($cart->checkForQuantities($product, $quantity, $errorMsg)){
            if ($errorMsg != '') cot_message($product["page_title"].": ".$errorMsg);
            return true;
        }
        else{
            cot_error($product["page_title"].": ".$errorMsg);
        }
    }

    /**
     * Установить способ оплаты
     * @return string
     */
    public function setPaymentAction(){
        global $cfg, $L;

        $ret = array('error' => '', 'message' => '');

        $orderId = cot_import('order_id', 'P', 'INT');
        $paym_id = cot_import('rpaym_id', 'P', 'INT');

        $order = Order::getById($orderId);
        if (!$order){
            $ret['error'] = $L['shop']['order_notfound'];
            return json_encode ($ret);
        }
        $method = PaymentMethod::getById($paym_id);
        if (!$method){
            $ret['error'] = $L['shop']['order_notfound'];
            return json_encode ($ret);
        }
        $old_paym_id = $order->paym_id; // need for plugins

        $order->paym_id = $paym_id;
        $order->save();

        /* === Hook === */
        foreach (cot_getextplugins('shop.order.set_payment.done') as $pl)
        {
            include $pl;
        }
        /* ===== */

        $ret['paym_id'] = $method->paym_id;
        $ret['paym_title'] = $method->paym_title;
        $ret['paym_desc'] = $method->paym_desc;

        return json_encode ($ret);
    }

}