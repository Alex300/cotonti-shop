<?php
/**
 * Model class for the orders
 * реквизиты вынесены в отдельную таблицу, по крайней мере реквизиты доставки (ST)
 * 
 * @package shop
 * @subpackage order
 *
 */
defined('COT_CODE') or die('Wrong URL.');

/**
 * Model class for the order
 *
 * @package shop
 * @subpackage order
 *
 * @property int $order_id
 * @property int $user_id
 * @property int vendor_id
 * @property string $order_status
 * @property int shipm_id   id способа доставки
 * @property int paym_id    id способа оплаты
 * @property string $coupon_code
 * @property string $order_ip_address
 * @property int $order_user_currency_id        Валюта покупателя
 * @property string $order_user_currency_rate   Курс валюты покупателя
 * @property int $order_currency                Валюта продавца. Все цены указаны в ней
 * @property string $order_customer_note        Примечание покупателя
 * @property string $order_number               Номер заказа
 * @property string $order_pass                 Пароль для доступа по номеру заказа
 *
 * @property float $order_total
 * @property float $order_subtotal_cost       Себестоимость позиций товара в валюте продавца
 * @property float $order_salesPrice          Окончательная Стоимость всех позиций (товаров) заказа
 *                                                          (без купона, доставки и оплаты, только товары)
 * @property float $order_salesPrice_origin   $order_salesPrice на момент формирования заказа
 * @property float $order_billTaxAmount
 * @property float $order_billDiscountAmount
 * @property float $order_discountAmount
 * @property float $order_subtotal          priceWithoutTax
 * @property float $order_tax
 * @property float $order_shipment
 * @property float $order_shipment_tax
 * @property float $order_payment
 * @property float $order_payment_tax
 * @property float $coupon_discount     Скидка по купону
 * @property float $order_discount      // ?? discount order_items
 *
 *
 * @property PaymentMethod $paymentMethod Способ оплаты
 * @property ShipmentMethod $shipmentMethod Способ доставки
 *
 * @method static Order getById(int $pk)
 * @method static Order[] find(mixed $conditions, int $limit = 0, int $offset = 0, string $order = '')
 */
class Order extends ShopModelAbstract{

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

    /**
     * Реквизиты покупателя
     * @var OrderUserInfo
     */
    public $billTo;

    /**
     * Адрес доставки
     * @var OrderUserInfo
     */
    public $shipTo;

    /**
     * Позиции заказа
     * @var OrderItem[]
     */
    public $products = array();

    /**
     * @var array
     */
    public $history = array();

    /**
     * @var array
     */
    public $calc_rules = array();

    /**
     * Static constructor
     */
    public static function __init(){
        global $db_shop_orders;

        self::$_table_name = $db_shop_orders;
        self::$_primary_key = 'order_id';
        parent::__init();
    }

    public function getPaymentMethod($column){
        if (!empty($this->_data['paym_id'])) return PaymentMethod::getById($this->_data['paym_id']);
        return NULL;
    }

    public function setPaymentMethod($column, $value){
        if($value instanceof PaymentMethod) $this->_data['paym_id'] = $value->paym_id;
        if(is_array($value)) $this->_data['paym_id'] = $value['paym_id'];
        if(is_integer($value)) $this->_data['paym_id'] = $value;
        return NULL;
    }

    public function getShipmentMethod($column){
        if (!empty($this->_data['shipm_id'])) return ShipmentMethod::getById($this->_data['shipm_id']);
        return NULL;
    }

    public function setShipmentMethod($column, $value){
        if($value instanceof ShipmentMethod) $this->_data['shipm_id'] = $value->paym_id;
        if(is_array($value)) $this->_data['shipm_id'] = $value['shipm_id'];
        if(is_integer($value)) $this->_data['shipm_id'] = $value;
        return NULL;
    }

    /**
     * Get List
     * @static
     * @param int $user_id
     * @param int $vendor_id
     * @param int $limit Maximum number of returned objects
     * @param int $offset Offset from where to begin returning objects
     * @param string $order Column name to order on
     * @param string $way Order way 'ASC' or 'DESC'
     * @return Order[]
     */
    public static function getList($user_id = 0, $vendor_id = 0, $limit = 0, $offset = 0, $order = 'order_updated_on DESC'){
        $user_id = (int)$user_id;
        $vendor_id = (int)$vendor_id;
        $conditions = array();
        if ($user_id > 0) $conditions[] = "user_id = $user_id";
        if ($vendor_id > 0) $conditions[] = "vendor_id = $vendor_id";

        return Order::fetch($conditions, $limit, $offset, $order);
    }

