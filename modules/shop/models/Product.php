<?php
defined('COT_CODE') or die('Wrong URL.');
/**
 * Model class for the product
 * Эта модель отличается от остальных т.к. Cotonti работает со страницами как с массивами
 *
 * @package shop
 * @subpackage product
 * @author Alex
 * @copyright http://portal30.ru
 *
 * @property int $page_id
 * @property int $prod_id
 * @property string $page_title
 * @property string $sku
 * @property float $in_pack
 * @property float $in_stock
 * @property float $ordered
 * @property bool $no_coupon_discount
 *
 * @property string $page_cat
 *
 *
 * @property bool $allow_decimal_quantity
 * @method static Product getById(int $pk)
 * @method static Product[] getList(int $limit = 0, int $offset = 0, string $order = '')
 * @method static Product[] find($conditions, $limit = 0, $offset = 0, $order = '')
 */
class Product extends ShopModelAbstract{


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
     * Основная цена товара в указанная в настройках товара
     * @var array
     */
    public $price;

    /**
     * Для временного хранения цен в корзине и при расчетах
     * @var array
     */
    public $prices;

    /**
     * Дополнительные цены
     * @var array
     */
    public $add_prices;

    /**
     * Информация о производителе
     * @var array
     */
    public $manufacturer;

    /**
     * id Продавца
     * @var int
     */
    public $vendor_id;

    /**
     * Static constructor
     */
    public static function __init(){
        global $db_pages ;

        self::$_table_name = $db_pages;
        self::$_primary_key = 'page_id';
        parent::__init();
    }


    /**
     * isset() handler for object properties.
     * @param string $column Column name
     * @return boolean TRUE if the column has a value, FALSE otherwise.
     */
    public function __isset($column){
        global $cfg;
        $parentVal = parent::__isset($column);
        if($parentVal !== false) return $parentVal;

        if(isset($cfg['shop']["pextf_{$column}"])) return isset($this->_data['page_'.$cfg['shop']["pextf_{$column}"]]);

        return false;
    }

    /**
     * Getter for a column.
     * @param string $column Column name
     * @return mixed Column value
     */
    public function __get($column){
        global $cfg;

        $parentVal = parent::__get($column);
        if($parentVal !== NULL) return $parentVal;

        if(isset($cfg['shop']["pextf_{$column}"])) return $this->_data['page_'.$cfg['shop']["pextf_{$column}"]];

        return NULL;
    }

    /**
     * Setter for a column.
     * @param string $column Column name
     * @param mixed $value Column value
     * @return bool
     */
    public function __set($column, $value){
        return parent::__set($column, $value);
    }

    /**
     * unset() handler for object properties.
     * @param string $column Column name
     */
    public function __unset($column){
        parent::__unset($column);
    }


    public function getProd_id($column){
        return $this->_data['page_id'];
    }


    /**
     * Checks if the quantity is correct
     * @todo fix
     */
    public function checkForQuantities(&$quantity = 0, &$errorMsg ='') {
        global $cfg, $L;

        $stockhandle = $cfg["shop"]['stockhandle'];

        /* Check for a valid quantity */
        if (!is_numeric( $quantity)) {
            $errorMsg = $L['shop']['cart_error_no_valid_quantity'];
            return false;
        }
        /* Check for negative quantity */
        //if ($quantity < 1) {
        if ($quantity < 0) {
            $errorMsg = $L['shop']['cart_error_no_valid_quantity'];
            return false;
        }
        // Check to see if checking stock quantity
        if ($stockhandle != 'none' && $stockhandle != 'risetime') {

            $productsleft = $this->_data['page_'.$cfg['shop']['pextf_in_stock']] - $this->_data['page_'.$cfg['shop']['pextf_ordered']];
            // TODO $productsleft = $product->in_stock - $product->ordered - $quantityincart ;
            if ($quantity > $productsleft ){
                if($productsleft>0 and $stockhandle='disableadd'){
                    $quantity = $productsleft;
                    $errorMsg = sprintf($L['shop']['product_out_of_quantity'], $quantity);
                    $this->setError($errorMsg);
                } else {
                    $errorMsg = $L['shop']['product_out_of_stock'];
                    $this->setError($errorMsg); // Private error retrieved with getError is used only by addJS, so only the latest is fine
                    return false;
                }
            }
        }

        /* Check for the minimum and maximum quantities */
        $min = (float)$this->_data['page_'.$cfg['shop']['pextf_min_order_level']];
        $max = (float)$this->_data['page_'.$cfg['shop']['pextf_max_order_level']];

        // Продажа упаковками
        $inPack = (float)$this->_data["page_{$cfg['shop']['pextf_in_pack']}"];
        if ($inPack > $min && $this->_data["page_{$cfg["shop"]['pextf_order_by_pack']}"] == '1') {
            $min = $inPack;
        }

        if ($min != 0 && $quantity < $min) {
            $errorMsg = sprintf($L['shop']['cart_min_order'], $min);
            $this->setError($errorMsg);
            return false;
        }
        if ($max != 0 && $quantity > $max) {
            $errorMsg = sprintf($L['shop']['cart_max_order'], $max);
            $this->setError($errorMsg);
            return false;
        }

        // Продажа упаковками
        if ($this->_data["page_{$cfg["shop"]['pextf_order_by_pack']}"] == '1'){
            $tmp = $quantity % $inPack;
            if ($tmp != 0){
                $quantity = shop_nearMultiple($quantity, $inPack);
                $unit = '';
                if ($this->_data["page_{$cfg["shop"]['pextf_unit']}"] != ''){
                    $unit = '('.$this->_data["page_{$cfg["shop"]['pextf_unit']}"].')';
                }
                $errorMsg = sprintf($L['shop']['product_out_of_pack'], $quantity, $inPack, $unit);
                $this->setError($errorMsg);
            }
        }else{
            // Если не продаем упаковками, то проверяем шаг
            $step = (float)$this->_data["page_{$cfg['shop']['pextf_step']}"];
            if ($step <= 0) $step = 1;
            if($this->_data["page_{$cfg['shop']['pextf_allow_decimal_quantity']}"] != '1') $step = (int)$step;
            $msg = '';
            if ($min != 0 && $min != $step){
                $tmp = $quantity - $min;
                $newQtt = shop_nearMultiple($tmp, $step) + $min;
                $msg = sprintf($L['shop']['product_quantity_corrected_min'], $this->_data["page_title"], $step, $min, $newQtt);
            }else{
                $newQtt = shop_nearMultiple($quantity, $step);
                $msg = sprintf($L['shop']['product_quantity_corrected'], $this->_data["page_title"], $min, $newQtt);
            }
            if ($quantity != $newQtt){
                cot_message($msg);
                $quantity = $newQtt;
            }
        }
        return true;
    }

