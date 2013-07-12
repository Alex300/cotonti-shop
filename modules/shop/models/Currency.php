<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Model class for shop Currencies
 * @package shop
 * @subpackage Currency
 *
 * @property int $curr_id;
 * @property string $curr_title
 * @property string $curr_symbol
 *
 * @method static Currency getById(int $pk)
 * @method static Currency[] getList(int $limit = 0, int $offset = 0, string $order = '')
 * @method static Currency[] find(mixed $conditions, int $limit = 0, int $offset = 0, string $order = '')
  */
class Currency extends ShopModelAbstract{

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
     * Static constructor
     */
    public static function __init(){
        global $db_shop_currencies ;

        self::$_table_name = $db_shop_currencies;
        self::$_primary_key = 'curr_id';
        parent::__init();
    }

    /**
     * Retireve a key => val list of currencies from the database.
     * @param int $vendorId
     * This is written to get a list for selecting currencies. Therefore it asks for enabled
     * @return array 
     */
    public static function getKeyValPairsList($vendorId=1) {
        global $db, $db_shop_currencies;
        
        $q = "SELECT curr_id, curr_title FROM `$db_shop_currencies` 
            WHERE (`vendor_id` = ".(int)$vendorId." OR `curr_shared`=1) 
                AND curr_published = 1 ORDER BY `curr_title` ASC";
        $sql = $db->query($q);
        
        return $sql->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Changes the currency_id into the right currency_code
     * For example 47 => EUR
     * @param int $id
     * @global CotDb $db
     * @return string
     * @todo кеширование в т.ч. Статичное
     */
    public static function getCode3ById($id){
        global $db, $db_shop_currencies;
        if(is_numeric($id)){
            $id = (int)$id;
            $q = "SELECT `curr_code_3` FROM `$db_shop_currencies` WHERE `curr_id`={$id}";
            $sql = $db->query($q);
            $currInt = $sql->fetchColumn();
            if(empty($currInt)){
                cot_error('Attention, couldn\'t find currency code in the table for id = '.$id);
                return false;
            }
            return $currInt;
        }
        return false;
    }

    /**
     * Save data
     * @param Currency|array|null $data
     * @return int id of saved record
     */
    public function save($data = null){
        global $sys, $usr;

        if(!$data) $data = $this->_data;

        if ($data instanceof Currency) {
            $data = $data->toArray();
        }

        $data['curr_updated_on'] = date('Y-m-d H:i:s', $sys['now']);
        $data['curr_updated_by'] = $usr['id'];

        if(!$data['curr_id']) {
            // Добавить новую запись
            $data['curr_created_on'] = date('Y-m-d H:i:s', $sys['now']);
            $data['curr_created_by'] = $usr['id'];
        }
        $id = parent::save($data);
        if($id){
            if(!$data['curr_id']) {
                cot_log("Added new currency # {$id} - {$data['curr_title']}",'adm');
            }else{
                cot_log("Edited currency # {$id} - {$data['curr_title']}",'adm');
            }
        }
        return $id;
    }

    // === Методы для работы с шаблонами ===
    /**
     * Returns all currency tags for coTemplate
     *
     * @param Currency|int $item Currency object or ID
     * @param string $tagPrefix Prefix for tags
     * @param bool $cacheitem Cache tags
     * @return array|void
     */
    public static function generateTags($item, $tagPrefix = '', $cacheitem = true){
        global $cfg, $L;

        static $extp_first = null, $extp_main = null;
        static $currency_cache = array();

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('shop.currency.tags.first');
            $extp_main  = cot_getextplugins('shop.currency.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */

        if ( ($item instanceof Currency) && is_array($currency_cache[$item->curr_id]) ) {
            $temp_array = $currency_cache[$item->curr_id];
        }elseif (is_int($item) && is_array($currency_cache[$item])){
            $temp_array = $currency_cache[$item];
        }else{
            if (is_int($item) && $item > 0){
                $item = self::getById($item);
            }
            /** @var Currency $item  */
            if ($item->curr_id > 0){
                $item_link = cot_url('admin', array('m'=>'shop', 'n'=>'currency', 'a'=>'edit', 'id'=>$item->curr_id));
                $date_format = 'datetime_medium';
                $temp_array = array(
                    'URL' => $item_link,
                    'ID' => $item->curr_id,
                    'TITLE' => htmlspecialchars($item->curr_title),
                    'EXCHANGE_RATE' => $item->curr_exchange_rate,
                    'SYMBOL' => htmlspecialchars($item->curr_symbol),
                    'CODE_2' => $item->curr_code_2,
                    'CODE_3' => $item->curr_code_3,
                    'NUMERIC_CODE' => $item->curr_numeric_code,
                    'PUBLISHED' => $item->curr_published ? $L['Yes'] : $L['No'],
                    'CREATE_DATE' => cot_date($date_format, strtotime($item->curr_created_on)),
                    'MODIFY_DATE' => cot_date($date_format, strtotime($item->curr_updated_on)),
                    'DELETE_URL' => cot_confirm_url(cot_url('admin', 'm=shop&n=currency&a=delete&id='.$item->curr_id.'&'.cot_xg()), 'admin'),
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
                $cacheitem && $currency_cache[$item->curr_id] = $temp_array;
            }else{
                // Валюта не существует
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
Currency::__init();