    /**
     * Returns SQL COUNT for given conditions
     *
     * @param mixed $conditions Array of SQL WHERE conditions or a single
     *      condition as a string
     * @global CotDb $db
     * @return int
     */
    public static function count($conditions = array()){
        global $db, $db_shop_order_history, $db_shop_order_calc_rules,
               $db_shop_order_userinfo, $db_shop_paymethods, $db_shop_shipmethods;

        $where = array();
        $params = array();

        list($where_cond, $params_cond) = Order::parseConditions($conditions);

        if (count($where) > 0) $where_cond .= " AND ".implode(' AND ', $where);
        $where = $where_cond;

        //Получить все товары из всех категорий магазина
        $sql = "SELECT COUNT(*)
            FROM ".self::$_table_name."
            LEFT JOIN $db_shop_order_userinfo AS ubt ON (ubt.order_id=`".self::$_table_name."`.`order_id` AND ubt.oui_address_type='BT')
            LEFT JOIN $db_shop_order_userinfo AS ust ON (ust.order_id=`".self::$_table_name."`.`order_id` AND ust.oui_address_type='ST')
            LEFT JOIN $db_shop_paymethods AS pm ON pm.paym_id=`".self::$_table_name."`.paym_id
            LEFT JOIN $db_shop_shipmethods AS sm ON sm.shipm_id=`".self::$_table_name."`.shipm_id
                 $where";
        $res = $db->query($sql, $params_cond)->fetchColumn();

        return (int)$res;
    }

    /**
     * Get all objects from the database matching given conditions
     *
     * @param mixed $conditions Array of SQL WHERE conditions or a single
     * condition as a string
     * @param int $limit Maximum number of returned records or 0 for unlimited
     * @param int $offset Return records starting from offset (requires $limit > 0)
     * @param string $order Sorting
     * @return Order[] List of objects matching conditions or null
     * @global CotDb $db
     */
    protected static function fetch($conditions = array(), $limit = 0, $offset = 0, $order = 'order_updated_on DESC'){
        global $db, $db_shop_order_history, $db_shop_order_calc_rules;

        /** @var Order[] $items */
        $items = parent::fetch($conditions, $limit, $offset, $order);
        if(!$items) return NULL;

        $ids = array();
        /** @var Order[] $tmpItems Ссылки на объекты заказов с ключами по id */
        $tmpItems = array();
        foreach($items as $item){
            $ids[] = (int)$item->order_id;
            $tmpItems[$item->order_id] = $item;
        }

        $userInfos = OrderUserInfo::find(array(array('order_id', $ids)), 0, 0, 'order_id ASC');
        if($userInfos){
            foreach($userInfos as $elem){
                if($elem->oui_address_type == 'BT') $tmpItems[$elem->order_id]->billTo = $elem;
                if($elem->oui_address_type == 'ST') $tmpItems[$elem->order_id]->shipTo = $elem;
            }
        }

        $products = OrderItem::find(array(array('order_id', $ids)), 0, 0, 'order_id ASC');
        if($products){
            foreach($products as $elem){
                // Тут и в корзине ключем массива выступает id товара
                $tmpItems[$elem->order_id]->products[$elem->prod_id] = $elem;
            }
        }

        // Get History
        $q = "SELECT *
            FROM $db_shop_order_history i
            WHERE order_id IN (".implode(', ', $ids).") ORDER BY oh_created_on ASC";
        $res = $db->query($q);
        while($row = $res->fetch()){
            $tmpItems[$row['order_id']]->history[$row['oh_id']] = $row;
        }

        // Get Calc Rules
        $q = "SELECT *
            FROM $db_shop_order_calc_rules i
            WHERE order_id IN (".implode(', ', $ids).") ORDER BY calc_title ASC";
        $res = $db->query($q);
        while($row = $res->fetch()){
            $tmpItems[$row['order_id']]->calc_rules[$row['ocr_id']] = $row;
        }

        return $items;
    }


