<?php
defined('COT_CODE') or die('Wrong URL.');
/**
 * Model class for the coupons
 *
 * @package shop
 * @subpackage order
 *
 * @property int $coupon_id;
 * @property string $coupon_code;
 * @property string $coupon_type;
 * @property string $coupon_percent_or_total
 * @property float $coupon_value
 * @property bool $coupon_published;
 *
 * @method static Coupon getById(int $pk)
 * @method static Coupon[] getList(int $limit = 0, int $offset = 0, string $order = '')
 * @method static Coupon[] find(mixed $conditions, int $limit = 0, int $offset = 0, string $order = '')
 *
 */
class Coupon extends ShopModelAbstract{

    /**
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
     * Static constructor
     */
    public static function __init(){
        global $db_shop_coupons ;

        self::$_table_name = $db_shop_coupons;
        self::$_primary_key = 'coupon_id';
        parent::__init();

    }

    /**
     * @param mixed $data Array or Object - свойства
     *   в свойства заполнять только те поля, что есть в таблице + user_name
     */
    public function __construct($data = false) {

        parent::__construct($data);

        if(!$this->_data['coupon_vdate'] || $this->_data['coupon_vdate'] == '') {
            $this->_data['coupon_vdate'] = '0000-00-00';
        }
        if(!$this->_data['coupon_edate'] || $this->_data['coupon_edate'] == ''){
            $this->_data['coupon_edate'] = '0000-00-00';
        }
        $this->_data['coupon_published'] = (int)$this->_data['coupon_published'];
    }

    /**
     * Get Coupon by Code
     * @static
     * @param $code
     * @return Coupon|null
     */
    public static function getByCode($code){

        $res = self::fetch("coupon_code='{$code}'", 1);

        return ($res) ? $res[0] : null;
    }

    /**
     * Coupon Validate
     * @param $orderTotal
     * @param bool $showErrors
     * @return bool
     */
    public function isValid($orderTotal, $showErrors = true){
        global $L, $sys, $cfg;
        $isValid = true;

        /* === Hook === */
        foreach (cot_getextplugins('shop.coupon.validate') as $pl){
            include $pl;
        }
        if (cot_error_found() || !$isValid) return false;

        if(!$this->coupon_published){
            $isValid = false;
            if ($showErrors) cot_error($L['shop']['coupon_notfound']);
        }
        // Проверяем даты
        $startDate = strtotime($this->_data['coupon_vdate']);
        $expiryDate = strtotime($this->_data['coupon_edate']);
        if($startDate > $sys['now']){
            $isValid = false;
            if ($showErrors) cot_error(sprintf($L['shop']['coupon_code_notyet'], cot_date('datetime_medium',$startDate)));
        }
        if($expiryDate <= $sys['now'] && $expiryDate > 100){
            $isValid = false;
            if ($showErrors) cot_error($L['shop']['coupon_code_expired']);
        }

        // Сумма заказа
        if ($orderTotal < $this->_data['coupon_min_order_total']) {
            if(!class_exists('CurrencyDisplay')) require_once($cfg['modules_dir'].DS.'shop'.DS.'lib'.DS.'currencydisplay.php');
            $currency = CurrencyDisplay::getInstance();

            $coupon_value_valid = $currency->priceDisplay($this->_data['coupon_min_order_total']);
            $isValid = false;
            if ($showErrors) cot_error(sprintf($L['shop']['coupon_code_tolow'], $coupon_value_valid));
        }
        return $isValid;
    }

    /**
     * Save data
     * @param Coupon|array|null $data
     * @return int id of saved record
     */
    public function save($data = null){
        global $sys, $usr;

        if(!$data) $data = $this->_data;

        if ($data instanceof Coupon) {
            $data = $data->toArray();
        }

        $data['coupon_updated_on'] = date('Y-m-d H:i:s', $sys['now']);
        $data['coupon_updated_by'] = $usr['id'];

        if(!$data['coupon_id']) {
            // Добавить новый
            $data['coupon_created_on'] = date('Y-m-d H:i:s', $sys['now']);
            $data['coupon_created_by'] = $usr['id'];
        }
        $id = parent::save($data);
        if($id){
            if(!$data['coupon_id']) {
                cot_log("Added new coupon #".$id,'adm');
            }else{
                cot_log("Edited copon #".$id,'adm');
            }
        }
        return $id;
    }

