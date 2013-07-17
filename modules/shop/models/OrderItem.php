<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Model class for Order Items
 * @package shop
 * @subpackage Order
 *
 * @property int $oi_id;
 * @property int $order_id
 * @property int $vendor_id
 * @property int $prod_id
 * @property string $prod_sku
 * @property string $prod_title
 * @property float $prod_quantity
 * @property float $prod_price          В валюте продавца
 * @property float $prod_base_price     В валюте продавца
 * @property float $prod_tax
 * @property float $prod_basePriceWithTax
 * @property float $prod_sales_price
 * @property float $prod_sales_price_origin
 * @property float $prod_subtotal_tax
 * @property float $prod_subtotal_discount
 * @property float $prod_subtotal_with_tax
 * @property bool $prod_no_coupon_discount
 * @property int $curr_id
 * @property string $order_status
 *
 *
 * @property Currency $currency
 *
 * @method static OrderItem getById(int $pk)
 * @method static OrderItem[] getList(int $limit = 0, int $offset = 0, string $order = '')
 * @method static OrderItem[] find(mixed $conditions, int $limit = 0, int $offset = 0, string $order = '')
  */
class OrderItem extends ShopModelAbstract{

    /**
     * SQL table name
     * Fatal error: Access level to Currency::$_table_name must be public (as in class ShopModelAbstract) in
     *   .../modules/shop/models/Currency.php on line 17
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
     * Дополнение к Select метода fetch
     * @var array
     */
    public static $_fetchСolumns = array();

    /**
     * Дополнение к FROM метода fetch
     * @var array
     */
    public static $_fetchJoins = array();

    public $page_id;
    public $page_alias;
    public $page_cat;

    /**
     * Для временного хранения цен в корзине и при расчетах
     * @var array
     */
    public $prices;

    /**
     * Основная цена товара в указанная в настройках товара
     * Для временного хранения цены в корзине
     */
    public $price;

    /**
     * Дополнительные цены
     * для расчета цены при работе с корзиной
     * @var array
     */
    public $add_prices;

    /**
     * Static constructor
     */
    public static function __init(){
        global $db_shop_order_items, $db_pages;

        self::$_table_name = $db_shop_order_items;
        self::$_primary_key = 'oi_id';

        // Дополнение к запросу при выборке
        self::$_fetchСolumns = array('p.page_alias', 'p.page_cat');
        self::$_fetchJoins = array("LEFT JOIN $db_pages as p ON p.page_id=`$db_shop_order_items`.prod_id");

        parent::__init();
    }

    /**
     * @param ModelAbstract|array $data
     * @throws Exception
     * @todo поддержка экстраполей страниц и $db_shop_order_items
     */
    public function setData($data){
        global $cfg;

        $class = get_class($this);

        // Принимаем данные из объекта Product
        if( $data instanceof Product){
            $this->page_id = $data->page_id;
            $this->page_alias = $data->page_alias;
            $this->page_cat = $data->page_cat;
            $this->_data['prod_id'] = $data->prod_id;
            $this->_data['prod_sku'] = $data->sku;
            $this->_data['prod_title'] = $data->page_title;
            $this->_data['vendor_id'] = $data->vendor_id;
            $this->_data['prod_no_coupon_discount'] = (bool)$data->no_coupon_discount;
            $this->_data['curr_id'] = $data->price['curr_id'];
            $this->price = $data->price;
            $this->prices = $data->prices;
            $this->add_prices = $data->add_prices;

            // Принять данные, которые содержатся в страницах и есть в полях $db_shop_order_items
            $columns = array();
            eval('$columns = '.$class.'::$_columns;');
            foreach($columns as $column){
                if(mb_strpos($column, 'oi_') !== 0 || in_array($column,
                    array('oi_id', "oi_created_on", "oi_created_by", "oi_updated_on", "oi_updated_by" ))) continue;

                $column = str_replace('oi_', '', $column);
                $key = 'oi_'.$column;
                $column = 'page_'.$column;

                if(isset($data->$column)){
                    $this->__set($key, $data->$column);
                }
            }

            return;
        }

        if ($data instanceof $class) $data = $data->toArray();
        if (!is_array($data)){
            throw new  Exception("Data must be an Array or instance of $class Class or Product");
        }

        foreach($data as $key => $value){
//            $this->__set($key, $value);
            $this->$key = $value;
        }
    }