    /**
     * Save Order
     * @return int id of saved record
     */
    public function save(){
        global $usr, $db, $db_shop_order_calc_rules, $db_shop_order_history, $sys;

        $order_id = $this->_data['order_id'];
        if(!$order_id) {
            // Добавить новый - оформляем заказ
            $this->_data['order_ip_address'] = $usr['ip'];

            /* === Hook === */
            foreach (cot_getextplugins('shop.order.create') as $pl){
                include $pl;
            }
            if(empty($this->_data['order_number'])){
                $this->_data['order_number'] = $this->generateOrderNumber($usr['id'],4, $this->_data['vendor_id']);
            }
            if(empty($this->_data['order_pass'])){
                $this->_data['order_pass'] = 'p_'.substr( md5((string)time().$this->_data['order_number'] ), 0, 5);
            }
            $this->_data['order_salesPrice_origin'] = $this->_data['order_salesPrice'];
            $this->_data['order_status'] = 'P';

        }

        $id = parent::save();

        if(!$order_id) {
            // Добавить новый - сохранить позиции, Историю, адреса и т.п.
            if(!$id){
                cot_error("Couldn't create order");
                return false;
            }
            // Сохранить позиции
            foreach($this->products as $product){
                $product->order_id = $id;
                $product->order_status = $this->_data['order_status'];
                if (!$product->save()){
                    cot_error("Couldn't create order items");
                    return false;
                }
            }

            // Сохранить правила расчета
            if(!empty($this->calc_rules)){
                foreach($this->calc_rules as $calc){
                    $calc['order_id'] = $id;
                    // Save the record to the database
                    if (!$db->insert($db_shop_order_calc_rules, $calc)){
                        cot_error("Couldn't order calc rules");
                        return false;
                    }
                }
            }

            // Update the order history
            $history = array(
                'order_id' => $id,
                'order_status' => $this->_data['order_status'],  // default - P
                'oh_customer_notified' => 1,
                'oh_created_on' => date('Y-m-d H:i:s', $sys['now']),
                'oh_created_by' => $usr['id'],
            );
            if(!$db->insert($db_shop_order_history, $history)){
                cot_error("Couldn't create order history");
                return false;
            }

            // save user info
            if(!empty($this->billTo)){
                $this->billTo->order_id = $id;
                if (!$this->billTo->save()) {
                    cot_error("Couldn't create order user info");
                    return false;
                }
            }
            if(!empty($this->shipTo) && (empty($this->STsameAsBT))){
                $this->shipTo->order_id = $id;
                if (!$this->shipTo->save()) {
                    cot_error("Couldn't create order user info");
                    return false;
                }
            }

            // учесть расход купона при оформлении заказа
            if (!empty($this->coupon_code)) {
                $coupon = Coupon::getByCode($this->coupon_code);
                // If a gift coupon was used, remove it now
                if ($coupon->coupon_type == 'gift'){
                    $coupon->coupon_published = 0;
                    $coupon->save();
                    cot_log("Coupon '{$this->coupon_code}' is used and disabled ");
                }
            }

            cot_log("Shop. Added order #".$id,'plg');
        }else{
            // Сохранить существующий
            $id = $order_id;
            cot_log("Shop. Edited order #".$id,'plg');
        }

        return $id;
    }

    /**
     * Delete order
     * @global CotDb $db
     * @return bool
     */
    public function delete(){
        global $db_shop_order_items, $db_shop_order_history, $db_shop_order_calc_rules,
               $db_shop_order_userinfo, $db;

        /* === Hook === */
        foreach (cot_getextplugins('shop.order.delete.first') as $pl){
            include $pl;
        }
        /* === /Hook === */

        // Отменим заказ всех позиций
        if (!empty($this->products)){
            foreach($this->products as $product){
                $product->delete();
            }

        }

        // Delete order info
        $db->delete($db_shop_order_calc_rules, "order_id=".$this->order_id);
        $db->delete($db_shop_order_items, "order_id=".$this->order_id);
        $db->delete($db_shop_order_history, "order_id=".$this->order_id);
        $db->delete($db_shop_order_userinfo, "order_id=".$this->order_id);

        parent::delete();

        return true;
    }


    /**
     * Установить статус (UpdateStatus)
     *  При присваивании заказу статуса Использовать этот метод!!!
     * @param string $status
     * @param string $comment
     * @param bool $customer_notify
     * @param bool $include_comment
     * @global CotDb $db
     * @return bool
     */
    public function setStatus($status, $comment = '', $customer_notify = true, $include_comment = true){
        global $cot_plugins, $usr, $sys, $db, $db_shop_order_history, $cfg;

        if (empty($this->order_id)) return false;

        $old_order_status = $this->order_status;
        if($this->order_status == $status){
            // Статус не изменился, ничего не делаем
            return false;
        }

        /* === Hook === */
        // Общий хук при обновлении статуса
        foreach (cot_getextplugins('shop.order.setStatus.first') as $pl){
            include $pl;
        }
        /* === /Hook === */

        /* === Hook === */
        // Хук для выбранных плагинов оплаты/доставки
        if (!empty($cot_plugins['shop.order.setStatus'])){
            //plgVmOnUpdateOrderPayment
            foreach($cot_plugins['shop.order.setStatus'] as $k){
                if ($k['pl_code'] == $this->paymentMethod->pl_code){
                    include $cfg['plugins_dir'] . '/' . $k['pl_file'];
                }
            }

            //plgVmOnUpdateOrderShipment
            foreach($cot_plugins['shop.order.setStatus'] as $k){
                if ($k['pl_code'] == $this->shipmentMethod->pl_code){
                    include $cfg['plugins_dir'] . '/' . $k['pl_file'];
                }
            }
        }
        /* === /Hook === */

        $this->_data['order_status'] = $status;
        $this->save();

        // Обработка позиций заказа
        // использовать метод Update Item
        if (!empty($this->products)){
            foreach($this->products as $item){
                $data = array(
                    'prod_id' => $item->prod_id,
                    'order_status' => $status,
                );
                $this->updateProduct($data);
            }

        }

        /* Update the order history */
        //$this->_updateOrderHist($virtuemart_order_id, $data->order_status, $inputOrder['customer_notified'], $inputOrder['comments']);
        $data = array(
            'order_id' => $this->order_id,
            'order_status' => $status,
            'oh_customer_notified' => $customer_notify,
            'oh_comment' => $comment,
            'oh_created_on' => date('Y-m-d H:i:s', $sys['now']),
            'oh_created_by' => $usr['id'],
        );
        $db->insert($db_shop_order_history, $data);

        if ($customer_notify){

            $vendorId = ($this->vendor_id > 0) ? $this->vendor_id : 1;
            $vendor = Vendor::getById($vendorId);
            $vendorMail = $vendor->vendor_email;
            $shopperMail = $this->billTo->oui_email;

            $notifyShopper = true;
            if ($vendorMail == $shopperMail) $notifyShopper = false;
            if ($usr['id'] == $vendor->vendor_ownerid && $cfg['shop']['notify_if_user_admin'] == 0) $notifyShopper = false;
            if (cot_auth('shop', 'any', 'A') && $cfg['shop']['notify_if_user_admin'] == 0) $notifyShopper = false;
            if ($notifyShopper){
                $this->sendNotify( 'shopper', $include_comment, $comment );
            }
            $this->sendNotify( 'vendor', true, $comment );
        }

        return true;
    }