    /**
     * Delete coupon
     * @return bool|void
     */
    public function delete(){
        $ret = parent::delete();

        cot_log("Deleted copon #".$this->_data['coupon_id'], 'adm');
        return $ret;
    }

    // === Методы для работы с шаблонами ===
    /**
     * Returns all order tags for coTemplate
     *
     * @param Coupon|int $coupon Coupon object or ID
     * @param string $tagPrefix Prefix for tags
     * @param bool $cacheitem Cache tags
     * @return array|void
     */
    public static function generateTags($coupon, $tagPrefix = '', $cacheitem = true){
        global $cfg, $L;

        static $extp_first = null, $extp_main = null;
        static $coupon_cache = array();

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('shop.coupon.tags.first');
            $extp_main = cot_getextplugins('shop.coupon.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */
        if ( is_object($coupon) && is_array($coupon_cache[$coupon->coupon_id]) ) {
            $temp_array = $coupon_cache[$coupon->coupon_id];
        }elseif (is_int($coupon) && is_array($coupon_cache[$coupon])){
            $temp_array = $coupon_cache[$coupon];
        }else{
            if (is_int($coupon) && $coupon > 0){
                $coupon = self::getById($coupon);
            }
            if ($coupon->coupon_id > 0){
                $coupon_link = cot_url('admin', array('m'=>'shop', 'n'=>'coupon', 'a'=>'edit', 'id'=>$coupon->coupon_id));
                $date_format = 'datetime_medium';
                $expiry = $L['shop']['never'];
                $eDate = strtotime($coupon->coupon_edate);
                if ($eDate > 100) $expiry = cot_date($date_format, $eDate);
                $temp_array = array(
                    'URL' => $coupon_link,
                    'ID' => $coupon->coupon_id,
                    'CODE' => $coupon->coupon_code,
                    'PER_O_TOTAL' => $L['shop']['coupon_'.$coupon->coupon_percent_or_total],
                    'PER_O_TOTAL_RAW' => $coupon->coupon_percent_or_total,
                    'TYPE' => $L['shop']['coupon_type_'.$coupon->coupon_type],
                    'TYPE_RAW' => $coupon->coupon_type,
                    'VALUE' => $coupon->coupon_value,
                    'MIN_ORDER_TOTAL' => $coupon->coupon_min_order_total,
                    'START_DATE' => cot_date($date_format, strtotime($coupon->coupon_vdate)),
                    'EXPIRY_DATE' => $expiry,
                    'PUBLISHED' => $coupon->coupon_published ? $L['Yes'] : $L['No'],
                    'CREATE_DATE' => cot_date($date_format, strtotime($coupon->coupon_created_on)),
                    'MODIFY_DATE' => cot_date($date_format, strtotime($coupon->coupon_updated_on)),
                    'DELETE_URL' => cot_confirm_url(cot_url('admin', 'm=shop&n=coupon&a=delete&id='.$coupon->coupon_id.'&'.cot_xg()), 'admin'),
                );

                // Extrafields
//                if (isset($cot_extrafields[$db_pages])){
//                    foreach ($cot_extrafields[$db_pages] as $row) {
//                        $tag = mb_strtoupper($row['field_name']);
//                        $temp_array[$tag.'_TITLE'] = isset($L['page_'.$row['field_name'].'_title']) ?  $L['page_'.$row['field_name'].'_title'] : $row['field_description'];
//                        $temp_array[$tag] = cot_build_extrafields_data('page', $row, $order["page_{$row['field_name']}"], $order['page_parser']);
//                    }
//                }

                /* === Hook === */
                foreach ($extp_main as $pl)
                {
                    include $pl;
                }
                /* ===== */
                $cacheitem && $coupon_cache[$coupon->coupon_id] = $temp_array;
            }else{
                // Заказ не существует
//                $temp_array = array(
//                    'TITLE' => (!empty($emptytitle)) ? $emptytitle : $L['Deleted'],
//                    'SHORTTITLE' => (!empty($emptytitle)) ? $emptytitle : $L['Deleted'],
//                );
            }
        }
        $return_array = array();
        foreach ($temp_array as $key => $val){
            $return_array[$tagPrefix . $key] = $val;
        }

        return $return_array;
    }
}

// Class initialization for some static variables
Coupon::__init();