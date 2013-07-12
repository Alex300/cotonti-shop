<?php
// Если не указано vendor->title брать название сайти из конфига
defined('COT_CODE') or die('Wrong URL.');

/**
 * Model for shop Vendors
 * @package	shop
 * @subpackage vendor
 *
 * @property int $vendor_id
 * @property string $vendor_title
 * @property int $curr_id    // Валюта продавца по-умолчанию
 * @property array $vendor_acc_currencies
 * @property string $vendor_email
 * @property int $vendor_ownerid
 *
 * @method static Vendor getById(int $pk)
 * @method static Vendor[] getList(int $limit = 0, int $offset = 0, string $order = '')
 * @method static Vendor[] find(mixed $conditions, int $limit = 0, int $offset = 0, string $order = '')
 *
 * @todo vendor extrafields
 * @todo определить необходимые поля для продавцов (vendor_email)
 */
class Vendor extends ShopModelAbstract {

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

    protected $user;

    /**
     * Static constructor
     */
    public static function __init(){
        global $db_shop_vendors;

        self::$_table_name = $db_shop_vendors;
        self::$_primary_key = 'vendor_id';
        parent::__init();
    }

    /**
     * Название магазина
     * @return string
     */
    public function getVendor_title(){
        global $cfg;

        if(!empty($this->_data['vendor_title'])) return $this->_data['vendor_title'];

        if($this->_data['vendor_id'] == 1) return $cfg['maintitle'];

        return '';
    }

    /**
     * Email
     * global CotDb $db
     * @return string
     */
    public function getVendor_email(){
        global $cfg, $db, $db_users;

        if(!empty($this->_data['vendor_email'])) return $this->_data['vendor_email'];

        if(empty($this->user)){
            $sql = "SELECT * FROM $db_users WHERE user_id={$this->_data['vendor_ownerid']}";
            $this->user = $db->query($sql)->fetch();
        }
        return $this->user['user_email'];

//        return '';
    }

    public function getVendor_acc_currencies(){
        if(empty($this->_data['vendor_acc_currencies'])) return NULL;

        return explode(',', $this->_data['vendor_acc_currencies']);
    }


    public function setVendor_acc_currencies($column, $value){
        if(is_array($value)){
            foreach($value as $key=>$val){
                $value[$key] = trim($value[$key]);
            }
            $value = implode(',', $value);
        }

        $this->_data['vendor_acc_currencies'] = $value;
    }

    /**
     * Save Vendor Data
     * @param type $data 
     * @return int id of saved record
     */
//	public function save($data = false){
//        global $db, $db_shop_vendors, $sys, $usr;
//
//        if(!$data) $data = $this;
//        if (is_object($data)) {
//            $tmp = array();
//            $data->vendor_acc_currencies = implode(',', $data->vendor_acc_currencies);
//            // Iterate over the object variables to build the query fields and values.
//            foreach (get_object_vars($data) as $k => $v){
//                // Only process non-null scalars.
//                if (is_array($v) or is_object($v) or $v === null) continue;
//                $tmp[$k] = $v;
//            }
//            $data = $tmp;
//        }else{
//            $data['vendor_acc_currencies'] = implode(',', $data['vendor_acc_currencies']);
//        }
//
//        $data['vendor_updated_on'] = date('Y-m-d H:i:s', $sys['now']);
//        $data['vendor_updated_by'] = $usr['id'];
//        if(!$data['vendor_id']) {
//           // Добавить новый
//           $data['vendor_created_on'] = date('Y-m-d H:i:s', $sys['now']);
//           $data['vendor_created_by'] = $usr['id'];
//           $data['vendor_ownerid'] = $usr['id'];
//           $res = $db->insert($db_shop_vendors, $data);
//           $id = $db->lastInsertId();
//
//           cot_log("Shop. Added vendor #".$id,'plg');
//       }else{
//           // Сохранить существующий
//           $id = $data['vendor_id'];
//           unset($data['vendor_id']);
//           $res = $db->update($db_shop_vendors, $data, "vendor_id={$id}");
//
//           cot_log("Shop. Edited vendor #".$id,'plg');
//       }
//
//       return $id;
//    }
    
//    /**
//     * Получить по id
//     * @param int $id Vendor Id
//     * @return Vendor
//     */
//    public static function getById($id){
//         global $db, $db_shop_vendors, $cfg, $db_users;
//
//        $id = (int)$id;
//        if (!$id) return false;
//
//        $res = $db->query("SELECT v.*, u.user_email as _user_email  FROM $db_shop_vendors as v
//                LEFT JOIN $db_users as u ON v.vendor_ownerid=u.user_id
//                WHERE vendor_id=$id");
//        $data = $res->fetch();
//        if (!$data) return false;
//
//        $data['vendor_acc_currencies'] = explode(',', $data['vendor_acc_currencies']);
//        if (empty($data['vendor_email'])) $data['vendor_email'] = $data['_user_email'];
//        $vendor = new Vendor($data);
//        return  $vendor;
//    }

    /**
     * Get all objects from the database matching given conditions
     *
     * @param mixed $conditions Array of SQL WHERE conditions or a single
     * condition as a string
     * @param int $limit Maximum number of returned records or 0 for unlimited
     * @param int $offset Return records starting from offset (requires $limit > 0)
     * @param string $order
     * @return Vendor[] List of objects matching conditions or null
     * @global CotDb $db
     */
//    protected static function fetch($conditions = array(), $limit = 0, $offset = 0, $order = ''){
//        global $db;
//
//        /** @var Vendor[]  $vendors */
//        $vendors = parent::fetch($conditions, $limit, $offset, $order);
//        if($vendors) return NULL;
//
//        $ids = array();
//        foreach($vendors as $vendor){
//            $
//        }
//    }

    /**
	 * Get the vendor specific currency id
     * @param int $vendorId
	 * @return int Currency Id
	 */
	public static function getCurrencyId ($vendorId = 1){
        global $db, $db_shop_vendors;
        
        $vendorId = (int)$vendorId;
        if (!$vendorId) return false;
        
		$q = "SELECT `curr_id` FROM `$db_shop_vendors` WHERE `vendor_id`=$vendorId";
		$res = $db->query($q)->fetchColumn();

		return $res;
	}

    /**
     * @static
     * @param $vendorId
     * @global CotDb $db
     * return array
     */
    public static function getAccCurrencies($vendorId){
        global $db, $db_shop_vendors;

        $res = $db->query("SELECT v.vendor_acc_currencies, v.curr_id FROM $db_shop_vendors as v
                WHERE vendor_id=$vendorId")->fetch();
        $shop_AccCurr = array();
        $tmp = explode(',', $res['vendor_acc_currencies']);
        foreach($tmp as $item){
            $item = (int)str_replace(' ', '', $item);
            if($item != '' && $item > 0) $shop_AccCurr[] = $item;
        }
        if(!in_array($res['curr_id'], $shop_AccCurr)) $shop_AccCurr[] = (int)$res['curr_id'];

        return $shop_AccCurr;
    }

    /**
     * Получить id залогиненного vendora
     * @return int Current Vendor Id
     * @todo дописать метод
     */
    public static function getLoggedVendorId(){
        global $db, $db_shop_vendors;
        
        $res = $db->query("SELECT COUNT(*) FROM $db_shop_vendors");
        $cnt = $res->fetchColumn();
        // если нет настроенных продавцов
        if($cnt == 0) return 0; 
        
        // Заглушка
        return 1;
    }
}
Vendor::__init();