    /**
     * Save Order
     * @return int id of saved record
     */
    public function save(){
        $itemId = $this->_data['oi_id'];

        if(!$itemId){
            // Добавить новый
            $this->_data['prod_sales_price_origin'] = $this->_data['prod_sales_price'];
        }
        $id = parent::save();
        if(!$itemId){
            // Добавить новый
            if(!$id) return false;
            $this->handleStockAfterStatusChanged($this->_data['order_status'], 'N');
        }

        return $id;
    }

    /**
     * Delete db record
     * @global CotDb $db
     * @return bool
     */
    public function delete(){

        $quantity = $this->prod_quantity;
        $itemOldStatus = $this->order_status;

        $ret = parent::delete();

        if($ret) $this->handleStockAfterStatusChanged('X', $itemOldStatus, $quantity);

        return $ret;
    }

    /**
     * Обновить остатки после смены статуса
     * @todo какое дурное название. Исправить.
     * @param string $newState
     * @param string $oldState
     * @param float $quantity
     */
    public function handleStockAfterStatusChanged($newState, $oldState, $quantity = 0.0){
        if($newState == $oldState) return;

        if(empty($quantity)) $quantity = $this->prod_quantity;

        $tmp = OrderStatus::getList();
        $StatusList = array();
        foreach($tmp as $ost){
            $StatusList[$ost->os_code] = $ost;
        }
        // new product is statut N
        $StatusList['N'] = new OrderStatus(array ( 'os_id' => 0 , 'os_code' => 'N' , 'os_stock_handle' => 'A'));

        if(!array_key_exists($oldState, $StatusList ) or !array_key_exists($newState, $StatusList)) {
            cot_error('The workflow for '.$newState.' or  '.$oldState.' is unknown, take a look on model/orders function
            handleStockAfterStatusChanged','Can\'t process workflow, contact the shopowner. Status is'.$newState);
            return ;
        }

        // P 	Pending
        // C 	Confirmed
        // X 	Cancelled
        // R 	Refunded
        // S 	Shipped
        // N 	New or coming from cart
        //  TO have no product setted as ordered when added to cart simply delete 'P' FROM array Reserved
        // don't set same values in the 2 arrays !!!
        // stockOut is in normal case shipped product
        //order_stock_handle
        // 'A' : sotck Available
        // 'O' : stock Out
        // 'R' : stock reserved
        // the status decreasing real stock ?
        if ($StatusList[$newState]->os_stock_handle == 'O'){
            $isOut = 1;
        }else{
            $isOut = 0;
        }
        if ($StatusList[$oldState]->os_stock_handle == 'O'){
            $wasOut = 1;
        }else{
            $wasOut = 0;
        }
        // Stock change ?
        if ($isOut && !$wasOut)     $product_in_stock = '-';
        else if ($wasOut && !$isOut ) $product_in_stock = '+';
        else $product_in_stock = '=';

        // the status increasing reserved stock(virtual Stock = product_in_stock - product_ordered)
        if ($StatusList[$newState]->os_stock_handle == 'R') $isReserved = 1;
        else $isReserved = 0;
        if ($StatusList[$oldState]->os_stock_handle == 'R') $wasReserved = 1;
        else $wasReserved = 0;

        // reserved stock must be change(all ordered product)
        if ($isReserved && !$wasReserved )     $product_ordered = '+';
        else if (!$isReserved && $wasReserved ) $product_ordered = '-';
        else $product_ordered = '=';

        $this->updateStock ($quantity, $product_in_stock, $product_ordered);

    }