    /**
     * Не даем напрямую писать в поле статус
     *  При присваивании заказу статуса вызывается этот метод,
     *  А надо использовать setStatus для правильной обработки остатков
     * @param $column
     * @param $value
     * @internal param string $status
     * @return bool
     */
     public function setOrder_status($column, $value){
         if (empty($this->_data['order_status'])){
             // Вероятно это инициализация
             $this->_data['order_status'] = $value;
         }
         return false;
//        return setOrder_status($status, $comment, $customer_notify, $include_comment);
     }

    /**
     * Notifies the customer that the Order Status has been changed
     * не место конечно этому в моделе
     * @todo ссылка на скачиваемые товары
     * @todo hook для плагинов оплаты
     */
    protected function sendNotify($recipient = 'shopper', $include_comment = 1, $comment = ''){
        global $cfg, $vendor, $L;

        $vendorId = ($this->vendor_id > 0) ? $this->vendor_id : 1;

        $vendor = Vendor::getById($vendorId);
        if (!$vendor->vendor_title || $vendor->vendor_title == '' && $vendorId = 1){
            $vendor->vendor_title = $cfg['maintitle'];
        }

        $tpl = cot_tplfile('shop.mail.order_notify.'.$recipient);
        if (!$tpl) $tpl = cot_tplfile('shop.mail.order_notify.shopper');

        // вывод стоимости заказа в валюте покупателя
        // TODO потестировать на мультивалютном
        if ($recipient == 'shopper'){
            $curr = CurrencyDisplay::getInstance($this->order_user_currency_id, $this->vendor_id);
        }else{
            $curr = CurrencyDisplay::getInstance($this->order_currency, $this->vendor_id);
        }
        $orderTotal = array();
        $orderTotal['shopper'] = $curr->priceDisplay($this->order_total);
        $orderTotal['vendor'] = $curr->priceDisplay($this->order_total);
        $orderTotal['admin'] = $orderTotal['vendor'];

        $t = new XTemplate($tpl);

        $orderShopperLink = cot_url('shop', array('m'=>'order', 'order_number'=>$this->order_number,
                                                  'order_pass'=>$this->order_pass));
        if (!cot_url_check($orderShopperLink)) $orderShopperLink = $cfg['mainurl'].'/'.$orderShopperLink;

        $orderVendorLink = cot_url('admin', array('m'=>'shop', 'n'=>'order', 'a'=>'edit', 'id'=>$this->order_id));
        if (!cot_url_check($orderVendorLink)) $orderVendorLink = $cfg['mainurl'].'/'.$orderVendorLink;

        $t->assign(Order::generateTags($this, 'ORDER_', $recipient));
        $t->assign(array(
            'DATE' => cot_date('datetime_fulltext'),
            'ORDER_VENDOR' => $vendor,
            'ORDER_SHOPPER_LINK' => $orderShopperLink,
            'ORDER_VENDOR_LINK' => $orderVendorLink,
            'ORDER_VENDOR_COMMENT' => ($include_comment) ? $comment : '',
        ));
        $t->parse('MAIN');
        $msgBody = $t->text('MAIN');

        if ($recipient == 'shopper'){
            $tmp = isset($L['shop']['mail_shopper_subj_'.$this->order_status]) ?
                $L['shop']['mail_shopper_subj_'.$this->order_status]: $L['shop']['mail_default_subj'];
            $subject = sprintf($tmp, $vendor->vendor_title, $orderTotal['shopper'], $this->order_number);
            $to = $this->billTo->oui_email;
        }else{
            $tmp = isset($L['shop']['mail_vendor_subj_'.$this->order_status]) ?
                $L['shop']['mail_vendor_subj_'.$this->order_status]: $L['shop']['mail_default_subj'];

            $shopperName = "{$this->billTo->oui_lastname} {$this->billTo->oui_firstname} {$this->billTo->oui_middlename}";
            $subject = sprintf($tmp, $shopperName, $orderTotal[$recipient], $this->order_number);
            $to = $vendor->vendor_email;

        }
        cot_mail($to, $subject, $msgBody, '', false, null, true);
    }