    /**
     * Загрузить информацию, спецефичную для товара к странице (цены призводители и т.д.)
     * @todo cache
     */
    public static function loadInfoByPag(&$pag, $withCalc = true){
        $id = (int)$pag['page_id'];
        if(!$id) return false;

        $product = Product::getById($id);
        if(!$product) return false;

//        $pag['manufacturer'] = $product->manufacturer;
//        $pag['add_prices'] = $product->add_prices;
//        $pag['price'] = $product->price;
//        $pag['prices'] = $product->prices;

        $pag = $product->toArray();

        return $pag;
    }

    public function toArray(){
        $data = parent::toArray();

        $data['manufacturer'] = $this->manufacturer;
        $data['add_prices'] = $this->add_prices;
        $data['price'] = $this->price;
        $data['prices'] = $this->prices;

        return $data;
    }

    /**
     * Add Waiting User
     * @param int $prod_id product id
     * @global CotDb $db
     * @return array
     */
    public static function getWaitingUserList($prod_id){
        global $db, $db_shop_waitingusers, $db_users;
        $prod_id = (int)$prod_id;
        if ($prod_id <= 0) return false;
        $res = $db->query("SELECT wu.*, u.user_name FROM $db_shop_waitingusers as wu
            LEFT JOIN $db_users as u ON u.user_id=wu.user_id
            WHERE product_id={$prod_id} AND wu_notified=0")->fetchAll();

        return $res;
    }

    /**
     * Add Waiting User
     * @param array $data
     * @global CotDb $db
     * @return bool
     */
    public static function saveWaitingUser($data){
        global $db, $db_shop_waitingusers, $usr, $sys;

        $data['user_id'] = (int)$data['user_id'];
        $data['product_id'] = (int)$data['product_id'];
        if(!empty($data['user_id']) && $data['user_id'] > 0){
            $where = array(
                "user_id={$data['user_id']}",
                "product_id={$data['product_id']}",
            );
        }else{
            $where = array(
                //"user_id={$data['user_id']}",
                "product_id={$data['product_id']}",
                "wu_notify_email='".$db->prep($data['wu_notify_email'])."'",
            );
        }
        $cnt = $db->query("SELECT COUNT(*) FROM $db_shop_waitingusers WHERE ".implode(' AND ', $where))->fetchColumn();
        $cnt = (int)$cnt;

        $data['wu_updated_on'] = date('Y-m-d H:i:s', $sys['now']);
        $data['wu_updated_by'] = $usr['id'];

        if ($cnt > 0){
            $db->update($db_shop_waitingusers, $data, implode(' AND ', $where));
        }else{
            $data['wu_created_on'] = date('Y-m-d H:i:s', $sys['now']);
            $data['wu_created_by'] = $usr['id'];
            $db->insert($db_shop_waitingusers, $data);
        }

        return true;
    }

    /**
     * Notify customers product is back in stock
     * @param int|array $prod
     * @global CotDb $db;
     * @return bool
     * @todo i18n
     * @todo Do something if the mail cannot be send
     */
    public static function notifyWaitingUser ($prod) {
        global $L, $db, $db_pages, $cfg, $sys, $db_shop_waitingusers;

        if (!is_array($prod)){
            $prod_id = (int)$prod;
            if ($prod_id <= 0) { return false; }

            /* Load the product details */
            $q = "SELECT page_id, page_alias, page_state, page_title, page_ownerid FROM $db_pages AS p WHERE p.page_id={$prod_id} LIMIT 1";
            $prod = $db->query($q)->fetch();
        }
        $w_users = self::getWaitingUserList($prod['page_id']);
        if (!$w_users || count($w_users) == 0) return false;

        $prod['page_pageurl'] = (empty($prod['page_alias'])) ? cot_url('page', 'c='.$prod['page_cat'].'&id='.$prod['page_id']) :
            cot_url('page', 'c='.$prod['page_cat'].'&al='.$prod['page_alias']);
        if(!cot_url_check($prod['page_pageurl'])) $prod['page_pageurl'] = $cfg['mainurl'].'/'.$prod['page_pageurl'];
        $prodLink = "<a href=\"{$prod['page_pageurl']}\">{$prod['page_pageurl']}</a>";

        // Todo Vendor::getIdByUserId($pag['page_ownerid'])
        if (!isset($prod['_vendor_id'])) $prod['_vendor_id'] = 1;

        if (!class_exists('Vendor')) require_once($cfg['modules_dir'].DS.'shop'.DS.'models'.DS.'vendor.php');
        global $vendor;
        $vendor = Vendor::getById($prod['_vendor_id']);
        if (!$vendor->vendor_title || $vendor->vendor_title == '' && $vendorId = 1){
            $vendor->vendor_title = $cfg['maintitle'];
        }

        $tpl = cot_tplfile('shop.mail.notify.waiting_user');
        $subject = sprintf($L['shop']['mail_wu_notify_subj'], $prod['page_title']);
        $subject .= " - ".htmlspecialchars($vendor->vendor_title);

        foreach ($w_users as $w_user) {
            $t = new XTemplate($tpl);
            $title = htmlspecialchars($prod['page_title']);
            $t->assign(array(
                'WU_NAME' => htmlspecialchars($w_user['wu_notify_name']),
                'WU_EMAIL' => htmlspecialchars($w_user['wu_notify_email']),
                'WU_DATE' => cot_date('datetime_medium', strtotime($w_user['wu_updated_on'])),
                'PRODUCT_RAW' => $prod,
                'PRODUCT_TITLE' => $title,
                'WU_MESSAGE' => sprintf($L['shop']['mail_wu_notify_text'], $title, $prodLink),
            ));
            $t->parse('MAIN');
            $msgBody = $t->text('MAIN');
            $to = $w_user['wu_notify_email'];
            cot_mail($to, $subject, $msgBody, '', false, null, true);
            $data = array(
                'wu_notify_date' => date('Y-m-d H:i:s', $sys['now']),
                'wu_notified' => 1,
            );
            $db->update($db_shop_waitingusers, $data, "wu_id={$w_user['wu_id']}");
        }
        return true;
    }

    /**
     * Gets price for product
     * @param float $quantity
     * @return array
     */
    public function getPrices($quantity = 1.0){
        // Loads the product price details
        $calculator = calculationHelper::getInstance();

        $prices = $calculator->setProductPrices($this, $quantity, false);

        return $prices;
    }
    
    /**
     * Проверить соотвествие дополнительных цен пользователю и дате
     * @param type $pag 
     */
    public static function checkAddPrices(&$pag){
        global $sys;
        
        if (empty($pag['add_prices'])) return;
        
        $calculator = calculationHelper::getInstance();
        $uGroups = $calculator->getShopperGroups();

        // Дополнительные цены
        if (is_array($pag['add_prices'])){
            foreach($pag["add_prices"] as $key => $apr) {
                // проверяем группы
                if(!empty($apr['price_groups']) && count(array_intersect($apr['price_groups'], $uGroups))== 0 ){
                    unset($pag["add_prices"][$key]);
                    continue;
                }
                // Проверяем даты
                if (!empty($apr['price_vdate']) && strtotime($apr['price_vdate']) > $sys['now']){
                    unset($pag["add_prices"][$key]);
                    continue;
                }
                $eTime = (!empty($apr['price_edate'])) ? strtotime($apr['price_edate']) : 0;
                if ($eTime > 1 && strtotime($apr['price_edate']) < $sys['now'])  unset($pag["add_prices"][$key]);
            }
        }
    }

    /**
     * Получить массив экстраполей товаров по умолчанию
     * @return array
     */
    public static function getShopDefaultExtrafields(){
        global $cfg;
        $extFields = parse_ini_file($cfg['modules_dir'].DS.'shop'.DS.'setup'.DS.'product_def_extrafields.ini', true);

        return $extFields;
    }


    /**
     * Get all objects from the database matching given conditions
     * @static
     * @param mixed $conditions Array of SQL WHERE conditions or a single
     * @param int $limit Maximum number of returned objects
     * @param int $offset Offset from where to begin returning objects
     * @param string $order Column name to order on and way
     * @global CotDb $db
     * @return array
     *
     * @todo withCalc получать из массива $conditions и удалять от туда это условие
     */
    protected static function fetch($conditions = array(), $limit = 0, $offset = 0, $order = ''){
        global $db, $usr, $cfg, $db_shop_product_prices, $db_shop_product_prices_gr, $db_structure;

        $shopCats = shop_readShopCats();
        if (!$shopCats || count($shopCats) == 0) return false;

        $where = array();
        $params = array();

        $withCalc = true;

        $where_state = $usr['isadmin'] ? '1' : "`".self::$_table_name."`.page_ownerid = {$usr['id']}";
        $where['state'] = "(`".self::$_table_name."`.page_state=0 OR `".self::$_table_name."`.page_state=2 AND $where_state)";
        $where['cat'] = "(`".self::$_table_name."`.page_cat IN (".implode(', ', self::quote($shopCats))."))";

        if ($order != ''){
            if(mb_strpos($order, '.') !== false || mb_strpos($order, ' ') !== false){
                $order = "ORDER BY {$order}";
            }else{
                $order = "ORDER BY `".self::$_table_name."`.`{$order}`";
            }
        }else{
            $order = "ORDER BY `".self::$_table_name."`.`page_id` ASC";
        }
        $limit = ($limit) ? "LIMIT $offset, $limit" : '';

        // под php 5.2 лучше класс указывать явно а не self::parseConditions($conditions);
        list($where_cond, $params_cond) = Product::parseConditions($conditions);

        if (count($where) > 0) $where_cond .= " AND ".implode(' AND ', $where);

        $where = $where_cond;

        //Получить все товары из всех категорий магазина
        $sql = "SELECT `".self::$_table_name."`.*,  m.page_id as _manufacturer_id, m.page_title as _mf_title, m.page_desc as _mf_desc,
                m.page_cat as _mf_cat, m.page_alias as _mf_alias,
                pp.price_id as _price_id, pp.price_price as _price, pp.price_override as _price_override,
                pp.price_override_price as _price_override_price, pp.price_tax_id as _price_tax_id,
                pp.price_discount_id as _price_discount_id, pp.price_currency as _price_currency
            FROM ".self::$_table_name."
            LEFT JOIN ".self::$_table_name." as m ON m.page_id=`".self::$_table_name."`.page_{$cfg["shop"]['pextf_manufacturer_id']}
            LEFT JOIN $db_shop_product_prices as pp ON pp.product_id=`".self::$_table_name."`.page_id AND price_primary=1
            LEFT JOIN $db_structure as cat ON cat.structure_code=`".self::$_table_name."`.page_cat AND cat.structure_area='page'
            $where $order $limit";

        $res = $db->query($sql, $params_cond);
        if($res->rowCount() == 0) return false;

        /** @var Product[] $products */
        $products = array();
        $prodIds = array();
        while($row = $res->fetch()){
            $prodIds[] = (int)$row['page_id'];
            $product = new Product($row);
            $product->vendor_id = isset($row['vendor_id']) ? $row['vendor_id'] : 1;
            $product->price = array(
                'id' => $row['_price_id'],
                'price' => $row['_price'],
                'override' => $row['_price_override'],
                'override_price' => $row['_price_override_price'],
                'tax_id' => $row['_price_tax_id'],
                'discount_id' => $row['_price_discount_id'],
                'curr_id' => $row['_price_currency'],
            );
            $product->add_prices = array();
            $product->manufacturer = array(
                'mf_id' => $row['_manufacturer_id'],
                'mf_title' => $row['_mf_title'],
                'mf_desc' => $row['_mf_desc'],
                'mf_cat' => $row['_mf_cat'],
                'mf_alias' => $row['_mf_alias'],
            );
            unset($row);
            $products["pr_{$product->prod_id}"] = $product;
        }

        if(count($prodIds) == 0) return false;  // Ничего не найдено

        // Выбираем все дополнительные цены
        $q = "SELECT price_id, product_id, price_price, price_vdate, price_edate, price_quantity_start, price_quantity_end
            FROM $db_shop_product_prices WHERE product_id IN (".implode(', ', $prodIds).") AND price_primary=0
            ORDER BY price_quantity_start, price_price";

        $res = $db->query($q);
        $add_prices = array();
        $pids = array();
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $pids[] = $row['price_id'];
            $add_prices[$row['price_id']] = $row;
        }

        // Выбираем группы для цен одним запросом
        if (count($pids) > 0){
            $q = "SELECT price_id, grp_id FROM $db_shop_product_prices_gr WHERE price_id IN (".implode(',', $pids).")
                ORDER BY price_id";
            $res = $db->query($q);
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                $add_prices[$row['price_id']]['price_groups'][] = $row['grp_id'];
            }
            foreach($add_prices as $pid => $price){
                $products["pr_{$price['product_id']}"]->add_prices[$price['price_id']] = $price;
            }
        }
        // /Выбираем все дополнительные цены
        if ($withCalc){
            // Проверить кол-во товара в корзине
            $cart = ShopCart::getInstance(true, false);
            foreach($products as $key => $prod){
                $quantity = 1;
                if (isset($cart->products[$prod->prod_id]) && $cart->products[$prod->prod_id]->prod_quantity > 1 ){
                    $quantity = $cart->products[$prod->prod_id]->prod_quantity;
                }
                $products[$key]->prices = $products[$key]->getPrices($quantity);
            }
        }
        $ret = array();
        foreach($products as $prod){
            $ret[] = $prod;
        }