    /**
     * Обновить остатки на складе
     *
     * Добавлено в эту модель, а не в Product, т.к. оно исполняется только из заказа,
     * чтобы не делать лишних запросов к базе связанных с получением товара из БД
     *
     * @param float $quantity Количество
     * @param string $signInStoc
     * @param string $signOrderedStock
     * @return boolean
     * @todo Generate low stock warning
     */
    protected function updateStock($quantity, $signInStoc, $signOrderedStock){
        global $db, $db_pages, $cfg;

        require_once cot_incfile('page', 'module');

        $validFields = array('=','+','-');
        if(!in_array($signInStoc,$validFields)){
            return false;
        }
        if(!in_array($signOrderedStock,$validFields)){
            return false;
        }

        //sanitize fields
        $id = (int) $this->prod_id;

        $quantity = (float) $quantity;
        $update = array();

        if($signInStoc != '=' || $signOrderedStock != '='){

            if($signInStoc!='='){
                $update[] = "`page_{$cfg["shop"]['pextf_in_stock']}` = `page_{$cfg["shop"]['pextf_in_stock']}` " .
                    $signInStoc . $quantity ;
            }
            if($signOrderedStock!='='){
                $update[] = "`page_{$cfg["shop"]['pextf_ordered']}` = `page_{$cfg["shop"]['pextf_ordered']}` " .
                    $signOrderedStock . $quantity ;
            }
            $q = "UPDATE `$db_pages` SET ".implode(", ", $update  )." WHERE `page_id`= $id";

            $db->query($q);
            // контроль отрицательных значений (только в случае, если в БД попадают отрицательные значения)
            $data = array("page_{$cfg['shop']['pextf_in_stock']}" => 0);
            $db->update($db_pages, $data, "page_{$cfg['shop']['pextf_in_stock']}<0");

            $data = array("page_{$cfg['shop']['pextf_ordered']}" => 0);
            $db->update($db_pages, $data, "page_{$cfg['shop']['pextf_ordered']}<0");

            if ($signInStoc == '-') {
                $sql = $db->query("SELECT `page_{$cfg["shop"]['pextf_in_stock']}` <
                    `page_{$cfg["shop"]['pextf_low_stock_notification']}`
                            FROM `$db_pages`
                            WHERE `page_id` = $id");
                if ($sql->fetchColumn() == 1) {

                    // TODO Generate low stock warning
                }
            }
        }
    }

    public function checkForQuantities(&$quantity = 0, &$errorMsg =''){
        global $cfg, $L;

        $stockhandle = $cfg["shop"]['stockhandle'];

        $product = Product::getById($this->prod_id);

        if(!$product && $stockhandle != 'none' && $stockhandle != 'risetime'){
            $errorMsg = $L['shop']['product_out_of_stock'];
            return false;
        }

        return $product->checkForQuantities($quantity, $errorMsg);
    }

    // === Методы для работы с шаблонами ===
    /**
     * Returns all OrderItem tags for coTemplate
     *
     * @param OrderItem|int $item OrderItem object or ID
     * @param string $tagPrefix Prefix for tags
     * @param string $userType User type 'shopper', 'vendor' or 'admin'
     * @param bool $cacheitem Cache tags
     * @return array
     *
     * @todo ORDER_STATUS_TITLE
     */
    public static function generateTags($item, $tagPrefix = '', $userType = 'shopper', $cacheitem = true){
        global $cot_extrafields, $db_shop_order_items, $cfg;

        static $extp_first = null, $extp_main = null;
        static $stCache = array();

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('shop.order.tags.first');
            $extp_main = cot_getextplugins('shop.order.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */
        if ( is_object($item) && is_array($stCache[$item->order_item_id."_".$userType]) ) {
            $tagsArray = $stCache[$item->order_item_id."_".$userType];
        }elseif (is_int($item) && is_array($stCache[$item."_".$userType])){
            $tagsArray = $stCache[$item."_".$userType];

        }else{
            if (!is_object($item) && $item > 0){
                $item = OrderItem::getById($item);
            }

            if ($item){
//                $curr = CurrencyDisplay::getInstance('', $item->vendor_id);
                // вывод стоимости заказа в валюте покупателя
                if ($userType == 'shopper'){
                    $curr = CurrencyDisplay::getInstance(0, $item->vendor_id);
                }else{
                    // $order->order_currency и есть валюта продавца
                    $vendorId = ($item->vendor_id > 0) ? $item->vendor_id : 1;
                    $vendor = Vendor::getById($vendorId);
                    $curr = CurrencyDisplay::getInstance($vendor->curr_id, $item->vendor_id);
                }

                $basePriceWithTax = '';
                $subtotalWithOutDiscount = '';
                if ( !empty($item->prod_basePriceWithTax ) && $item->prod_basePriceWithTax != $item->prod_sales_price
                    && $item->prod_basePriceWithTax > 0) {
                    $basePriceWithTax = $curr->priceDisplay($item->prod_basePriceWithTax);
                    $subtotalWithOutDiscount = $curr->priceDisplay($item->prod_basePriceWithTax * $item->prod_quantity);
                }
                $link = '';
                if ($item->prod_id > 0){
                    $tmp = ($item->page_alias != '') ? array('al'=>$item->page_alias) :
                        array('id'=>$item->prod_id);
                    $tmp['c'] = $item->page_cat;
                    $link = cot_url('page', $tmp);
                    if (!cot_url_check($link)) $link = $cfg['mainurl'].'/'.$link;
                }

                $tagsArray = array(
                    'ID' => $item->oi_id,
                    'PROD_ID' => $item->prod_id,
                    'TITLE' => htmlspecialchars($item->prod_title),
                    'NO_COUPON_DISCOUNT' => $item->prod_no_coupon_discount,
                    'SHORTTITLE' => htmlspecialchars($item->prod_title), // для совместимости с тегами страниц
                    'URL' => $link,
                    'SKU' => htmlspecialchars($item->prod_sku),

                    // Основная цена (себестоимость) в валюте продавца
                    'PRICE' => $curr->priceDisplay($item->prod_price),
                    // Основная цена в валюте продавца c примененным правилом Marge (наценка)
                    'BASE_PRICE' => $curr->priceDisplay($item->prod_base_price),
                    'BASE_PRICE_WIDTH_TAX' => $basePriceWithTax,
                    // Окончательная цена за 1 шт
                    'SALES_PRICE' => $curr->priceDisplay($item->prod_sales_price),
                    // Окончательная цена за 1 шт на момент формирования заказа
                    'SALES_PRICE_ORIGINAL' => $curr->priceDisplay($item->prod_sales_price_origin),
                    'QUANTITY' => $item->prod_quantity,
                    'TAX_AMOUNT' => $curr->priceDisplay($item->prod_tax),

                    // Всего по основной цене в валюте продавца
                    'SUBTOTAL' => $curr->priceDisplay($item->prod_price * $item->prod_quantity),
                    // Всего прибыли по товару:  Окончательная цена - Основная цена
                    'SUBTOTAL_WIDTH_TAX_MINUS_PRICE' => $curr->priceDisplay(
                        $item->prod_subtotal_with_tax - ($item->prod_price * $item->prod_quantity)),

                    // Окончательная цена за указанное количество
                    'SUBTOTAL_WIDTH_TAX' => $curr->priceDisplay($item->prod_subtotal_with_tax),
                    'SUBTOTAL_WIDTHOUT_DISCOUNT' => $subtotalWithOutDiscount,
                    'SUBTOTAL_TAX_AMOUNT' => $curr->priceDisplay($item->prod_subtotal_tax),
                    'SUBTOTAL_DISCOUNT' => $curr->priceDisplay($item->prod_subtotal_discount),
                );

                // Extrafields
                if (isset($cot_extrafields[$db_shop_order_items]))
                {
                    foreach ($cot_extrafields[$db_shop_order_items] as $exfld)
                    {
                        $tag = mb_strtoupper($exfld['field_name']);
                        $field = 'oi_'.$exfld['field_name'];
                        $tagsArray[$tag.'_TITLE'] = isset($L['page_'.$exfld['field_name'].'_title']) ?  $L['page_'.$exfld['field_name'].'_title'] : $exfld['field_description'];
                        $tagsArray[$tag] = cot_build_extrafields_data('shop', $exfld, $item->$field);
                        $tagsArray[$tag.'_VALUE'] = $item->$field;
                    }
                }

                /* === Hook === */
                foreach ($extp_main as $pl)
                {
                    include $pl;
                }
                /* ===== */

                $cacheitem && $item->order_item_id > 0 &&  $stCache[$item->order_item_id."_".$userType] = $tagsArray;
            }else{
                // Item Not Found
                return false;
            }

        }
        $return_array = array();
        foreach ($tagsArray as $key => $val){
            $return_array[$tagPrefix . $key] = $val;
        }

        return $return_array;
    }


}
// Class initialization for some static variables
OrderItem::__init();