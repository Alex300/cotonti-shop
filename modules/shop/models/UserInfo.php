<?php
defined('COT_CODE') or die('Wrong URL.');
/**
 * Model class for User Info
 * @package shop
 * @subpackage Users
 *
 * @property int $ui_id;
 * @method static UserInfo getById(int $pk)
 * @method static UserInfo[] getList(int $limit = 0, int $offset = 0, string $order = '', string $way = 'ASC')
 * @method static UserInfo[] find(mixed $conditions, int $limit = 0, int $offset = 0, string $order = '', string $way = 'ASC')
 */
class UserInfo extends ShopModelAbstract{

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
        global $db_shop_shop_userinfo;

        self::$_table_name = $db_shop_shop_userinfo;
        self::$_primary_key = 'ui_id';
        parent::__init();
    }

    /**
     * Save data
     * @param Currency|array|null $data
     * @return int id of saved record
     */
    public function save($data = null){
        global $sys, $usr;

        if(!$data) $data = $this->_data;

        if ($data instanceof UserInfo) {
            $data = $data->toArray();
        }

        $data['ui_updated_on'] = date('Y-m-d H:i:s', $sys['now']);
        $data['ui_updated_by'] = $usr['id'];

        if(!$data['ui_id']) {
            // Добавить новую запись
            $data['ui_created_on'] = date('Y-m-d H:i:s', $sys['now']);
            $data['ui_created_by'] = $usr['id'];
        }
        $id = parent::save($data);
        if($id){
            if(!$data['ui_id']) {
                cot_log("Added new user adress # {$id}",'adm');
            }else{
                cot_log("Edited user adress # {$id}",'adm');
            }
        }
        return $id;
    }


    // === Методы для работы с шаблонами ===
    /**
     * Returns all UserInfo tags for coTemplate
     *
     * @param UserInfo|int $item UserInfo object or ID
     * @param string $tagPrefix Prefix for tags
     * @param bool $cacheitem Cache tags
     * @return array|void
     */
    public static function generateTags($item, $tagPrefix = '', $cacheitem = true){
        global $cfg, $L;

        static $extp_first = null, $extp_main = null;
        static $userinfo_cache = array();

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('shop.userinfo.tags.first');
            $extp_main  = cot_getextplugins('shop.userinfo.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */

        if ( ($item instanceof UserInfo) && is_array($userinfo_cache[$item->ui_id]) ) {
            $temp_array = $userinfo_cache[$item->ui_id];
        }elseif (is_int($item) && is_array($userinfo_cache[$item])){
            $temp_array = $userinfo_cache[$item];
        }else{
            if (is_int($item) && $item > 0){
                $item = self::getById($item);
                if(!$item) return false;
            }
            /** @var UserInfo $item  */
            if ($item->ui_id > 0){
                $item_link = cot_url('shop', array('m'=>'user', 'a'=>'editaddress', 'addrtype' => 'ST', 'uiid'=>$item->ui_id));
                $date_format = 'datetime_medium';
                $temp_array = array(
                    'EDTI_URL' => $item_link,
                    'ID' => $item->ui_id,
                    'TITLE' => htmlspecialchars($item->ui_title),
                    'CREATE_DATE' => cot_date($date_format, strtotime($item->ui_created_on)),
                    'MODIFY_DATE' => cot_date($date_format, strtotime($item->ui_updated_on)),
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
                $cacheitem && $userinfo_cache[$item->ui_id] = $temp_array;
            }else{
                // Нет записи
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
UserInfo::__init();