        return $ret;
    }


    /**
     * Сохранить информацию, спецефичную для товара к странице (цены призводители и т.д.)
     * @todo более полная обработка цен
     * @todo Set the product packaging
     * @todo xRef цены к группам
     * @todo кеширование товаров
     */
    public function saveInfoByPag($prod){
        global $usr, $db, $db_shop_product_prices, $db_shop_product_prices_gr, $cfg;
        //Set the product packaging

        // Обработка цены
        if(!empty($prod['price']['price']) || $prod['price']['price'] == '0'){
            $prod['price']['price'] = str_replace(array(',',' '), array('.',''), $prod['price']['price']);
        } else {
            $prod['price'] = null;
        }
        $data = array(
            'price_primary' => 1,
            'price_price' => $prod['price']['price'],
            'price_currency' => $prod['price']['curr_id'],
            'price_updated_on' => date('Y-m-d H:i:s', $prod['page_updated']),
            'price_updated_by' => $usr['id'],
            'price_tax_id' => $prod['price']['tax_id'],
            'price_discount_id' => $prod['price']['discount_id'],
            'price_override_price' => $prod['price']['override_price'],
            'price_override' => $prod['price']['override'],
        );
        $sql = $db->query("SELECT COUNT(*) FROM $db_shop_product_prices WHERE
                price_primary=1 AND  product_id={$prod['page_id']}");
        $cnt = $sql->fetchColumn();
        if ($cnt > 0){
            $db->update($db_shop_product_prices, $data, "price_primary=1 AND product_id={$prod['page_id']}");
        }else{
            $data['price_created_on'] = date('Y-m-d H:i:s', $prod['page_updated']);
            $data['price_created_by'] = $usr['id'];
            $data['product_id'] = $prod['page_id'];
            $db->insert($db_shop_product_prices, $data);
        }
        // /Обработка цены

        // Дополнительные цены
        $ids = array();
        foreach ($prod['_addprice'] as $key => $val){
            if (!empty($val) || $val == '0'){
                $prod["_addprice"][$key] = str_replace(array(',',' '), array('.',''), $prod["_addprice"][$key]);
                $data = array(
                    'price_primary' => 0,
                    'price_price' => $prod["_addprice"][$key],
                    'price_currency' => $prod['_price_currency'],
                    'price_updated_on' => date('Y-m-d H:i:s', $prod['page_updated']),
                    'price_updated_by' => $usr['id'],
                    'price_quantity_start' => (int)$prod["_addprice_min_quantity"][$key],
                    'price_quantity_end' => (int)$prod["_addprice_max_quantity"][$key],
                );
                $uGroups = $prod['_addprice_groups'][$key];

                $key = mb_substr($key, 2);
                if ((int)$key > 0 && (int)$prod['page_id'] > 0){
                    $db->update($db_shop_product_prices, $data, "price_id = '".(int)$key."'");
                    Product::saveXRef($db_shop_product_prices_gr, $uGroups, 'grp_id', $key, 'price_id'); //группы
                    $ids[] = $key;
                }else{
                    $data['price_created_on'] = date('Y-m-d H:i:s', $prod['page_updated']);
                    $data['price_created_by'] = $usr['id'];
                    $data['product_id'] = $prod['page_id'];
                    $db->insert($db_shop_product_prices, $data);
                    $tmp = $db->lastInsertId();
                    Product::saveXRef($db_shop_product_prices_gr, $uGroups, 'grp_id', $tmp, 'price_id'); //группы
                    $ids[] = $tmp;
                }
            }
        }
        if ((int)$prod['page_id'] > 0 ){
            $where = '';
            if (count($ids) > 0) $where = " AND price_id NOT IN ('".implode("','", $ids)."') ";
            // Удалить ссылки на группы вложенным запросом
            $sql = $db->delete($db_shop_product_prices_gr,
                "price_id IN (SELECT price_id FROM $db_shop_product_prices WHERE price_primary=0
                        AND product_id={$prod['page_id']} $where )");
            $sql = $db->delete($db_shop_product_prices, "price_primary=0 AND product_id={$prod['page_id']} $where ");
        }
        // /Дополнительные цены

        // Update waiting list
        // TODO может надо учесть зарезервированные товары

        if(!empty($prod['_wu_notify'])){
            // $prod["page_{$cfg['shop']['pextf_ordered']}"]);

            if ($prod["page_{$cfg['shop']['pextf_in_stock']}"] > 0 && $prod['_wu_notify'] == '1') {
                Product::notifyWaitingUser($prod);
            }
        }

    }

    /**
     * Соханить внешние ссылки (many to many)
     * @param string $table
     * @param array $data
     * @param string $xField - имя поля с id внешней таблицы (structure_code, grp_id и т.д.)
     * @param int $id - значение _primary (calc_id) таблицы, для которой обновляем связи)
     * @param string $field - имя поля для свази, если не указано используется $this->_primary
     */
    protected static function saveXRef($table, $data, $xField, $id = false, $field = false){
        global $db;

        $id =  (int)$id;
        if (!$field) $field = Product::$_primary_key;
//        if (!$id) $id = $this->{$field};
        if (!$id) return false;

        $query = "SELECT `$xField` FROM $table WHERE {$field}=$id";
        $old_xRefs = $db->query($query)->fetchAll(PDO::FETCH_COLUMN);

        if (!$old_xRefs) $old_xRefs = array();
        $kept_xRefs = array();
        $new_xRefs = array();
        // Find new groups, count old groups that have been left
        $cnt = 0;
        $isstr = false;
        foreach ($data as $item){
            if (empty($item)) continue;
            if (!is_int($item)) $isstr = true;
            $p = array_search($item, $old_xRefs);
            if($p !== false){
                $kept_xRefs[] = $old_xRefs[$p];
                $cnt++;
            }else{
                $new_xRefs[] = $item;
            }
        }
        // Remove old user groups that have been removed
        $rem_xRefs = array_diff($old_xRefs, $kept_xRefs);
        if (count($rem_xRefs) > 0) {
            if ($isstr){
                $inCond = "('".implode("','", $rem_xRefs)."')";
            }else{
                $inCond = "(".implode(",", $rem_xRefs).")";
            }
            $db->delete($table, "$field=$id AND $xField IN $inCond");
        }
        // Add new xRefs
        foreach($new_xRefs as $item){
            if ((!$isstr && $item > 0) || ($isstr && $item!='')){
                $upData = array(
                    $field  => $id,
                    $xField => $item,
                );
                $res = $db->insert($table, $upData);
            }
        }
    }

    // === Методы для работы с шаблонами ===
    /**
     * Returns all product tags for coTemplate
     * Генерируются только теги, спецефичные для магазина
     * Для вывода всех тегов страниц используйте cot_generate_pagetags()
     *
     * @param Array|int $item - Product Array or it's ID
     * @param string $tagPrefix Prefix for tags
     * @param bool $cacheitem Cache tags
     * @return array|void
     *
     * @todo права пользователя на добавления товара в корзину/просмотр цен. Например 2 и 3
     * @todo функция "Задать вопрос по товару"; Можно прямо в обратную связь
     * @todo при мультипродавце - ссылка на страницу с информацией о продавце
     * @todo Availability Image
     * @todo Product Packaging
     * @todo customfieldsRelatedProducts сопутствующие товары (c этим продуктом покупают)
     * @todo customfieldsRelatedCategories сопутствующие категории
     * @todo недавно просмотренные товары (надо ли?)
     */
    public static function generateTags($item, $tagPrefix = '', $cacheitem = true){
        global $cfg, $L, $usr, $shop_priceScript, $shop_headerDone;

        static $extp_first = null, $extp_main = null;
        static $prod_cache = array();

        static $notifyFormOut = false;
        static $uData = false;

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('shop.product.tags.first');
            $extp_main  = cot_getextplugins('shop.product.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */


        if ( (is_array($item)) && is_array($prod_cache[$item['page_id']]) ) {
            $temp_array = $prod_cache[$item['page_id']];
        }elseif (is_int($item) && is_array($prod_cache[$item])){
            $temp_array = $prod_cache[$item];
        }else{
            if (is_int($item) && $item > 0){
                $item = self::getById($item);
            }

            if ($item['page_id'] > 0){

                // Add JS and CSS for shop cart
                if(empty($shop_priceScript)){
                    $jsVars = '';
                    $jsVars .= "shopCartText = '". addslashes( $L['shop']['minicart_added_js'] )."' ;\n" ;
                    $jsVars .= "shopCartError = '". addslashes( $L['shop']['minicart_error_js'] )."' ;\n" ;
                    if (empty($shop_headerDone)){
                        cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop_prices.js');    // без консолидации
                        cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop_dialog.js');
                        cot_rc_link_file($cfg['modules_dir'].'/shop/tpl/shop.css', 'css');
                        cot_rc_embed($jsVars);
                    }else{
                        // Если хедер выполнился уже, придется выводить в футер
                        cot_rc_link_footer($cfg['modules_dir'].'/shop/js/shop_prices.js');    // без консолидации
                        cot_rc_link_footer($cfg['modules_dir'].'/shop/js/shop_dialog.js');
                        cot_rc_link_footer($cfg['modules_dir'].'/shop/tpl/shop.css', 'css');
                        cot_rc_embed_footer($jsVars);
                    }
                    $jsVars = '';
                    $shop_priceScript = true;
                }

                //Минимальное количество для заказа
                $min_order_level = (float)$item["page_{$cfg['shop']['pextf_min_order_level']}"];
                // todo если разрешено заказывать дробями, то $min_order_level может быть >0 и <1
                if ($min_order_level < 1) $min_order_level = 1;


                // Продажа упаковками
                $inPack = (float)$item["page_{$cfg['shop']['pextf_in_pack']}"];
                if ($inPack > $min_order_level && $item["page_{$cfg["shop"]['pextf_order_by_pack']}"] == '1') {
                    $min_order_level = $inPack;
                }

                // Грузим доп. инфо по товару
                // При наличии в $item необходимых данных, вообще не вызывать эту функцию
                if(!is_array($item['add_prices']) || empty($item['price']['id']) || empty($item['manufacturer'])){
                    Product::loadInfoByPag($item);
                }
                Product::checkAddPrices($item);

                // отображать основную цену
                $showBasePrice = $usr['isadmin']; // todo add config settings

                $temp_array = array();

                // Цены
                if($cfg["shop"]['show_prices'] == '1'){

                    $currency = CurrencyDisplay::getInstance( );

                    // Product Price
                    if ($showBasePrice) {
                        $temp_array['PROD_BASE_PRICE'] = $currency->createPriceDiv ( 'basePrice',
                            $L['shop']['product_baseprice'].': ', $item['prices'] );
                        $temp_array['PROD_BASE_PRICE_VARIANT'] =  $currency->createPriceDiv ( 'basePriceVariant',
                            $L['shop']['product_baseprice_variant'].': ', $item['prices'] );
                    }

                    $basePriceWithTax = 0;
                    if ( !empty($item['prices']['basePriceWithTax'] ) && $item['prices']['basePriceWithTax'] != $item['prices']['salesPrice']
                        && $item['prices']['basePriceWithTax'] > 0) {
                        $basePriceWithTax = $item['prices']['basePriceWithTax'];
                    }

//                    $temp_array['PROD_PRICE_VARIANT_MODIFICATION'] = $currency->createPriceDiv ( 'variantModification',
//                        $L['shop']['product_variant_mod'].': ', $item['prices'] );
                    $temp_array['PROD_BASE_PRICE_WIDTH_TAX'] = $currency->createPriceDiv ( 'basePriceWithTax',
                        $L['shop']['product_baseprice_withtax'].': ', $basePriceWithTax );
                    //==
                    $temp_array['PROD_DISCOUNTED_PRICE_WIDTHOUT_TAX'] =  $currency->createPriceDiv ( 'discountedPriceWithoutTax',
                        $L['shop']['product_discounted_price'].': ', $item['prices'] );
                    $temp_array['PROD_SALES_PRICE_WIDTH_DISCOUNT'] =   $currency->createPriceDiv ( 'salesPriceWithDiscount',
                        $L['shop']['product_salesprice_width_discount'].': ', $item['prices'] );
                    $temp_array['PROD_SALES_PRICE'] = $currency->createPriceDiv ( 'salesPrice',
                        $L['shop']['product_salesprice'].': ', $item['prices'] );
                    //==
                    $temp_array['PROD_PRICE_WIDTHOUT_TAX'] = $currency->createPriceDiv ( 'priceWithoutTax',
                        $L['shop']['product_salesprice_widthout_tax'].': ', $item['prices'] );
                    $temp_array['PROD_DISCOUNT'] =  $currency->createPriceDiv ( 'discountAmount',
                        $L['shop']['product_discoun_amount'].': ', $item['prices'] );
                    $temp_array['PROD_TAX'] = $currency->createPriceDiv ( 'taxAmount',
                        $L['shop']['product_tax_amount'].': ', $item['prices'] );


                    // Дополнительные цены
                    if (is_array($item['add_prices'])){
                        $temp_array['PROD_ADD_PRICES'] = array();
                        foreach($item["add_prices"] as $key => $apr) {

                            $temp_array['PROD_ADD_PRICES'][] = array(
                                'PRICE' => $currency->priceDisplay($apr['price_price']),
                                'RAW' => $apr['price_price'],
                                'MIN' => $apr['price_quantity_start'],
                                'MAX' => $apr['price_quantity_end'] > 0 ? $apr['price_quantity_end'] : '' ,
                            );
                        }
                    }

                    // Все цены в чистом виде:
                    $temp_array['PROD_PRICES'] = $item['prices'];

                }

                // Output "Add to cart" button
                if (!$cfg["shop"]['use_as_catalog']){
                    $tpl = new XTemplate(cot_tplfile('shop.cart.addto'));

                    // Add the button
                    $button_lbl = $L['shop']['cart_add_to'];
                    $button_cls = 'addtocart-button';
                    $button_name = 'addtocart';

                    $stockhandle = $cfg['shop']['stockhandle'];
                    if (($stockhandle == 'disableit' or $stockhandle == 'disableadd') &&
                        ($item["page_{$cfg['shop']['pextf_in_stock']}"] - $item["page_{$cfg['shop']['pextf_ordered']}"]) < 1)
                    {
                        $button_lbl = $L['shop']['cart_add_notify'];
                        $button_cls = 'notify-button';
                        $button_name = 'notifycustomer';
                        if (!$notifyFormOut){
                            $userEmail = '';
                            $userName = '';
//                var_dump($cfg['shop']);

                            if (!$uData) $uData = Userfields::getFieldsData($usr['profile'], 'BT', 'notify_', 'user_');
                            if($uData){
                                $userEmail = $uData['email']["field_value"];
                                $userName = $uData[$cfg['shop']['uextf_lastname']]["field_value"].' '.
                                    $uData[$cfg['shop']['uextf_firstname']]["field_value"].' '.
                                    $uData[$cfg['shop']['uextf_middlename']]["field_value"];
                                $userName = trim($userName);
                                $userPhone = trim($uData[$cfg['shop']['uextf_phone']]["field_value"]);
                            }else{
                                $cart = ShopCart::getCart();
                                if(!empty($cart->BT)){
                                    $userEmail = $cart->BT['email'];
                                    $userName = "{$cart->BT['last_name']} {$cart->BT['first_name']} {$cart->BT['middle_name']}";
                                    $userPhone = $cart->BT['phone'];
                                }
                            }
                            $tpl->assign(array(
                                'NOTIFY_NAME' => cot_inputbox('text', 'prod_notify_name', $userName, array(
                                    'id' => 'prod_notify_name')),
                                'NOTIFY_EMAIL' => cot_inputbox('text', 'prod_notify_email', $userEmail, array(
                                    'id' => 'prod_notify_email')),
                                'NOTIFY_PHONE' => cot_inputbox('text', 'prod_notify_phone', $userPhone, array(
                                    'id' => 'prod_notify_phone')),
                            ));
                            $tpl->parse('MAIN.NOTIFY');
                            $notifyFormOut = true;
                        }
                    }

                    $step = (float)$item["page_{$cfg['shop']['pextf_step']}"];
                    if ($step <= 0) $step = 1;
                    if($item["page_{$cfg['shop']['pextf_allow_decimal_quantity']}"] != '1') $step = (int)$step;

                    if ($inPack > 1 && $item["page_{$cfg['shop']['pextf_order_by_pack']}"] == '1'){
                        $step = $inPack;
                    }
                    $priceTotal = $item['prices']['salesPrice'] * $min_order_level;
                    if ($inPack > 0){
                        // Количество упаковок всегда целое )))
                        $packsQt = intval($min_order_level / $inPack);
                        $ost = $min_order_level - ($inPack * $packsQt);
                        $packs = $packsQt;
                    }else{
                        $packsQt = $ost = $packs = 0;
                    }
                    $prodUnit = '';
                    if (!empty($item["page_{$cfg["shop"]['pextf_unit']}"])){
                        $prodUnit = $item["page_{$cfg["shop"]['pextf_unit']}"];
                    }
                    if($ost > 0) $packs .= " (+ $ost  $prodUnit )";
                    if ($stockhandle != 'disableit' || $item["page_{$cfg['shop']['pextf_in_stock']}"] > 0){
                        $tpl->assign(array(
                            'PROD_ID' => $item['page_id'],
                            'PROD_SHORTTITLE' => htmlspecialchars($item['page_title']),
                            'PROD_MIN_ORDER_LEVEL' => $min_order_level,
                            'ADD_BUTTON_LBL' => $button_lbl,
                            'ADD_BUTTON_CLS' => $button_cls,
                            'ADD_BUTTON_NAME' => $button_name,
                            'PROD_MANUFACTURER_ID' => (int)$item['page_'.$cfg["shop"]['pextf_manufacturer_id']],
                            'PROD_CAT' => $item['page_cat'],
                            'PROD_IN_PACK' => $inPack,
                            'PROD_UNIT' => $prodUnit, // TODO i18n
                            'PROD_PRICE_TOTAL' => ($cfg["shop"]['show_prices'] == '1') ? $currency->priceDisplay($priceTotal) :
                                '',     // Минимальная стоимость заказа
                            'PROD_PACKS' => $packs,
                            'ORDER_BY_PACK' => $item["page_{$cfg['shop']['pextf_order_by_pack']}"],
                            'STEP' => $step,
                            'ALLOW_DECIMAL' => $item["page_{$cfg['shop']['pextf_allow_decimal_quantity']}"]
                        ));
                        $tpl->parse('MAIN');
                    }
                }

                $temp_array['PROD_ADD_TO_CART']     = (!$cfg["shop"]['use_as_catalog']) ? $tpl->text('MAIN') : '';
                $temp_array['SHOW_BASE_PRICE']      = $showBasePrice;

                // Производитель
                $mfUrl = '';
                if (!empty($item['manufacturer']) && $item['manufacturer']['mf_id'] > 0){
                    $mfUrlParams = ($item['manufacturer']['mf_alias'] != '') ?
                        array('c'=>$item['manufacturer']['mf_cat'], 'al'=>$item['manufacturer']['mf_alias']) :
                        array('c'=>$item['manufacturer']['mf_cat'], 'id'=>$item['manufacturer']['mf_id']);
                    $mfUrl = cot_url('page', $mfUrlParams);
                }

                // Данные о товаре в чистов виде, не зависящие от экстраполей
                // Могут перекрыть существующие экстраполя. Чтож, не используйте пока префикс prod_ в своих полях
                $temp_array['PROD_MIN_ORDER_LEVEL'] = $min_order_level;
                $temp_array['PROD_MAX_ORDER_LEVEL'] = (float)$item["page_{$cfg['shop']['pextf_max_order_level']}"];
                $temp_array['PROD_IN_PACK'] = $inPack;
                $temp_array['PROD_UNIT'] = $item["page_{$cfg["shop"]['pextf_unit']}"]; // TODO i18n
                $temp_array['PROD_SKU'] = $item["page_{$cfg["shop"]['pextf_sku']}"];
                $temp_array['PROD_PROD_STEP'] = $step;
                $temp_array['PROD_ALLOW_DECIMAL'] = $item["page_{$cfg['shop']['pextf_allow_decimal_quantity']}"];
                $temp_array['PROD_MANUFACTURER_ID'] = (int)$item['manufacturer']['mf_id'];
                $temp_array['PROD_MANUFACTURER_NAME'] = htmlspecialchars($item['manufacturer']['mf_title']); // TODO i18n
                $temp_array['PROD_MANUFACTURER_DESC'] = htmlspecialchars($item['manufacturer']['mf_desc']);  // TODO i18n
                $temp_array['PROD_MANUFACTURER_URL']  = $mfUrl;
                $temp_array['PROD_IN_STOCK']  = $item["page_{$cfg['shop']['pextf_in_stock']}"];
                $temp_array['PROD_ORDERED']  = $item["page_{$cfg['shop']['pextf_ordered']}"];

                /* === Hook === */
                foreach ($extp_main as $pl)
                {
                    include $pl;
                }
                /* ===== */
                $cacheitem && $prod_cache[$item['page_id']] = $temp_array;
            }else{
                // Товар не существует
                return false;
            }
        }

        $return_array = array();
        foreach ($temp_array as $key => $val){
            $return_array[$tagPrefix . $key] = $val;
        }

        return $return_array;
    }

}
Product::__init();