    /**
     * Добавить позицию к существующему заказу
     * @param $product
     * @param $quantity
     * @param int $priceOverride
     * @return void
     * @global CotDB $db
     */
    public function add($product, $quantity, $priceOverride = 0){
        global $L;

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

        $errorMsg = '';
        if (!$product->checkForQuantities($quantity, $errorMsg)) {
            // PRODUCT OUT OF STOCK
            return false;
        }

        $this->products[$product->prod_id] = new OrderItem($product);
        $this->products[$product->prod_id]->prod_quantity = $quantity;
        $this->products[$product->prod_id]->order_id = $this->order_id;
        $this->products[$product->prod_id]->order_status = $this->order_status;

        if($priceOverride > 0){
            $this->products[$product->prod_id]->price['override_price'] = $priceOverride;
            $this->products[$product->prod_id]->price['override'] = 1;
        }

        // Loads the product price details
        $calculator = calculationHelper::getInstance();
        $prices = $calculator->setProductPrices($this->products[$product->prod_id], $quantity, false);

        $this->products[$product->prod_id]->prod_subtotal_tax = $this->products[$product->prod_id]->prod_tax * $quantity;
        $this->products[$product->prod_id]->prod_subtotal_discount = -
            $this->products[$product->prod_id]->prices['discountAmount'] * $quantity;
        $this->products[$product->prod_id]->prod_subtotal_with_tax = $this->products[$product->prod_id]->prod_sales_price * $quantity;

        $this->products[$product->prod_id]->save();

        $this->recalculate();
        $this->save();
    }

    /**
     * Обновить позицию заказа
     * @param array $data
     * @global CotDb $db
     * @return bool
     *
     * @todo правильный расчет цен на продукты с учетом сохраненных правил
     */
    public function updateProduct($data){

        $item = $this->products[$data['prod_id']];
        if(empty($item)) return false;

        // Проверить количество
        // Не учитываем для проверки кол-ва резерв для этого заказа
        if(!empty($data['prod_quantity']) && $data['prod_quantity'] > 0){
            $addQuantity = $tmp = $data['prod_quantity'] - $item->prod_quantity;
            $errorMsg = '';
            if($addQuantity > 0){
                // увеличилось количество
                if ($item->checkForQuantities($addQuantity, $errorMsg)) {
                    // Если количество скорректировано:
                    if($addQuantity != $tmp) $data['prod_quantity'] = $item->prod_quantity + $addQuantity;
                }else{
                    unset($data['prod_quantity']);
                }
            }else{
                // Уменьшилось
                // todo Проверить кратность для $data['prod_quantity']
            }
        }

        // Пересчитать стоимость с учетом количества:
        if (isset($data['prod_quantity']) || isset($data['prod_sales_price'])){
            $quantity = $item->prod_quantity;
            if (isset($data['prod_quantity']) && $data['prod_quantity'] > 0){
                $quantity = $data['prod_quantity'];
            }

            $subtotal_with_tax = $item->prod_subtotal_with_tax;
            $salesPrice = $item->prod_sales_price;
            if (isset($data['prod_sales_price']) && $data['prod_sales_price'] > 0){
                $salesPrice = $data['prod_sales_price'];
                $subtotal_with_tax = $salesPrice * $quantity;
            }
            $discount = $item->prod_basePriceWithTax - $salesPrice;
            if ($discount < 0) $discount = 0;

            $data['prod_subtotal_tax'] = $item->prod_tax * $quantity;
            $data['prod_subtotal_discount'] = $discount * $quantity;
            $data['prod_subtotal_with_tax'] = $subtotal_with_tax;
        }

        $itemOldStatus = $item->order_status;
        $oldQuantity   = $item->prod_quantity;

        $item->setData($data);

         if($item->save()){
            if (isset($data['prod_quantity']) || isset($data['prod_sales_price'])){
                $tmp = $quantity - $oldQuantity;
                if ($tmp > 0){
                    // Добавились товары, поставить в резерв
                    $item->handleStockAfterStatusChanged($itemOldStatus, 'N', $tmp);
                }elseif($tmp < 0){
                    // Уменьшились товары
                    $tmp = 0 - $tmp; // Нам нужно положительное количество снять с резерва
                    $item->handleStockAfterStatusChanged('X', $itemOldStatus, $tmp);
                }
                // Пересчитать заказ
                $this->recalculate();
                // Сохранить заказ
                $this->save();
            }
        }

        if(empty($data['order_status'])) $data['order_status'] = $itemOldStatus;
        $item->handleStockAfterStatusChanged($data['order_status'], $itemOldStatus);
    }

