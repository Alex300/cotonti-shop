<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Model class for shop Order Statuses
 * @package shop
 * @subpackage Order
 *
 * @property int $os_id;
 * @property int $os_code;
 * @method static OrderStatus getById(int $pk)
 * @method static OrderStatus[] getList(int $limit = 0, int $offset = 0, string $order = '', string $way = 'ASC')
 * @method static OrderStatus[] find(mixed $conditions, int $limit = 0, int $offset = 0, string $order = '', string $way = 'ASC')
 * @todo нельзя удалить системный статус
 */
class OrderStatus extends ShopModelAbstract{
    
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
     * Static constructor
     */
    public static function __init(){
        global $db_shop_order_status;

        self::$_table_name = $db_shop_order_status;
        self::$_primary_key = 'os_id';
        parent::__init();
    }

    /**
     * @param string $code
     * @return string
     */
    public static function getTitleByCode($code){
        global $L, $db_shop_order_status, $db;

        if(!empty($L['shop']['order_'.$code])) return $L['shop']['order_'.$code];

        $tmp = $db->query("SELECT os_title FROM $db_shop_order_status WHERE os_code=".$db->quote($code))->fetchColumn();
        return $tmp;
    }

    public static function getKeyValPairsList($vendorId = 1){
        global $L, $db_shop_order_status, $db;

        $q = "SELECT os_code, os_title FROM `$db_shop_order_status`
            WHERE (`vendor_id` = ".(int)$vendorId." OR `os_system`=1)
                AND os_published = 1 ORDER BY `os_title` ASC";
        $sql = $db->query($q);
        $res =  $sql->fetchAll(PDO::FETCH_KEY_PAIR);

        $result = array();
        foreach ($res as $key => $title){
            if(!empty($L['shop']['order_'.$key])) {
                $result[$key] = $L['shop']['order_'.$key];
            }else{
                $result[$key] = $title;
            }
        }

        return $result;
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

        $data['os_updated_on'] = date('Y-m-d H:i:s', $sys['now']);
        $data['os_updated_by'] = $usr['id'];

        if(!$data['os_id']) {
            // Добавить новую запись
            $data['os_created_on'] = date('Y-m-d H:i:s', $sys['now']);
            $data['os_created_by'] = $usr['id'];
        }
        $id = parent::save($data);
        if($id){
            if(!$data['os_id']) {
                cot_log("Added new Order Status # {$id} - {$data['os_title']}",'adm');
            }else{
                cot_log("Edited Order Status # {$id} - {$data['os_title']}",'adm');
            }
        }
        return $id;
    }

    /**
     * Delete order
     * @return bool
     */
    public function delete(){
        if ($this->os_system != 0){
            return false;
        }else{
            parent::delete();
            cot_log("Deleted Order Status # {$this->_data['os_id']} - {$this->_data['os_title']}",'adm');
        }
    }

    // === Методы для работы с шаблонами ===
    /**
     * Returns all currency tags for coTemplate
     *
     * @param OrderStatus|int $item OrderStatus object or ID
     * @param string $tagPrefix Prefix for tags
     * @param bool $cacheitem Cache tags
     * @return array|void
     */
    public static function generateTags($item, $tagPrefix = '', $cacheitem = true){
        global $cfg, $L;

        static $extp_first = null, $extp_main = null;
        static $ordrerst_cache = array();

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('shop.orderstatus.tags.first');
            $extp_main  = cot_getextplugins('shop.orderstatus.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */
        if ( ($item instanceof OrderStatus) && is_array($ordrerst_cache[$item->os_id]) ) {
            $temp_array = $ordrerst_cache[$item->os_id];
        }elseif (is_int($item) && is_array($ordrerst_cache[$item])){
            $temp_array = $ordrerst_cache[$item];
        }else{
            if (is_int($item) && $item > 0){
                $item = self::getById($item);
            }

            if ($item->os_id > 0){
                $item_link = cot_url('admin', array('m'=>'shop', 'n'=>'orderstatus', 'a'=>'edit', 'id'=>$item->os_id));
                $date_format = 'datetime_medium';
                $temp_array = array(
                    'URL' => $item_link,
                    'ID' => $item->os_id,
                    'TITLE' => htmlspecialchars($item->os_title),
                    'TITLE_LOCAL' => (!empty($L['shop']['order_'.$item->os_code])) ?
                            $L['shop']['order_'.$item->os_code] : '',
                    'CODE' => $item->os_code,
                    'DESC' => htmlspecialchars($item->os_desc),
                    'PUBLISHED' => $item->os_published ? $L['Yes'] : $L['No'],
                    'CREATE_DATE' => cot_date($date_format, strtotime($item->os_created_on)),
                    'MODIFY_DATE' => cot_date($date_format, strtotime($item->os_updated_on)),
                    'DELETE_URL' => cot_confirm_url(cot_url('admin', 'm=shop&n=orderstatus&a=delete&id='.$item->os_id.'&'.cot_xg()), 'admin'),
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
                $cacheitem && $ordrerst_cache[$item->os_id] = $temp_array;
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
OrderStatus::__init();