    /**
     * Delete order Item
     * @param $prod_id
     * @return bool
     */
    public function deleteProduct($prod_id){

        $prod_id = (int)$prod_id;
        if (empty($prod_id) || empty($this->products[$prod_id])) return false;

        // Удалить
        $ret = $this->products[$prod_id]->delete();

        if ($ret){
            // Удалить из заказа
            unset($this->products[$prod_id]);
            // Пересчитать заказ
            $this->recalculate();
            $this->save();
        }
        return $ret;
    }

    
    /**
	 * Gets the orderId, for anonymous users
     * @param string $orderNumber
     * @param string $orderPass
	 * @return int order_id
	 */
	public static function getIdByOrderNumberPass($orderNumber, $orderPass){
        global $db_shop_orders, $db;
        
		$q = "SELECT `order_id` FROM `$db_shop_orders` 
                WHERE `order_pass`=".$db->quote($orderPass)." AND `order_number`=".$db->quote($orderNumber);
		$res = $db->query($q);
		$orderId = $res->fetchColumn();

		return $orderId;
	}
    
    /**
	 * Gets the orderId by it's string number
     * @param string $orderNumber
     * @return int order_id
	 */
	public function getIdByNumber($orderNumber){
        global $db_shop_orders, $db;
        
		$q = "SELECT `order_id` FROM `$db_shop_orders` WHERE `order_number`=".$db->quote($orderNumber);
		
        $res = $db->query($q);
		$orderId = $res->fetchColumn();
		
        return $orderId;
	}



    // ==== Служубные методы ====
    /**
     * Пересчитать стоимость заказа
     * @todo CalculationHelper
     * Пока дублируем код
     */
    protected function recalculate(){

        $prices = array(
            'salesPrice' => 0,
            'subtotal_cost' => 0,
            'taxAmount' => 0,
            'basePriceWithTax' => 0,
            'discountAmount' => 0,
        );
        foreach ($this->products as $name => $product) {
            if (empty($product->prod_quantity)) {
                // todo translate it!
                cot_error('Error the quantity of the product for calculation is 0, please notify the shopowner,
                    the product id ' . $product->prod_id);
                continue;
            }
            $prices['salesPrice'] = $prices['salesPrice'] + $product->prod_sales_price * $product->prod_quantity;
            $prices['subtotal_cost'] = $prices['subtotal_cost'] + $product->prod_price * $product->prod_quantity;
            $prices['taxAmount'] = $prices['taxAmount'] + $product->prod_subtotal_tax;
            $prices['basePriceWithTax'] = $prices['basePriceWithTax'] + $product->prod_basePriceWithTax * $product->prod_quantity;

        }


        $prices['discountAmount'] = $prices['basePriceWithTax'] - $prices['salesPrice'];
        if ($prices['discountAmount'] < 0) $prices['discountAmount'] = 0;


        $prices['billTotal'] = $prices['salesPrice'] + $this->order_payment + $this->order_shipment - $this->coupon_discount;

        // todo order_subtotal,
        // todo налоги и правила применяемые к заказу в целом
            // Получить все правила расчетов для заказа в целом из cot_shop_order_calc_rules  и применить их к заказу
            // Ну или применить текущие. Так будет лучше
            //Порядок из Calculation helper

        // Итоговая стоимость всех товарных позиций в заказе
        $this->order_salesPrice = $prices['salesPrice'];
        $this->order_subtotal_cost = $prices['subtotal_cost'];
        // Сумма налога по всем позициям
        $this->order_tax = $prices['taxAmount'];
        // Суммарная скидка по всем позициям
        // Определить как "Итоговая базовая стоимость с налогами" - $this->order_salesPrice
        $this->order_discountAmount = $prices['discountAmount'];

        $this->order_billTaxAmount = $this->order_tax + $this->order_shipment_tax + $this->order_payment_tax;
        $this->order_billDiscountAmount = $this->order_discountAmount + $this->coupon_discount;

        $this->order_total = $prices['billTotal'];

//        $this->save();
    }


    /**
     * Generate Order Number String
	 * @param integer $uid The user ID. Defaults to 0 for guests
     * @param integer $length
     * @param integer $vendor_id
	 * @return string A unique ordernumber
	 */
	protected function generateOrderNumber($uid = 0, $length = 10, $vendor_id){
        global $db_shop_orders, $db;

        $vendor_id = (int)$vendor_id;

		$q = "SELECT COUNT(1) FROM $db_shop_orders WHERE `vendor_id`=$vendor_id";
		$res = $db->query($q);

		//We can use that here, because the order_number is free to set, the invoice_number must often follow special rules
		$count = $res->fetchColumn();
		$count = $count + (int)SHOP_ORDER_OFFSET;

		$data = substr( md5( session_id().(string)time().(string)$uid ),0,$length).'0'.$count;

		return $data;
	}
    

    

    
    // === Методы для работы с шаблонами ===
    /**
     * Returns all order tags for coTemplate without order items
     *
     * @param Order|int $order Order object or ID
     * @param string $tagPrefix Prefix for tags
     * @param string $userType User type 'shopper', 'vendor' or 'admin'
     * @param bool $cacheitem Cache tags
     * @return array
     * 
     * @todo ORDER_STATUS_TITLE
     */
    public static function generateTags($order, $tagPrefix = '', $userType = 'shopper', $cacheitem = true){
        global $cfg, $cot_countries;

        static $extp_first = null, $extp_main = null;
        static $order_cache = array();

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('shop.order.tags.first');
            $extp_main = cot_getextplugins('shop.order.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */
        if ( ($order instanceof Order) && is_array($order_cache[$order->order_id."_".$userType]) ) {
            $orderArray = $order_cache[$order->order_id."_".$userType];

        }elseif (is_int($order) && is_array($order_cache[$order."_".$userType])){
            $orderArray = $order_cache[$order."_".$userType];

        }else{
            if (!($order instanceof Order)){
                $order = Order::getById($order);
            }
            if ($order->order_id > 0 || $order instanceof ShopCart){
//                die('Order::generateTags');
                $order_link = '';
                if ($userType == 'admin'){
                    $order_link = cot_url('admin', array('m'=>'shop', 'n'=>'order', 'a'=>'edit', 'id'=>$order->order_id));
                }else{
                    $order_link = cot_url('shop', array('m'=>'order', 'order_number'=>$order->order_number));
                }
                $date_format = 'date_fulltext';

                $BT = array();
                if(!empty($order->billTo)){
                    $tmp = $order->billTo->toArray();
                    $BT = array();
                    // поубираем префиксы
                    foreach($tmp as $key => $value){
                        $key = str_replace('oui_', '', $key);
                        $BT[$key] = $value;
                    }
                    if (!empty($BT['company'])) {
                        $BT['company'] = htmlspecialchars($BT['company']);
                    }
                    if (!empty($BT['country'])){
                        if (!$cot_countries) include_once cot_langfile('countries', 'core');
                        $BT['country_name'] = $cot_countries[$BT['country']];
                    }
                }

                $ST = array();
                if(!empty($order->shipTo)){
                    $tmp = $order->shipTo->toArray();
                    $ST = array();
                    // поубираем префиксы
                    foreach($tmp as $key => $value){
                        $key = str_replace('oui_', '', $key);
                        $ST[$key] = $value;
                    }
                    if (!empty($ST['company'])) {
                        $ST['company'] = htmlspecialchars($ST['company']);
                    }
                    if (!empty($ST['country'])){
                        if (!$cot_countries) include_once cot_langfile('countries', 'core');
                        $ST['country_name'] = $cot_countries[$ST['country']];
                    }
                }


                // вывод стоимости заказа в валюте покупателя
                if ($userType == 'shopper'){
                    $curr = CurrencyDisplay::getInstance(0, $order->vendor_id);
                }else{
                    // $order->order_currency и есть валюта продавца
                    $curr = CurrencyDisplay::getInstance($order->order_currency, $order->vendor_id);
                }

                $orderArray = array(
                    'URL' => $order_link,
                    'CREATE_DATE' => cot_date($date_format, strtotime($order->order_created_on)),
                    'CREATE_DATE_STAMP' => strtotime($order->order_created_on),
                    'MODIFY_DATE' => cot_date($date_format, strtotime($order->order_updated_on)),
                    'MODIFY_DATE_STAMP' => strtotime($order->order_updated_on),
                    'ID' => $order->order_id,
                    'NUMBER' => $order->order_number,
                    'PASS' => $order->order_pass,
                    'STATUS' => $order->order_status,
                    'STATUS_TITLE' => OrderStatus::getTitleByCode($order->order_status),
                    'IP' => $order->order_ip_address,

                    'BILL_TO' => $BT,
                    'BILL_TO_RAW' => Userfields::getFieldsData($order->billTo),
                    'SHIP_TO' => $ST,
                    'SHIP_TO_RAW' => Userfields::getFieldsData($order->shipTo, 'ST'),

                    'CUSTOMER_NOTE' => htmlspecialchars($order->order_customer_note),
                    'HISTORY' => $order->history,
                    'USER_ID' => $order->user_id,
                    'USER_NAME' => $order->user_name,
                    'USER_PROFILE_URL' => cot_url('users', 'm=details&id='.$order->user_id.'&u='.$order->user_name),
                    'VENDOR_ID' => $order->vendor_id,

                    // Промежуточные результаты:
                    'TAX_AMOUNT' => $curr->createPriceDiv('taxAmount','', $order->order_tax,false),
                    'DISCOUNT_AMOUNT' => $curr->createPriceDiv('discountAmount','', $order->order_discountAmount,false),
                    'SALES_PRICE' => $curr->createPriceDiv('salesPrice','', $order->order_salesPrice,false),
                    'SALES_PRICE_ORIGINAL' => $curr->createPriceDiv('salesPrice','', $order->order_salesPrice_origin,false),
                    'SALES_PRICE_MINUS_COST' => $curr->priceDisplay($order->order_salesPrice - $order->order_subtotal_cost),

                    'SUBTOTAL' => $curr->priceDisplay($order->order_subtotal),
                    'SUBTOTAL_COST' => $curr->priceDisplay($order->order_subtotal_cost),

                    'COUPON_CODE' => $order->coupon_code ? $order->coupon_code : '',
                    'SALES_PRICE_COUPON' => $curr->priceDisplay($order->coupon_discount),

                    // Доставка
                    'SHIPMENT_TITLE' => htmlspecialchars($order->shipmentMethod->shipm_title),
                    'SHIPMENT_DESC' => htmlspecialchars($order->shipmentMethod->shipm_desc),
                    'SHIPMENT_PRICE'  => $curr->priceDisplay($order->order_shipment),
                    'SHIPMENT_TAX'  => $curr->createPriceDiv('shipmentTax','', $order->order_shipment_tax,false),
                    'SALES_PRICE_SHIPMENT' => $curr->createPriceDiv('salesPriceShipment','',$order->order_shipment + $order->order_shipment_tax,false),

                    // Оплата
                    'PAYMENT_TITLE' => htmlspecialchars($order->paymentMethod->paym_title),
                    'PAYMENT_DESC' => htmlspecialchars($order->paymentMethod->paym_desc),
                    'PAYMENT_PRICE'  => $curr->priceDisplay($order->order_payment),
                    'PAYMENT_TAX' => $curr->createPriceDiv('paymentTax','', $order->order_payment_tax,false),
                    'SALES_PRICE_PAYMENT' => $curr->createPriceDiv('salesPricePayment','',
                        $order->order_payment + $order->order_payment_tax,false),

                    // Итого
                    'BILL_TAX_AMOUNT' => $curr->createPriceDiv('billTaxAmount','', $order->order_billTaxAmount, false),
                    'BILL_DISCOUNT_AMOUNT' => $curr->createPriceDiv('billDiscountAmount','',$order->order_billDiscountAmount, false),
                    'TOTAL' => $curr->priceDisplay($order->order_total),
                    'TOTAL_RAW' => $order->order_total,

                    'DELETE_URL' => cot_confirm_url(cot_url('admin', 'm=shop&n=order&a=delete&id='.$order->order_id.'&'.cot_xg()), 'admin'),
                );
                if (cot_auth('shop', 'any', 'A')){
                    $orderArray['PASSWORD'] = $order->order_pass;
                }
                // Extrafields
//                if (isset($cot_extrafields[$db_pages])){
//                    foreach ($cot_extrafields[$db_pages] as $row) {
//                        $tag = mb_strtoupper($row['field_name']);
//                        $orderArray[$tag.'_TITLE'] = isset($L['page_'.$row['field_name'].'_title']) ?  $L['page_'.$row['field_name'].'_title'] : $row['field_description'];
//                        $orderArray[$tag] = cot_build_extrafields_data('page', $row, $order["page_{$row['field_name']}"], $order['page_parser']);
//                    }
//                }

                $orderArray['PAYMENT_TEXT']  = $orderArray['SHIPMENT_TEXT'] = '';
                // Тут плагины в теги PAYMENT_TEXT и SHIPMENT_TEXT могут вывести свою инфу
                /* === Hook === */
                foreach ($extp_main as $pl)
                {
                    include $pl;
                }
                /* ===== */

                $cacheitem && $order_cache[$order->order_id."_".$userType] = $orderArray;
            }else{
                // Заказ не существует
            }
        }
        $return_array = array();
        foreach ($orderArray as $key => $val){
            $return_array[$tagPrefix . $key] = $val;
        }

        return $return_array;
    }
            
}
// Class initialization for some static variables
Order::__init();